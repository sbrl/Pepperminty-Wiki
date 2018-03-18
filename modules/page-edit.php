<?php
register_module([
	"name" => "Page editor",
	"version" => "0.17.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	
	"code" => function() {
		global $settings, $env;
		
		// Download diff.min.js - which we use when displaying edit conflicts
		register_remote_file([
			"local_filename" => "diff.min.js",
			"remote_url" => "https://cdnjs.cloudflare.com/ajax/libs/jsdiff/2.2.2/diff.min.js"
		]);

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
			$creatingpage = !isset($pageindex->{$env->page});
			if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
			{
				$title = "Creating $env->page";
			}
			else if(isset($_POST['preview-edit']) && isset($_POST['content']))
			{
				$title = "Preview Edits for $env->page";
			}
			else
			{
				$title = "Editing $env->page";
			}
			
			$pagetext = "";
			if(isset($pageindex->{$env->page}))
			{
				$pagetext = file_get_contents($filename);
			}
			
			$isOtherUsersPage = false;
			if(
				$settings->user_page_prefix == mb_substr($env->page, 0, mb_strlen($settings->user_page_prefix)) and // The current page is a user page of some sort
				(
					!$env->is_logged_in or // the user isn't logged in.....
					extract_user_from_userpage($env->page) !== $env->user // ...or it's not under this user's own name
				)
			) {
				$isOtherUsersPage = true;
			}
			
			if((!$env->is_logged_in and !$settings->anonedits) or // if we aren't logged in and anonymous edits are disabled
				!$settings->editing or // or editing is disabled
				(
					isset($pageindex->{$env->page}) and // or if the page exists
					isset($pageindex->{$env->page}->protect) and // the protect property exists
					$pageindex->{$env->page}->protect and // the protect property is true
					!$env->is_admin // the user isn't an admin
				) or
				$isOtherUsersPage // this page actually belongs to another user
			)
			{
				if(!$creatingpage)
				{
					// The page already exists - let the user view the page source
					$sourceViewContent = "<p>$settings->sitename does not allow anonymous users to make edits. You can view the source of $env->page below, but you can't edit it. You could, however, try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>\n";
					
					if($env->is_logged_in)
						$sourceViewContent = "<p>$env->page is protected, and you aren't an administrator or moderator. You can view the source of $env->page below, but you can't edit it.</p>\n";
					
					if(!$settings->editing)
						$sourceViewContent = "<p>$settings->sitename currently has editing disabled, so you can't make changes to this page at this time. Please contact $settings->admindetails_name, $settings->sitename's administrator for more information - their contact details can be found at the bottom of this page. Even so, you can still view the source of this page. It's disabled below:</p>";
					
					if($isOtherUsersPage)
						$sourceViewContent = "<p>$env->page is a special user page which acutally belongs to " . extract_user_from_userpage($env->page) . ", another user on $settings->sitename. Because of this, you are not allowed to edit it (though you can always edit your own page and any pages under it if you're logged in). You can, however, vieww it's source below.</p>";
					
					// Append a view of the page's source
					$sourceViewContent .= "<textarea name='content' readonly>$pagetext</textarea>";
					
					exit(page_renderer::render_main("Viewing source for $env->page", $sourceViewContent));
				}
				else
				{
					$errorMessage = "<p>The page <code>$env->page</code> does not exist, but you do not have permission to create it.</p><p>If you haven't already, perhaps you should try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>\n";
					
					if($isOtherUsersPage) {
						$errorMessage = "<p>The page <code>" . htmlentities($env->page) . "</code> doesn't exist, but you can't create it because it's a page belonging to another user.</p>\n";
						if(!$env->is_logged_in)
							$errorMessage .= "<p>You could try <a href='?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>\n";
					}
						
					http_response_code(404);
					exit(page_renderer::render_main("404 - $env->page", $errorMessage));
				}
			}
			
			$content = "<h1>$title</h1>\n";
			$page_tags = implode(", ", (!empty($pageindex->{$env->page}->tags)) ? $pageindex->{$env->page}->tags : []);
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			
			// Include preview, if set
			if(isset($_POST['preview-edit']) && isset($_POST['content'])) {
				// Need this for the prev-content-hash to prevent the conflict page from appearing
				$old_pagetext = $pagetext;

				// set the page content to the newly edited content
				$pagetext = $_POST['content'];

				// Set the tags to the new tags, if needed
				if(isset($_POST['tags']))
					$page_tags = $_POST['tags'];

				// Insert the "view" part of the page we're editing
				$content .=  "<p class='preview-message'><strong>This is only a preview, so your edits haven't been saved! Scroll down to continue editing.</strong></p>" . parse_page_source($pagetext);

			}

			$content .= "<form method='post' name='edit-form' action='index.php?action=preview-edit&page=" . rawurlencode($env->page) . "' class='editform'>
					<input type='hidden' name='prev-content-hash' value='" . ((isset($old_pagetext)) ? sha1($old_pagetext) : sha1($pagetext)) . "' />
					<button class='smartsave-restore' title=\"Only works if you haven't changed the editor's content already!\">Restore Locally Saved Content</button>
					<textarea name='content' autofocus tabindex='1'>$pagetext</textarea>
					<pre class='fit-text-mirror'></pre>
					<input type='text' name='tags' value='" . htmlentities($page_tags, ENT_HTML5 | ENT_QUOTES) . "' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
					<p class='editing-message'>$settings->editing_message</p>
					<input name='preview-edit' class='edit-page-button' type='submit' value='Preview Changes' tabindex='4' />
					<input name='submit-edit' class='edit-page-button' type='submit' value='Save Page' tabindex='3' />
					</form>";
			// Allow tab characters in the page editor
			page_renderer::AddJSSnippet("window.addEventListener('load', function(event) {
	// Adapted from https://jsfiddle.net/2wAzx/13/
	document.querySelector(\"[name=content]\").addEventListener(\"keydown\", (event) => {
		if(event.keyCode !== 9) return true;
		var currentValue = event.target.value, startPos = event.target.selectionStart, endPos = event.target.selectionEnd;
		event.target.value = currentValue.substring(0, startPos) + \"\\t\" + currentValue.substring(endPos);
		event.target.selectionStart = event.target.selectionEnd = startPos + 1;
		event.stopPropagation(); event.preventDefault();
		return false;
	});
});");
			
			// Utilise the mirror to automatically resize the textarea to fit it's content
			page_renderer::AddJSSnippet('function updateTextSize(textarea, mirror, event) {
	let textareaFontSize = parseFloat(getComputedStyle(textarea).fontSize);
	
	let textareaWidth = textarea.getBoundingClientRect().width;// - parseInt(textarea.style.padding);
	mirror.style.width = `${textareaWidth}px`;
	mirror.innerText = textarea.value;
	textarea.style.height = `${mirror.offsetHeight + (textareaFontSize * 5)}px`;
}
function trackTextSize(textarea) {
	let mirror = textarea.nextElementSibling;
	textarea.addEventListener("input", updateTextSize.bind(null, textarea, mirror));
	updateTextSize(textarea, mirror, null);
}
window.addEventListener("load", function(event) {
	trackTextSize(document.querySelector("textarea[name=content]"));
});
');
			
			// ~
			
			/// ~~~ Smart saving ~~~ ///
			page_renderer::AddJSSnippet('window.addEventListener("load", function(event) {
	"use strict";
	// Smart saving
	let getSmartSaveKey = function() { return document.querySelector("main h1").innerHTML.replace("Creating ", "").replace("Editing ", "").trim(); }
	// Saving
	document.querySelector("textarea[name=content]").addEventListener("keyup", function(event) { window.localStorage.setItem(getSmartSaveKey(), event.target.value) });
	// Loading
	var editor = document.querySelector("textarea[name=content]");
	let smartsave_restore = function() {
		editor.value = localStorage.getItem(getSmartSaveKey());
	}
	
	if(editor.value.length === 0) // Don\'t restore if there\'s data in the editor already
		smartsave_restore();
	
	document.querySelector(".smartsave-restore").addEventListener("click", function(event) {
		event.stopPropagation();
		event.preventDefault();
		smartsave_restore();
	});
});');
			
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/**
		 * @api {post} ?action=preview-edit&page={pageName}[&newpage=yes]	Get a preview of the page
		 * @apiDescription	Gets a preview of the current edit state of a given page
		 * @apiName 		PreviewPage
		 * @apiGroup		Editing
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse	PageParameter
		 * @apiParam	{string}	newpage 	Set to 'yes' if a new page is being created.
		 * @apiParam	{string}	preview-edit 	Set to a value to preview an edit of a page.
		 */

		/*
		 *
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███
		 *
		 * ███████ ██████  ██ ████████ 
		 * ██      ██   ██ ██    ██    
		 * █████   ██   ██ ██    ██    
		 * ██      ██   ██ ██    ██    
		 * ███████ ██████  ██    ██    
		 *
		 */
		add_action("preview-edit", function() {
			global $pageindex, $settings, $env, $actions;

			if(isset($_POST['preview-edit']) && isset($_POST['content'])) {
				// preview changes
				get_object_vars($actions)['edit']();
			}
			else {
				// save page
				get_object_vars($actions)['save']();
			}

			
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
				header("location: index.php?page=" . rawurlencode($env->page));
				exit(page_renderer::render_main("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
			}
			if(!$env->is_logged_in and !$settings->anonedits)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=" . rawurlencode($env->page));
				exit("You are not logged in, so you are not allowed to save pages on $settings->sitename. Redirecting in 5 seconds....");
			}
			if((
				isset($pageindex->{$env->page}) and
				isset($pageindex->{$env->page}->protect) and
				$pageindex->{$env->page}->protect
			) and !$env->is_admin)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=" . rawurlencode($env->page));
				exit(htmlentities($env->page) . " is protected, and you aren't logged in as an administrator or moderator. Your edit was not saved. Redirecting in 5 seconds...");
			}
			if(!isset($_POST["content"]))
			{
				http_response_code(400);
				header("refresh: 5; url=index.php?page=" . rawurlencode($env->page));
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
			if(!empty($pageindex->{$env->page}) && file_exists($env->storage_prefix . $pageindex->{$env->page}->filename))
			{
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
					$content .= "<p>An edit conflict has arisen because someone else has saved an edit to " . htmlentities($env->page) . " since you started editing it. Both texts are shown below, along the differences between the 2 conflicting revisions. To continue, please merge your changes with the existing content. Note that only the text in the existing content box will be kept when you press the \"Resolve Conflict\" button at the bottom of the page.</p>
					
					<form method='post' action='index.php?action=save&page=" . rawurlencode($env->page) . "&action=save' class='editform'>
					<h2>Existing content</h2>
					<textarea id='original-content' name='content' autofocus tabindex='1'>$existingPageData</textarea>
					
					<h2>Differences</h2>
					<div id='highlighted-diff' class='highlighted-diff'></div>
					<!--<pre class='highlighted-diff-wrapper'><code id='highlighted-diff'></code></pre>-->
					
					<h2>Your content</h2>
					<textarea id='new-content'>$pagedata</textarea>
					<input type='text' name='tags' value='" . htmlentities($_POST["tags"]) . "' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
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
					// diff.min.js is downloaded above
					$content .= "\n<script src='diff.min.js'></script>
					<script>$diffScript</script>\n";
					
					exit(page_renderer::render_main("Edit Conflict - $env->page - $settings->sitename", $content));
				}
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
				// Make sure that this page's parents exist
				check_subpage_parents($env->page);
				
				// Update the page index
				if(!isset($pageindex->{$env->page}))
				{
					$pageindex->{$env->page} = new stdClass();
					$pageindex->{$env->page}->filename = "$env->page.md";
				}
				$pageindex->{$env->page}->size = strlen($_POST["content"]);
				$pageindex->{$env->page}->lastmodified = time();
				if($env->is_logged_in)
					$pageindex->{$env->page}->lasteditor = $env->user;
				else // TODO: Add an option to record the user's IP here instead
					$pageindex->{$env->page}->lasteditor = "anonymous";
				$pageindex->{$env->page}->tags = $page_tags;
				
				// A hack to resave the pagedata if the preprocessors have
				// changed it. We need this because the preprocessors *must*
				// run _after_ the pageindex has been updated.
				$pagedata_orig = $pagedata;
				
				// Execute all the preprocessors
				foreach($save_preprocessors as $func)
				{
					$func($pageindex->{$env->page}, $pagedata, $oldpagedata);
				}
				
				if($pagedata !== $pagedata_orig)
					file_put_contents("$env->storage_prefix$env->page.md", $pagedata);
				
				
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
				
				if(isset($_GET["newpage"]))
					http_response_code(201);
				else
					http_response_code(200);
				
//				header("content-type: text/plain");
				header("location: index.php?page=" . rawurlencode($env->page) . "&edit_status=success&redirect=no");
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
		
		add_help_section("17-user-pages", "User Pages", "<p>If you are logged in, $settings->sitename allocates you your own user page that only you can edit. On $settings->sitename, user pages are sub-pages of the <a href='?page=" . rawurlencode($settings->user_page_prefix) . "'>" . htmlentities($settings->user_page_prefix) . "</a> page, and each user page can have a nested structure of pages underneath it, just like a normal page. Your user page is located at <a href='?page=" . rawurlencode(get_user_pagename($env->user)) . "'>" . htmlentities(get_user_pagename($env->user)) . "</a>.</p>");
	}
]);

?>
