<?php
register_module([
	"name" => "Sidebar",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "",
	"id" => "extra-sidebar",
	"code" => function() {
		$show_sidebar = false;
		
		// Show the sidebar if it is enabled in the settings
		if(isset($settings->sidebar_show) && $settings->sidebar_show === true)
			$show_sidebar = true;
		
		// Also show and persist the sidebar if the special GET parameter
		// sidebar is seet
		if(!$show_sidebar && isset($_GET["sidebar"]))
		{
			$show_sidebar = true;
			// Set a cookie to persist the display of the sidebar
			setcookie("sidebar_show", "true", 60 * 60 * 24 * 30);
		}
		
		// Show the sidebar if the cookie is set
		if(!$show_sidebar && isset($_COOKIE["sidebar_show"]))
			$show_sidebar = true;
		
		// Delete the cookie and hide the sidebar if the special GET paramter
		// nosidebar is set
		if(isset($_GET["nosidebar"]))
		{
			$show_sidebar = false;
			unset($_COOKIE["sidebar_show"]);
			setcookie("sidebar_show", null, time() - 3600);
		}
		
		page_renderer::register_part_preprocessor(function(&$parts) use ($show_sidebar) {
			global $settings;
			
			if($show_sidebar)
			{
				// Show the sidebar
				$sidebar_contents = "Testing";
				$parts["{body}"] = "<aside class='sidebar'>$sidebar_contents</aside>
<div>" . $parts["{body}"] . "</div>";
			}
		});
	}
]);

?>
