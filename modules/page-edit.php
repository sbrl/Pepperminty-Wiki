<?php
register_module([
	"name" => "Page editor",
	"version" => "0.15",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	
	"code" => function() {
		global $settings;
		
		/**
		 * @api {get} ?action=edit&page={pageName}[&newpage=yes]	Get an editing page
		 * @apiDescription	Gets an editing page for a given page. If you don't have permission to edit the page in question, a view source pagee is returned instead.
		 * @apiName			EditPage
		 * @apiGroup		Page
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse PageParameter
		 * @apiParam	{string}	newpage		Set to 'yes' if a new page is being created. Only affects a few bits of text here and there, and the HTTP response code recieved on success from the `save` action.
		 */
		
		/*
		 *           _ _ _
		 *   ___  __| (_) |_
		 *  / _ \/ _` | | __|
		 * |  __/ (_| | | |_
		 *  \___|\__,_|_|\__|
		 *             %edit%
		 */
		add_action("edit", function() {
			global $pageindex, $settings, $env;
			
			$filename = "$env->storage_prefix$env->page.md";
			$page = $env->page;
			$creatingpage = !isset($pageindex->$page);
			if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
			{
				$title = "Creating $env->page";
			}
			else
			{
				$title = "Editing $env->page";
			}
			
			$pagetext = "";
			if(isset($pageindex->$page))
			{
				$pagetext = file_get_contents($filename);
			}
			
			if((!$env->is_logged_in and !$settings->anonedits) or // if we aren't logged in and anonymous edits are disabled
			   !$settings->editing or // or editing is disabled
			   (
				   isset($pageindex->$page) and // or if the page exists
				   isset($pageindex->$page->protect) and // the protect property exists
				   $pageindex->$page->protect and // the protect property is true
				   !$env->is_admin // the user isn't an admin
			   )
			)
			{
				if(!$creatingpage)
				{
					// The page already exists - let the user view the page source
					if($env->is_logged_in)
						exit(page_renderer::render_main("Viewing source for $env->page", "<p>$env->page is protected, and you aren't an administrator or moderator. You can view the source of $env->page below, but you can't edit it.</p><textarea name='content' readonly>$pagetext</textarea>"));
					else
						exit(page_renderer::render_main("Viewing source for $env->page", "<p>$settings->sitename does not allow anonymous users to make edits. You can view the source of $env->page below, but you can't edit it. You could, however, try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p><textarea name='content' readonly>$pagetext</textarea>"));
						
				}
				else
				{
					http_response_code(404);
					exit(page_renderer::render_main("404 - $env->page", "<p>The page <code>$env->page</code> does not exist, but you do not have permission to create it.</p><p>If you haven't already, perhaps you should try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>"));
				}
			}
			
			$content = "<h1>$title</h1>";
			$page_tags = implode(", ", (!empty($pageindex->{$env->page}->tags)) ? $pageindex->{$env->page}->tags : []);
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save' class='editform'>
			<input type='hidden' name='prev-content-hash' value='" . sha1($pagetext) . "' />
			<textarea name='content' autofocus tabindex='1'>$pagetext</textarea>
			<input type='text' name='tags' value='$page_tags' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
			<p class='editing-message'>$settings->editing_message</p>
			<input name='submit-edit' type='submit' value='Save Page' tabindex='3' />
			<script>
				// Adapted from https://jsfiddle.net/2wAzx/13/
				document.querySelector(\"[name=content]\").addEventListener(\"keydown\", (event) => {
					if(event.keyCode !== 9) return true;
					var currentValue = event.target.value, startPos = event.target.selectionStart, endPos = event.target.selectionEnd;
					event.target.value = currentValue.substring(0, startPos) + \"\\t\" + currentValue.substring(endPos);
					event.target.selectionStart = event.target.selectionEnd = startPos + 1;
					event.stopPropagation(); event.preventDefault();
					return false;
				});
			</script>
		</form>";
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/**
		 * @api {post} ?action=save&page={pageName}	Save an edit to a page.
		 * @apiDescription	Saves an edit to a page. If an edit conflict is encountered, then a conflict resolution page is returned instead.
		 * @apiName			EditPage
		 * @apiGroup		Page
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse	PageParameter
		 * @apiParam	{string}	newpage		GET only. Set to 'yes' to indicate that this is a new page that is being saved. Only affects the HTTP response code you recieve upon success.
		 * @apiParam	{string}	content		POST only. The new content to save to the given filename.
		 * @apiParam	{string}	tags		POST only. A comma-separated list of tags to assign to the current page. Will replace the existing list of tags, if any are present.
		 * @apiParam	{string}	prev-content-hash	POST only. The hash of the original content before editing. If this hash is found to be different to a hash computed of the currentl saved content, a conflict resolution page will be returned instead of saving the provided content.
		 * 
		 * @apiError	UnsufficientPermissionError	You don't currently have sufficient permissions to save an edit.
		 */
		
		/*
		 *
		 *  ___  __ ___   _____
		 * / __|/ _` \ \ / / _ \
		 * \__ \ (_| |\ V /  __/
		 * |___/\__,_| \_/ \___|
		 *                %save%
		 */
		add_action("save", function() {
			global $pageindex, $settings, $env, $save_preprocessors, $paths; 
			
			if(!$settings->editing)
			{
				header("location: index.php?page=$env->page");
				exit(page_renderer::render_main("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
			}
			if(!$env->is_logged_in and !$settings->anonedits)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("You are not logged in, so you are not allowed to save pages on $settings->sitename. Redirecting in 5 seconds....");
			}
			$page = $env->page;
			if((
				isset($pageindex->$page) and
				isset($pageindex->page->protect) and
				$pageindex->$page->protect
			) and !$env->is_admin)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("$env->page is protected, and you aren't logged in as an administrator or moderator. Your edit was not saved. Redirecting in 5 seconds...");
			}
			if(!isset($_POST["content"]))
			{
				http_response_code(400);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("Bad request: No content specified.");
			}
			
			// Make sure that the directory in which the page needs to be saved exists
			if(!is_dir(dirname("$env->storage_prefix$env->page.md")))
			{
				// Recursively create the directory if needed
				mkdir(dirname("$env->storage_prefix$env->page.md"), 0775, true);
			}
			
			// Read in the new page content
			$pagedata = $_POST["content"];
			// We don't need to santise the input here as Parsedown has an
			// option that does this for us, and is _way_ more intelligent about
			// it.
			
			// Read in the new page tags, so long as there are actually some
			// tags to read in
			$page_tags = [];
			if(strlen(trim($_POST["tags"])) > 0)
			{
				$page_tags = explode(",", $_POST["tags"]);
				// Trim off all the whitespace
				foreach($page_tags as &$tag)
					$tag = trim($tag);
			}
			
			// Check for edit conflicts
			$existing_content_hash = sha1_file($env->storage_prefix . $pageindex->{$env->page}->filename);
			if(isset($_POST["prev-content-hash"]) and
				$existing_content_hash != $_POST["prev-content-hash"])
			{
				$existingPageData = htmlentities(file_get_contents($env->storage_prefix . $env->storage_prefix . $pageindex->{$env->page}->filename));
				// An edit conflict has occurred! We should get the user to fix it.
				$content = "<h1>Resolving edit conflict - $env->page</h1>";
				if(!$env->is_logged_in and $settings->anonedits)
				{
					$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
				}
				$content .= "<p>An edit conflict has arisen because someone else has saved an edit to $env->page since you started editing it. Both texts are shown below, along the differences between the 2 conflicting revisions. To continue, please merge your changes with the existing content. Note that only the text in the existing content box will be kept when you press the \"Resolve Conflict\" button at the bottom of the page.</p>
			
			<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save' class='editform'>
				<h2>Existing content</h2>
				<textarea id='original-content' name='content' autofocus tabindex='1'>$existingPageData</textarea>
				
				<h2>Differences</h2>
				<div id='highlighted-diff' class='highlighted-diff'></div>
				<!--<pre class='highlighted-diff-wrapper'><code id='highlighted-diff'></code></pre>-->
				
				<h2>Your content</h2>
				<textarea id='new-content'>$pagedata</textarea>
				<input type='text' name='tags' value='" . $_POST["tags"] . "' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
				<p class='editing_message'>$settings->editing_message</p>
				<input name='submit-edit' type='submit' value='Resolve Conflict' tabindex='3' />
			</form>";
				
				// Insert a reference to jsdiff to generate the diffs
				$diffScript = <<<'DIFFSCRIPT'
window.addEventListener("load", function(event) {
	var destination = document.getElementById("highlighted-diff"),
		diff = JsDiff.diffWords(document.getElementById("original-content").value, document.getElementById("new-content").value),
		output = "";
	diff.forEach(function(change) {
		var classes = "token";
		if(change.added) classes += " diff-added";
		if(change.removed) classes += " diff-removed";
		output += `<span class='${classes}'>${change.value}</span>`;
	});
	destination.innerHTML = output;
});
DIFFSCRIPT;

				$content .= "\n<script src='https://cdnjs.cloudflare.com/ajax/libs/jsdiff/2.2.2/diff.min.js'></script>
		<script>$diffScript</script>\n";
				
				exit(page_renderer::render_main("Edit Conflict - $env->page - $settings->sitename", $content));
			}
			
			// -----~~~==~~~-----
			
			// Update the inverted search index
			
			// Construct an index for the old and new page content
			$oldindex = [];
			$oldpagedata = ""; // We need the old page data in order to pass it to the preprocessor
			if(file_exists("$env->storage_prefix$env->page.md"))
			{
				$oldpagedata = file_get_contents("$env->storage_prefix$env->page.md");
				$oldindex = search::index($oldpagedata);
			}
			$newindex = search::index($pagedata);
			
			// Compare the indexes of the old and new content
			$additions = [];
			$removals = [];
			search::compare_indexes($oldindex, $newindex, $additions, $removals);
			// Load in the inverted index
			$invindex = search::load_invindex($env->storage_prefix . "invindex.json");
			// Merge the changes into the inverted index
			search::merge_into_invindex($invindex, ids::getid($env->page), $additions, $removals);
			// Save the inverted index back to disk
			search::save_invindex($env->storage_prefix . "invindex.json", $invindex);
			
			// -----~~~==~~~-----
			
			if(file_put_contents("$env->storage_prefix$env->page.md", $pagedata) !== false)
			{
				$page = $env->page;
				// Make sure that this page's parents exist
				check_subpage_parents($page);
				
				// Update the page index
				if(!isset($pageindex->$page))
				{
					$pageindex->$page = new stdClass();
					$pageindex->$page->filename = "$env->page.md";
				}
				$pageindex->$page->size = strlen($_POST["content"]);
				$pageindex->$page->lastmodified = time();
				if($env->is_logged_in)
					$pageindex->$page->lasteditor = utf8_encode($env->user);
				else
					$pageindex->$page->lasteditor = utf8_encode("anonymous");
				$pageindex->$page->tags = $page_tags;
				
				// A hack to resave the pagedata if the preprocessors have
				// changed it. We need this because the preprocessors *must*
				// run _after_ the pageindex has been updated.
				$pagedata_orig = $pagedata;
				
				// Execute all the preprocessors
				foreach($save_preprocessors as $func)
				{
					$func($pageindex->$page, $pagedata, $oldpagedata);
				}
				
				if($pagedata !== $pagedata_orig)
					file_put_contents("$env->storage_prefix$env->page.md", $pagedata);
				
				
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
				
				if(isset($_GET["newpage"]))
					http_response_code(201);
				else
					http_response_code(200);
				
//				header("content-type: text/plain");
				header("location: index.php?page=$env->page&edit_status=success&redirect=no");
				exit();
			}
			else
			{
				http_response_code(507);
				exit(page_renderer::render_main("Error saving page - $settings->sitename", "<p>$settings->sitename failed to write your changes to the server's disk. Your changes have not been saved, but you might be able to recover your edit by pressing the back button in your browser.</p>
				<p>Please tell the administrator of this wiki (" . $settings->admindetails_name . ") about this problem.</p>"));
			}
		});
		
		add_help_section("15-editing", "Editing", "<p>To edit a page on $settings->sitename, click the edit button on the top bar. Note that you will probably need to be logged in. If you do not already have an account you will need to ask $settings->sitename's administrator for an account since there is no registration form. Note that the $settings->sitename's administrator may have changed these settings to allow anonymous edits.</p>
		<p>Editing is simple. The edit page has a sizeable box that contains a page's current contents. Once you are done altering it, add or change the comma separated list of tags in the field below the editor and then click save page.</p>
		<p>A reference to the syntax that $settings->sitename supports can be found below.</p>");
	}
]);

?>
