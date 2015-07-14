<?php
register_module([
	"name" => "Page viewer",
	"version" => "0.8",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to view pages. You should include this one.",
	"id" => "page-view",
	"code" => function() {
		add_action("view", function() {
			global $pageindex, $settings, $page, $parse_page_source;
			
			//check to make sure that the page exists
			if(!isset($pageindex->$page))
			{
				// todo make this intelligent so we only redirect if the user is acutally able to create the page
				if($settings->editing)
				{
					//editing is enabled, redirect to the editing page
					http_response_code(307); // Temporary redirect
					header("location: index.php?action=edit&newpage=yes&page=" . rawurlencode($page));
					exit();
				}
				else
				{
					//editing is disabled, show an error message
					http_response_code(404);
					exit(page_renderer::render_main("$page - 404 - $settings->sitename", "<p>$page does not exist.</p><p>Since editing is currently disabled on this wiki, you may not create this page. If you feel that this page should exist, try contacting this wiki's Administrator.</p>"));
				}
			}
			$title = "$page - $settings->sitename";
			$content = "<h1>$page</h1>";
			
			$parsing_start = microtime(true);
			
			$content .= $parse_page_source(file_get_contents("$page.md"));
			
			if($settings->show_subpages)
			{
				$subpages = get_object_vars(get_subpages($pageindex, $page));
				
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
