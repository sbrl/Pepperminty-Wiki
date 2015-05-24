<?php
register_module([
	"name" => "Page deleter",
	"version" => "0.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		add_action("delete", function() {
			global $pageindex, $settings, $page, $isadmin;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Deleting $page - error", "<p>You tried to delete $page, but editing is disabled on this wiki.</p>
				<p>If you wish to delete this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$page'>Go back to $page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$isadmin)
			{
				exit(page_renderer::render_main("Deleting $page - error", "<p>You tried to delete $page, but you are not an admin so you don't have permission to do that.</p>
				<p>You should try <a href='index.php?action=login'>logging in</a> as an admin.</p>"));
			}
			if(!isset($_GET["delete"]) or $_GET["delete"] !== "yes")
			{
				exit(page_renderer::render_main("Deleting $page", "<p>You are about to <strong>delete</strong> $page. You can't undo this!</p>
				<p><a href='index.php?action=delete&page=$page&delete=yes'>Click here to delete $page.</a></p>
				<p><a href='index.php?action=view&page=$page'>Click here to go back.</a>"));
			}
			unset($pageindex->$page); //delete the page from the page index
			file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT)); //save the new page index
			unlink("./$page.md"); //delete the page from the disk

			exit(page_renderer::render_main("Deleting $page - $settings->sitename", "<p>$page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
	}
]);

?>
