<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Page editor",
	"version" => "0.18",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	"extra_data" => [
		"diff.min.js" => "https://cdnjs.cloudflare.com/ajax/libs/jsdiff/2.2.2/diff.min.js",
		"awesomplete.min.js" => "https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.js",
		"awesomplete.min.css" => "https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.css"
	],
	
	"code" => function() {
		global $settings, $env;
		
		
		/**
		 * @api {get} ?action=edit&page={pageName}[&newpage=yes]	Get an editing page
		 * @apiDescription	Gets an editing page for a given page. If you don't have permission to edit the page in question, a view source pagee is returned instead.
		 * @apiName			EditPage
		 * @apiGroup		Editing
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse PageParameter
		 * @apiParam	{string}	newpage		Optional. Set to 'yes' if a new page is being created. Only affects a few bits of text here and there, and the HTTP response code recieved on success from the `save` action.
		 * @apiParam	{string}	unknownpagename	Optional. Set to 'yes' if the name of the page to be created is currently unknown. If set, a page name box will be shown too.
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
			global $pageindex, $settings, $env, $paths;
			
			$unknownpagename = isset($_GET["unknownpagename"]) && strlen(trim($_GET["unknownpagename"])) > 0;
			$filename = "$env->storage_prefix$env->page.md";
			$creatingpage = !isset($pageindex->{$env->page});
			if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
				$title = "Creating $env->page";
			else if(isset($_POST['preview-edit']) && isset($_POST['content']))
				$title = "Preview Edits for $env->page";
			else if($unknownpagename)
				$title = "Creating new page";
			else
				$title = "Editing $env->page";
			
			$pagetext = ""; $page_tags = "";
			if(isset($pageindex->{$env->page}) && !$unknownpagename)
				$pagetext = file_get_contents($filename);
			if(!$unknownpagename)
				$page_tags = htmlentities(implode(", ", (!empty($pageindex->{$env->page}->tags)) ? $pageindex->{$env->page}->tags : []));
			
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
				if(!$creatingpage) {
					// The page already exists - let the user view the page source
					$sourceViewContent = "<p>$settings->sitename does not allow anonymous users to make edits. You can view the source of $env->page below, but you can't edit it. You could, however, try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>\n";
					
					if($env->is_logged_in)
						$sourceViewContent = "<p>$env->page is protected, and you aren't an administrator or moderator. You can view the source of $env->page below, but you can't edit it.</p>\n";
					
					if(!$settings->editing)
						$sourceViewContent = "<p>$settings->sitename currently has editing disabled, so you can't make changes to this page at this time. Please contact $settings->admindetails_name, $settings->sitename's administrator for more information - their contact details can be found at the bottom of this page. Even so, you can still view the source of this page. It's disabled below:</p>";
					
					if($isOtherUsersPage)
						$sourceViewContent = "<p>$env->page is a special user page which acutally belongs to " . extract_user_from_userpage($env->page) . ", another user on $settings->sitename. Because of this, you are not allowed to edit it (though you can always edit your own page and any pages under it if you're logged in). You can, however, vieww it's source below.</p>";
					
					// Append a view of the page's source
					$sourceViewContent .= "<textarea name='content' readonly>".htmlentities($pagetext)."</textarea>";
					
					exit(page_renderer::render_main("Viewing source for $env->page", $sourceViewContent));
				}
				else {
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
			
			if(!$env->is_logged_in and $settings->anonedits) {
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			if(isset($_GET["redirected_from"])) {
				$content .= "<p><em>Redirected from <a href='?page=".rawurlencode($_GET["redirected_from"])."&amp;redirect=no'>".htmlentities($_GET["redirected_from"])."</a></em></p>\n";
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

			$content .= "<button class='smartsave-restore' title=\"Only works if you haven't changed the editor's content already!\">Restore Locally Saved Content</button>
			<form method='post' name='edit-form' action='index.php?action=preview-edit&page=" . rawurlencode($env->page) . "' class='editform'>
					<input type='hidden' name='prev-content-hash' value='" . generate_page_hash(isset($old_pagetext) ? $old_pagetext : $pagetext) . "' />";
			if($unknownpagename)
				$content .= "<div><label for='page'>Page Name:</label>
					<input type='text' id='page' name='page' value='' placeholder='Enter the name of the page here.' title='Enter the name of the page here.' />
					<input type='hidden' name='prevent_save_if_exists' value='yes' />";
			$content .= "<textarea name='content' autofocus tabindex='1'>$pagetext</textarea>
					<pre class='fit-text-mirror'></pre>
					<input type='text' id='tags' name='tags' value='" . htmlentities($page_tags, ENT_HTML5 | ENT_QUOTES) . "' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
					<p class='editing-message'>$settings->editing_message</p>
					<input name='preview-edit' class='edit-page-button' type='submit' value='Preview Changes' tabindex='4' />
					<input name='submit-edit' class='edit-page-button' type='submit' value='Save Page' tabindex='3' />
					</form>";
			// Allow tab characters in the page editor
			page_renderer::add_js_snippet("window.addEventListener('load', function(event) {
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
			page_renderer::add_js_snippet('function updateTextSize(textarea, mirror, event) {
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
			page_renderer::add_js_snippet('window.addEventListener("load", function(event) {
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
			// Why would anyone want this disabled?
			if($settings->editing_tags_autocomplete) {
				page_renderer::add_js_link("$paths->extra_data_directory/page-edit/awesomplete.min.js");
				page_renderer::add_js_snippet('window.addEventListener("load", async (event) => {
	// FUTURE: Optionally cache this?
	let response = await fetch("?action=list-tags&format=text");
	if(!response.ok) {
		console.warn(`Warning: Failed to fetch tags list with status code ${response.status} ${response.statusText}`);
		return;
	}
	
	let tags = (await response.text()).split("\n");
	console.log(tags);
	window.input_tags_completer = new Awesomplete(
		document.querySelector("#tags"), {
			list: tags,
			filter: function(text, input) {
				console.log(arguments);
				// Avoid suggesting tags that are already present
				if(input.split(/,\s*/).includes(text.value)) return false;
				return Awesomplete.FILTER_CONTAINS(text, input.match(/[^,]*$/)[0]);
			},
			item: function(text, input) {
				return Awesomplete.ITEM(text, input.match(/[^,]*$/)[0]);
			},
			replace: function(text) {
				var before = this.input.value.match(/^.+,\s*|/)[0];
				this.input.value = before + text + ", ";
			}
		}
	);
});
				');
				$content .= "<link rel=\"stylesheet\" href=\"$paths->extra_data_directory/page-edit/awesomplete.min.css\" />";
			}
			
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/**
		 * @api {post} ?action=preview-edit&page={pageName}[&newpage=yes]	Get a preview of an edit to a page
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
		 * @api {post} ?action=acquire-edit-key&page={pageName}		Acquire an edit key for a page
		 * @apiDescription	Returns an edit key that can be used to programmatically save an edit to a page. It does _not_ necessarily mean that such an edit will be saved. For example, editing might be disabled, or you might not have permission to save an edit on a particular page.
		 * @apiName 		AcquireEditKey
		 * @apiGroup		Editing
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse		PageParameter
		 * @apiParam	{string}	format	The format to return the edit key in. Possible values: text, json. Default: text.
		 * @apiParam	{string}	prevent_save_if_exists	Optional. If set to 'yes', then if a page exists with the specified page name the save is aborted and an error page returned instead.
		 * @apiParam	{string}	page	The name of the page to save the edit to. Note that in this specific instance *only*, the page name can be specified over GET or POST (POST will override GET if both are present). 
		 */
		
		/*
 		 *  █████   ██████  ██████  ██    ██ ██ ██████  ███████
 		 * ██   ██ ██      ██    ██ ██    ██ ██ ██   ██ ██
 		 * ███████ ██      ██    ██ ██    ██ ██ ██████  █████ █████
 		 * ██   ██ ██      ██ ▄▄ ██ ██    ██ ██ ██   ██ ██
 		 * ██   ██  ██████  ██████   ██████  ██ ██   ██ ███████
 		 *                     ▀▀
 		 * 
 		 * ███████ ██████  ██ ████████
 		 * ██      ██   ██ ██    ██
 		 * █████   ██   ██ ██    ██ █████
 		 * ██      ██   ██ ██    ██
 		 * ███████ ██████  ██    ██
 		 * 
 		 * ██   ██ ███████ ██    ██
 		 * ██  ██  ██       ██  ██
 		 * █████   █████     ████
 		 * ██  ██  ██         ██
 		 * ██   ██ ███████    ██
		 */
		add_action("acquire-edit-key", function() {
			global $env;
			
			if(!file_exists($env->page_filename)) {
				http_response_code(404);
				header("content-type: text/plain");
				exit("Error: The page '$env->page' couldn't be found.");
			}
			
			$format = $_GET["format"] ?? "text";
			$page_hash = generate_page_hash(file_get_contents($env->page_filename));
			
			switch($format) {
				case "text":
					header("content-type: text/plain");
					exit("$env->page\t$page_hash");
				case "json":
					$result = new stdClass();
					$result->page = $env->page;
					$result->key = $page_hash;
					header("content-type: application/json");
					exit(json_encode($result));
				default:
					http_response_code(406);
					header("content-type: text/plain");
					exit("Error: The format $format is not currently known. Supported formats: text, json. Default: text.\nThink this is a bug? Open an issue at https://github.com/sbrl/Pepperminty-Wiki/issues/new");
			}
		});
		
		/**
		 * @api {post} ?action=save&page={pageName}	Save an edit to a page
		 * @apiDescription	Saves an edit to a page. If an edit conflict is encountered, then a conflict resolution page is returned instead.
		 * @apiName			SavePage
		 * @apiGroup		Editing
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
			// Update the page name in the main environment, since the page name may be submitted via the POST form
			if(isset($_POST["page"])) {
				$env->page = $_POST["page"];
				$env->page_safe = htmlentities($env->page);
			}
			
			if(!$settings->editing)
			{
				header("x-failure-reason: editing-disabled");
				header("location: index.php?page=" . rawurlencode($env->page));
				exit(page_renderer::render_main("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
			}
			if(!$env->is_logged_in and !$settings->anonedits)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=" . rawurlencode($env->page));
				header("x-login-required: yes");
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
			if(isset($_POST["prevent_save_if_exists"]) && isset($pageindex->{$env->page})) {
				http_response_code(409);
				exit(page_renderer::render_main("Error saving new page - ".htmlentities($env->page)." - $settings->sitename", "<p>Error: A page with that name already exists. Things you can do:</p>
				<ul>
					<li>View the existing page here: <a target='_blank' href='?action={$settings->defaultaction}&page=".rawurlencode($env->page)."'>".htmlentities($env->page)."</a></li>
					<li><a href='javascript:history.back();'>Go back to continu editing and change the page name</a></li>
				</ul>
				<p>For reference, the page content you attempted to submit is shown below:</p>
				<textarea name='content'>".htmlentities($_POST["content"])."</textarea>"));
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
			if(strlen(trim($_POST["tags"])) > 0) {
				$page_tags = explode(",", $_POST["tags"]);
				// Trim off all the whitespace
				foreach($page_tags as &$tag) {
					$tag = trim($tag);
				}
				// Ignore empty tags
				$page_tags = array_filter($page_tags, function($value) {
					return !is_null($value) && $value !== '';
				});
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
					$diff_script = <<<'DIFFSCRIPT'
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

					page_renderer::add_js_link("$paths->extra_data_directory/page-edit/diff.min.js");
					page_renderer::add_js_snippet($diff_script);
					
					header("x-failure-reason: edit-conflict");
					exit(page_renderer::render_main("Edit Conflict - $env->page - $settings->sitename", $content));
				}
			}
			
			// -----~~~==~~~-----
			
			// Update the inverted search index
			
			if(module_exists("feature-search")) {
				// Construct an index for the old and new page content
				$oldindex = [];
				$oldpagedata = ""; // We need the old page data in order to pass it to the preprocessor
				if(file_exists("$env->storage_prefix$env->page.md")) {
					$oldpagedata = file_get_contents("$env->storage_prefix$env->page.md");
					$oldindex = search::index_generate($oldpagedata);
				}
				$newindex = search::index_generate($pagedata);
				
				// Compare the indexes of the old and new content
				$additions = [];
				$removals = [];
				search::index_compare($oldindex, $newindex, $additions, $removals);
				// Load in the inverted index
				search::invindex_load($paths->searchindex);
				// Merge the changes into the inverted index
				search::invindex_merge(ids::getid($env->page), $additions, $removals);
				// Save the inverted index back to disk
				search::invindex_close();
			}
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
					$func($pageindex->{$env->page}, $pagedata, $oldpagedata);
				
				if($pagedata !== $pagedata_orig)
					file_put_contents("$env->storage_prefix$env->page.md", $pagedata);
				
				save_pageindex();
				
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
				header("x-failure-reason: server-error");
				http_response_code(507);
				exit(page_renderer::render_main("Error saving page - $settings->sitename", "<p>$settings->sitename failed to write your changes to the server's disk. Your changes have not been saved, but you might be able to recover your edit by pressing the back button in your browser.</p>
				<p>Please tell the administrator of this wiki (" . $settings->admindetails_name . ") about this problem.</p>"));
			}
		});
		
		add_help_section("15-editing", "Editing", "<p>To edit a page on $settings->sitename, click the edit button on the top bar. Note that you will probably need to be logged in. If you do not already have an account you will need to ask $settings->sitename's administrator for an account since there is no registration form. Note that the $settings->sitename's administrator may have changed these settings to allow anonymous edits.</p>
		<p>Editing is simple. The edit page has a sizeable box that contains a page's current contents. Once you are done altering it, add or change the comma separated list of tags in the field below the editor and then click save page.</p>
		<p>A reference to the syntax that $settings->sitename supports can be found below.</p>");
		
		add_help_section("17-user-pages", "User Pages", "<p>If you are logged in, $settings->sitename allocates you your own user page that only you can edit. On $settings->sitename, user pages are sub-pages of the <a href='?page=" . rawurlencode($settings->user_page_prefix) . "'>" . htmlentities($settings->user_page_prefix) . "</a> page, and each user page can have a nested structure of pages underneath it, just like a normal page. Your user page is located at <a href='?page=" . rawurlencode(get_user_pagename($env->user)) . "'>" . htmlentities(get_user_pagename($env->user)) . "</a>. " .
			(module_exists("page-user-list") ? "You can see a list of all the users on $settings->sitename and visit their user pages on the <a href='?action=user-list'>user list</a>." : "")
		 . "</p>");
	}
]);
/**
 * Generates a unique hash of a page's content for edit conflict detection
 * purposes.
 * @param	string	$page_data	The page text to hash.
 * @return	string				A hash of the given page text.
 */
function generate_page_hash($page_data) {
	return sha1($page_data);
}

?>
