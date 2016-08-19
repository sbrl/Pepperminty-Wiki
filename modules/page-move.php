<?php
register_module([
	"name" => "Page mover",
	"version" => "0.9",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to move pages.",
	"id" => "page-move",
	"code" => function() {
		global $settings;
		
		/**
		 * @api		{get}	?action=move[new_name={newPageName}]	Move a page
		 * @apiName		Move
		 * @apiGroup	Page
		 * @apiPermission	Moderator
		 * 
		 * @apiParam	{string}	new_name	The new name to move the page to. If not set a page will be returned containing a move page form.
		 *
		 * @apiUse UserNotModeratorError
		 * @apiError	EditingDisabledError	Editing is disabled on this wiki, so pages can't be moved.
		 * @apiError	PageExistsAtDestinationError	A page already exists with the specified new name.
		 * @apiError	NonExistentPageError		The page you're trying to move doesn't exist in the first place.
		 * @apiError	PreExistingFileError		A pre-existing file on the server's file system was detected.
		 */
		
		/*
		 * ███    ███  ██████  ██    ██ ███████ 
		 * ████  ████ ██    ██ ██    ██ ██      
		 * ██ ████ ██ ██    ██ ██    ██ █████   
		 * ██  ██  ██ ██    ██  ██  ██  ██      
		 * ██      ██  ██████    ████   ███████ 
		 */
		add_action("move", function() {
			global $pageindex, $settings, $env, $paths;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Moving $env->page - error", "<p>You tried to move $env->page, but editing is disabled on this wiki.</p>
				<p>If you wish to move this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$env->page'>Go back to $env->page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$env->is_admin)
			{
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $env->page, but you do not have permission to do that.</p>
				<p>You should try <a href='index.php?action=login'>logging in</a> as an admin.</p>"));
			}
			
			if(!isset($_GET["new_name"]) or strlen($_GET["new_name"]) == 0)
				exit(page_renderer::render_main("Moving $env->page", "<h2>Moving $env->page</h2>
				<form method='get' action='index.php'>
					<input type='hidden' name='action' value='move' />
					<label for='old_name'>Old Name:</label>
					<input type='text' name='page' value='$env->page' readonly />
					<br />
					<label for='new_name'>New Name:</label>
					<input type='text' name='new_name' />
					<br />
					<input type='submit' value='Move Page' />
				</form>"));
			
			$new_name = makepathsafe($_GET["new_name"]);
			
			$page = $env->page;
			if(!isset($pageindex->$page))
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $env->page to $new_name, but the page with the name $env->page does not exist in the first place.</p>
				<p>Nothing has been changed.</p>"));
			
			if($env->page == $new_name)
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $page, but the new name you gave is the same as it's current name.</p>
				<p>It is possible that you tried to use some characters in the new name that are not allowed and were removed.</p>
				<p>Page names may only contain alphanumeric characters, dashes, and underscores.</p>"));
			
			if(isset($pageindex->$page->uploadedfile) and
				file_exists($new_name))
				exit(page_renderer::render_main("Moving $env->page - Error - $settings->sitename", "<p>Whilst moving the file associated with $env->page, $settings->sitename detected a pre-existing file on the server's file system. Because $settings->sitename can't determine whether the existing file is important to another component of $settings->sitename or it's host web server, the move have been aborted - just in case.</p>
				<p>If you know that this move is actually safe, please get your site administrator (" . $settings->admindetails_name . ") to perform the move manually. Their contact address can be found at the bottom of every page (including this one).</p>"));
			
			// Move the page in the page index
			$pageindex->$new_name = new stdClass();
			foreach($pageindex->$page as $key => $value)
			{
				$pageindex->$new_name->$key = $value;
			}
			unset($pageindex->$page);
			$pageindex->$new_name->filename = "$new_name.md";
			
			// If this page has an associated file, then we should move that too
			if(!empty($pageindex->$new_name->uploadedfile))
			{
				// Update the filepath to point to the description and not the image
				$pageindex->$new_name->filename = $pageindex->$new_name->filename . ".md";
				// Move the file in the pageindex
				$pageindex->$new_name->uploadedfilepath = $new_name;
				// Move the file on disk
				rename($env->storage_prefix . $env->page, $env->storage_prefix . $new_name);
			}
			
			// Come to think about it, we should probably move the history while we're at it
			foreach($pageindex->$new_name->history as &$revisionData)
			{
				// We're only interested in edits
				if($revisionData->type !== "edit") continue;
				$newRevisionName = $pageindex->$new_name->filename . ".r$revisionData->rid";
				// Move the revision to it's new name
				rename(
					$env->storage_prefix . $revisionData->filename,
					$env->storage_prefix . $newRevisionName
				);
				// Update the pageindex entry
				$revisionData->filename = $newRevisionName;
			}
			
			// Save the updated pageindex
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
			
			// Move the page on the disk
			rename("$env->storage_prefix$env->page.md", "$env->storage_prefix$new_name.md");
			
			// Move the page in the id index
			ids::movepagename($page, $new_name);
			
			// Exit with a nice message
			exit(page_renderer::render_main("Moving $env->page", "<p><a href='index.php?page=$env->page'>$env->page</a> has been moved to <a href='index.php?page=$new_name'>$new_name</a> successfully.</p>"));
		});
		
		// Register a help section
		add_help_section("60-move", "Moving Pages", "<p>If you are logged in as an administrator, then you have the power to move pages. To do this, click &quot;Delete&quot; in the &quot;More...&quot; menu when browsing the pge you wish to move. Type in the new name of the page, and then click &quot;Move Page&quot;.</p>");
	}
]);

?>
