<?php
register_module([
	"name" => "Page deleter",
	"version" => "0.9",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		global $settings;
		
		add_action("delete", function() {
			global $pageindex, $settings, $env, $paths, $modules;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Deleting $env->page - error", "<p>You tried to delete $env->page, but editing is disabled on this wiki.</p>
				<p>If you wish to delete this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$env->page'>Go back to $env->page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$env->is_admin)
			{
				exit(page_renderer::render_main("Deleting $env->page - error", "<p>You tried to delete $env->page, but you are not an admin so you don't have permission to do that.</p>
				<p>You should try <a href='index.php?action=login'>logging in</a> as an admin.</p>"));
			}
			if(!isset($_GET["delete"]) or $_GET["delete"] !== "yes")
			{
				exit(page_renderer::render_main("Deleting $env->page", "<p>You are about to <strong>delete</strong> $env->page. You can't undo this!</p>
				<p><a href='index.php?action=delete&page=$env->page&delete=yes'>Click here to delete $env->page.</a></p>
				<p><a href='index.php?action=view&page=$env->page'>Click here to go back.</a>"));
			}
			$page = $env->page;
			// Delete the associated file if it exists
			if(!empty($pageindex->$page->uploadedfile))
			{
				unlink($env->storage_prefix . $pageindex->$page->uploadedfilepath);
			}
			
			// Delete the page from the page index
			unset($pageindex->$page);
			
			// Save the new page index
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT)); 
			
			// Remove the page's name from the id index
			ids::deletepagename($env->page);
			
			// Delete the page from the search index, if that module is installed
			if(module_exists("feature-search"))
			{
				$pageid = ids::getid($env->page);
				$invindex = search::load_invindex($paths->searchindex);
				search::delete_entry($invindex, $pageid);
				search::save_invindex($paths->searchindex, $invindex);
			}
			
			// Delete the page from the disk
			unlink("$env->storage_prefix$env->page.md");
			
			exit(page_renderer::render_main("Deleting $env->page - $settings->sitename", "<p>$env->page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
		
		// Register a help section
		add_help_section("60-delete", "Deleting Pages", "<p>If you are logged in as an adminitrator, then you have the power to delete pages. To do this, click &quot;Delete&quot; in the &quot;More...&quot; menu when browsing the pge you wish to delete. When you are sure that you want to delete the page, click the given link.</p>
		<p><strong>Warning: Once a page has been deleted, you can't bring it back! You will need to recover it from your backup, if you have one (which you really should).</strong></p>");
	}
]);

?>
