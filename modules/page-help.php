<?php
register_module([
	"name" => "Help page",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the help action. You really want this one.",
	"id" => "page-help",
	"code" => function() {
		global $settings;
		
		add_action("help", function() {
			global $settings, $version, $help_sections;
			
			$title = "Help - $settings->sitename";
			
			// Sort the help sections by key
			ksort($help_sections, SORT_NATURAL);
			
			$content .= "	<h1>$settings->sitename Help</h1>
		<p>Welcome to $settings->sitename!</p>
		<p>$settings->sitename is powered by Pepperminty wiki, a complete wiki in a box you can drop into your server.</p>";
			
			// todo Insert a tabel of contents here?
			
			foreach($help_sections as $index => $section)
			{
				// Todo add a button that you can click to get a permanent link
				// to this section.
				$content .= "<h2 id='$index'>" . $section["title"];
				$content .= $section["content"] . "\n";
			}
			
			$content = "	<h1>$settings->sitename Help</h1>
	<h2>Administrator Actions</h2>
	<p>By default, the <code>delete</code> and <code>move</code> actions are shown on the nav bar. These can be used by administrators to delete or move pages.</p>
	<p>The other thing admininistrators can do is update the wiki (provided they know the site's secret). This page can be found here: <a href='?action=update'>Update $settings->sitename</a>.</p>
	<p>$settings->sitename is currently running on Pepperminty Wiki <code>$version</code></p>";
			exit(page_renderer::render_main($title, $content));
		});
		
		// Register a help section on general navigation
		add_help_section("5-navigation", "Navigation", "<h2>Navigating</h2>
		<p>All the navigation links can be found on the top bar, along with a search box (if your site administrator has enabled it). There is also a &quot;More...&quot; menu in the top right that contains some additional links that you may fine useful.</p>
		<p>This page, along with the credits page, can be found on the bar at the bottom of every page.</p>");
	}
]);

?>
