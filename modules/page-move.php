<?php
register_module([
	"name" => "Page mover",
	"version" => "0.7",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to move pages.",
	"id" => "page-move",
	"code" => function() {
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
			
			//move the page in the page index
			$pageindex->$new_name = new stdClass();
			foreach($pageindex->$page as $key => $value)
			{
				$pageindex->$new_name->$key = $value;
			}
			unset($pageindex->$page);
			$pageindex->$new_name->filename = $new_name;
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
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
			
			// Move the page on the disk
			rename("$env->storage_prefix$env->page.md", "$env->storage_prefix$new_name.md");
			
			// Move the page in the id index
			ids::movepagename($page, $new_name);
			
			// Exit with a nice message
			exit(page_renderer::render_main("Moving $env->page", "<p><a href='index.php?page=$env->page'>$env->page</a> has been moved to <a href='index.php?page=$new_name'>$new_name</a> successfully.</p>"));
		});
	}
]);

?>
