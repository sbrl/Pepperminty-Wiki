<?php
register_module([
	"name" => "Page deleter",
	"version" => "0.10.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		global $settings;
		/**
		 * @api {post} ?action=delete Delete a page
		 * @apiDescription	Delete a page and all its associated data.
		 * @apiName DeletePage
		 * @apiGroup Page
		 * @apiPermission Moderator
		 * 
		 * @apiParam {string}	page		The name of the page to delete.
		 * @apiParam {string}	delete		Set to 'yes' to actually delete the page.
		 *
		 * @apiUse	UserNotModeratorError
		 * @apiError	PageNonExistentError	The specified page doesn't exist
		 */
		
		/*
		 * ██████  ███████ ██      ███████ ████████ ███████ 
		 * ██   ██ ██      ██      ██         ██    ██      
		 * ██   ██ █████   ██      █████      ██    █████   
		 * ██   ██ ██      ██      ██         ██    ██      
		 * ██████  ███████ ███████ ███████    ██    ███████ 
		 */
		add_action("delete", function() {
			global $pageindex, $settings, $env, $paths, $modules;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Error: Editing disabled - Deleting $env->page", "<p>You tried to delete $env->page, but editing is disabled on this wiki.</p>
				<p>If you wish to delete this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$env->page'>Go back to $env->page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$env->is_admin)
			{
				exit(page_renderer::render_main("Error: Insufficient permissions - Deleting $env->page", "<p>You tried to delete $env->page, but you as aren't a moderator you don't have permission to do that.</p>
				<p>You could try <a href='index.php?action=login'>logging in</a> as an admin, or asking one of $settings->sitename's friendly moderators (find their names at the bottom of every page!) to delete it for you.</p>"));
			}
			if(!isset($pageindex->{$env->page}))
			{
				exit(page_renderer::render_main("Error: Non-existent page - Deleting $env->page", "<p>You tried to delete $env->page, but that page doesn't appear to exist in the first page. <a href='?'>Go back</a> to the $settings->defaultpage.</p>"));
			}
			
			if(!isset($_GET["delete"]) or $_GET["delete"] !== "yes")
			{
				exit(page_renderer::render_main("Deleting $env->page", "<p>You are about to <strong>delete</strong> <em>$env->page</em>" . (module_exists("feature-history")?" and all its revisions":"") . (module_exists("feature-comments")?" and all its comments":"") . ". You can't undo this!</p>
				<p><a href='index.php?action=delete&page=$env->page&delete=yes'>Click here to delete $env->page.</a></p>
				<p><a href='index.php?action=view&page=$env->page'>Click here to go back.</a>"));
			}
			$page = $env->page;
			// Delete the associated file if it exists
			if(!empty($pageindex->$page->uploadedfile))
			{
				unlink($env->storage_prefix . $pageindex->$page->uploadedfilepath);
			}
			
			// While we're at it, we should delete all the revisions too
			foreach($pageindex->{$env->page}->history as $revisionData)
			{
				unlink($env->storage_prefix . $revisionData->filename);
			}
			
			// If the commenting module is installed and the page has comments,
			// delete those too
			if(module_exists("feature-comments") and
				file_exists(get_comment_filename($env->page)))
			{
				unlink(get_comment_filename($env->page));
			}
			
			// Delete the page from the page index
			unset($pageindex->$page);
			
			// Save the new page index
			save_pageindex();
			
			
			// Delete the page from the search index, if that module is installed
			if(module_exists("feature-search")) {
				$pageid = ids::getid($env->page);
				search::invindex_load($paths->searchindex);
				search::invindex_delete($pageid);
				search::invindex_close();
			}
			
			// Remove the page's name from the id index
			ids::deletepagename($env->page);
			
			// Delete the page from the disk
			unlink("$env->storage_prefix$env->page.md");
			
			// Add a recent change announcing the deletion if the recent changes
			// module is installed
			if(module_exists("feature-recent-changes"))
			{
				add_recent_change([
					"type" => "deletion",
					"timestamp" => time(),
					"page" => $env->page,
					"user" => $env->user,
				]);
			}
			
			exit(page_renderer::render_main("Deleting $env->page - $settings->sitename", "<p>$env->page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
		
		// Register a help section
		add_help_section("60-delete", "Deleting Pages", "<p>If you are logged in as an adminitrator, then you have the power to delete pages. To do this, click &quot;Delete&quot; in the &quot;More...&quot; menu when browsing the pge you wish to delete. When you are sure that you want to delete the page, click the given link.</p>
		<p><strong>Warning: Once a page has been deleted, you can't bring it back! You will need to recover it from your backup, if you have one (which you really should).</strong></p>");
	}
]);

?>
