<?php
register_module([
	"name" => "Help page",
	"version" => "0.9.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a rather useful help page. Access through the 'help' action. This module also exposes help content added to Pepperminty Wiki's inbuilt invisible help section system.",
	"id" => "page-help",
	"code" => function() {
		global $settings;
		
		/**
		 * @api		{get}	?action=help[&dev=yes]	Get a help page
		 * @apiDescription	Get a customised help page. This page will be slightly different for every wiki, depending on their name, settings, and installed modules.
		 * @apiName		Help
		 * @apiGroup	Utility
		 * @apiPermission	Anonymous
		 *
		 * @apiParam	{string}	dev		Set to 'yes' to get a developer help page instead. The developer help page gives some general information about which modules and help page sections are registered, and other various (non-sensitive) settings.
		 */
		
		/*
		 * ██   ██ ███████ ██      ██████  
		 * ██   ██ ██      ██      ██   ██ 
		 * ███████ █████   ██      ██████  
		 * ██   ██ ██      ██      ██      
		 * ██   ██ ███████ ███████ ██      
		 */
		add_action("help", function() {
			global $env, $paths, $settings, $version, $help_sections, $actions;
			
			// Sort the help sections by key
			ksort($help_sections, SORT_NATURAL);
			
			if(isset($_GET["dev"]) and $_GET["dev"] == "yes")
			{
				$title = "Developers Help - $settings->sitename";
				$content = "<p>$settings->sitename runs on Pepperminty Wiki, an entire wiki packed into a single file. This page contains some information that developers may find useful.</p>
				<p>A full guide to developing a Pepperminty Wiki module can be found <a href='//github.com/sbrl/Pepperminty-Wiki/blob/master/Module_API_Docs.md#module-api-documentation'>on GitHub</a>.</p>
				<h3>Registered Help Sections</h3>
				<p>The following help sections are currently registered:</p>
				<table><tr><th>Index</th><th>Title</th><th>Length</th></tr>\n";
				$totalSize = 0;
				foreach($help_sections as $index => $section)
				{
					$sectionLength = strlen($section["content"]);
					$totalSize += $sectionLength;
					
					$content .= "\t\t\t<tr><td>$index</td><td>" . $section["title"] . "</td><td>" . human_filesize($sectionLength) . "</td></tr>\n";
				}
				$content .= "\t\t\t<tr><th colspan='2' style='text-align: right;'>Total:</th><td>" . human_filesize($totalSize) . "</td></tr>\n";
				$content .= "\t\t</table>\n";
				$content .= "<h3>Registered Actions</h3>
				<p>The following actions are currently registered:</p>\n";
				$content .= "<p>" . implode(", ", array_keys(get_object_vars($actions))) . "</p>";
				$content .= "<h3>Environment</h3>\n";
				$content .= "<ul>\n";
				$content .= "<li>$settings->sitename's root directory is " . (!is_writeable(__DIR__) ? "not " : "") . "writeable.</li>\n";
				$content .= "<li>The page index is currently " . human_filesize(filesize($paths->pageindex)) . " in size, and took " . $env->perfdata->pageindex_decode_time . "ms to decode.</li>";
				if(module_exists("feature-search"))
				{
					search::measure_invindex_load_time($paths->searchindex);
					$content .= "<li>The search index is currently " . human_filesize(filesize($paths->searchindex)) . " in size, and took " . $env->perfdata->searchindex_decode_time . "ms to decode.</li>";
				}
				
				$content .= "<li>The id index is currently " . human_filesize(filesize($paths->idindex)) . " in size, and took " . $env->perfdata->idindex_decode_time . "ms to decode.</li>";
				
				$wikiSize = 0;
				$wikiFiles = glob_recursive($env->storage_prefix . "*");
				foreach($wikiFiles as $filename)
				{
					if(endsWith($filename, ".php")) continue; // Skip php files
					$wikiSize += filesize($filename);
				}
				
				$content .= "<li>$settings->sitename is currently " . human_filesize($wikiSize) . " in size.</li>\n";
				
				$content .= "</ul>";
			}
			else
			{
				$title = "Help - $settings->sitename";
				
				$content = "	<h1>$settings->sitename Help</h1>
		<p>Welcome to $settings->sitename!</p>
		<p>$settings->sitename is powered by Pepperminty Wiki, a complete wiki in a box you can drop into your server and expect it to just <em>work</em>.</p>";
				
				// Todo Insert a table of contents here?
				
				foreach($help_sections as $index => $section)
				{
					// Todo add a button that you can click to get a permanent link
					// to this section.
					$content .= "<h2 id='$index' class='help-section-header'>" . $section["title"] . "</h2>\n";
					$content .= $section["content"] . "\n";
				}
			}
			
			exit(page_renderer::render_main($title, $content));
		});
		
		// Register a help section on general navigation
		add_help_section("5-navigation", "Navigating", "<p>All the navigation links can be found on the top bar, along with a search box (if your site administrator has enabled it). There is also a &quot;More...&quot; menu in the top right that contains some additional links that you may fine useful.</p>
		<p>This page, along with the credits page, can be found on the bar at the bottom of every page.</p>");
		
		add_help_section("999-extra", "Extra Information", "<p>You can find out whch version of Pepperminty Wiki $settings->sitename is using by visiting the <a href='?action=credits'>credits</a> page.</p>
		<p>Information for developers can be found on <a href='?action=help&dev=yes'>this page</a>.</p>");
	}
]);

?>
