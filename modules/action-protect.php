<?php
register_module([
	"name" => "Page protection",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Exposes Pepperminty Wiki's new page protection mechanism and makes the protect button in the 'More...' menu on the top bar work.",
	"id" => "action-protect",
	"code" => function() {
		add_action("protect", function() {
			global $env, $pageindex;

			// Make sure that the user is logged in as an admin / mod.
			if($env->is_admin)
			{
				// They check out ok, toggle the page's protection.
				$page = $env->page;

				$toggled = false;
				if(!isset($pageindex->$page->protect))
				{
					$pageindex->$page->protect = true;
					$toggled = true;
				}

				if(!$toggled && $pageindex->$page->protect === true)
				{
					$pageindex->$page->protected = false;
					$toggled = false;
				}

				if(!$toggled && $pageindex->$page->protect === false)
				{
					$pageindex->$page->protected = true;
					$toggled = true;
				}

				// Save the pageindex
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));

				$state = ($pageindex->$page->protect ? "enabled" : "disabled");
				$title = "Page protection $state.";
				exit(page_renderer::render_main($title, "<p>Page protection for $env->page has been $state.</p><p><a href='?action=$env->defaultaction&page=$env->page'>Go back</a>."));
			}
			else
			{
				exit(page_renderer::render_main("Error protecting page", "<p>You are not allowed to protect pages because you are not logged in as a mod or admin. Please try logging out if you are logged in and then try logging in as an administrator.</p>"));
			}
		});
	}
]);

?>
