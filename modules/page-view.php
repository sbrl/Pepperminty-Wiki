<?php
register_module([
	"name" => "Page viewer",
	"version" => "0.10",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to view pages. You reallyshould include this one.",
	"id" => "page-view",
	"code" => function() {
		add_action("view", function() {
			global $pageindex, $settings, $env, $parse_page_source;
			
			// Check to make sure that the page exists
			$page = $env->page;
			if(!isset($pageindex->$page))
			{
				// todo make this intelligent so we only redirect if the user is acutally able to create the page
				if($settings->editing)
				{
					// Editing is enabled, redirect to the editing page
					http_response_code(307); // Temporary redirect
					header("location: index.php?action=edit&newpage=yes&page=" . rawurlencode($env->page));
					exit();
				}
				else
				{
					// Editing is disabled, show an error message
					http_response_code(404);
					exit(page_renderer::render_main("$env->page - 404 - $settings->sitename", "<p>$env->page does not exist.</p><p>Since editing is currently disabled on this wiki, you may not create this page. If you feel that this page should exist, try contacting this wiki's Administrator.</p>"));
				}
			}
			$title = "$env->page - $settings->sitename";
			if(isset($pageindex->$page->protect) && $pageindex->$page->protect === true)
				$title = $settings->protectedpagechar . $title;
			$content = "<h1>$env->page</h1>";
			
			$parsing_start = microtime(true);
			
			$content .= $parse_page_source(file_get_contents("$env->page.md"));
			
			if($settings->show_subpages)
			{
				$subpages = get_object_vars(get_subpages($pageindex, $env->page));
				
				if(count($subpages) > 0)
				{
					$content .= "<hr />";
					$content .= "Subpages: ";
					foreach($subpages as $subpage => $times_removed)
					{
						if($times_removed <= $settings->subpages_display_depth)
						{
							$content .= "<a href='?action=view&page=" . rawurlencode($subpage) . "'>$subpage</a>, ";
						}
					}
					// Remove the last comma from the content
					$content = substr($content, 0, -2);
				}
			}
			
			$content .= "\n\t\t<!-- Took " . (microtime(true) - $parsing_start) . " seconds to parse page source -->\n";
			
			if(isset($_GET["printable"]) and $_GET["printable"] === "yes")
				exit(page_renderer::render_minimal($title, $content));
			else
				exit(page_renderer::render_main($title, $content));
		});
	}
]);

?>
