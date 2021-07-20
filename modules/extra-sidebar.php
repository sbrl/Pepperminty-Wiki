<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Sidebar",
	"version" => "0.3.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a sidebar to the left hand side of every page. Add '\$settings->sidebar_show = true;' to your configuration, or append '&sidebar=yes' to the url to enable. Adding to the url sets a cookie to remember your setting.",
	"id" => "extra-sidebar",
	"code" => function() {
		global $settings;
		
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
			send_cookie("sidebar_show", "true", time() + (60 * 60 * 24 * 30));
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
			send_cookie("sidebar_show", null, time() - 3600);
		}
		
		page_renderer::register_part_preprocessor(function(&$parts) use ($show_sidebar) {
			global $settings, $pageindex, $env;
			
			// Don't render a sidebar if the user is logging in and a login is
			// required in order to view pages.
			if($settings->require_login_view && in_array($env->action, [ "login", "checklogin" ]))
				return false;
			
			if($show_sidebar && !isset($_GET["printable"]))
			{
				// Show the sidebar
				$exec_start = microtime(true);
				
				// Sort the pageindex
				$sorted_pageindex = get_object_vars($pageindex);
				
				$sorter = new Collator("");
				uksort($sorted_pageindex, function($a, $b) use($sorter) : int {
					return $sorter->compare($a, $b);
				});
				
				$sidebar_contents = "";
				$sidebar_contents .= render_sidebar($sorted_pageindex);
				
				$parts["{body}"] = "<aside class='sidebar'>
			$sidebar_contents
			<!-- Sidebar rendered in " . (microtime(true) - $exec_start) . "s -->
		</aside>
		<div class='main-container'>" . $parts["{body}"] . "</div>
		<!-------------->
		<style>
			body { display: flex; }
			.main-container { flex: 1; }
		</style>";
			}
		});
		
		add_help_section("50-sidebar", "Sidebar", "<p>$settings->sitename has an optional sidebar which displays a list of all the current pages and their respective subpages that it is currently hosting in a tree like structure. It may or may not be enabled.</p>
		<p>If it isn't enabled, it can be enabled for your current browser only by appending <code>sidebar=yes</code> to the current page's query string.</p>
		<p>If it is enabled, it can be disabled for your current browser only by appending <code>nosidebar</code> to the current page's query string.</p>");
	}
]);

/**
 * Renders the sidebar for a given pageindex.
 * @package	extra-sidebar
 * @param	array		$pageindex		The pageindex to render the sidebar for
 * @param	string		$root_pagename	The pagename that should be considered the root of the rendering. You don't usually need to use this, it is used by the algorithm itself since it is recursive.
 * @return	string		A HTML rendering of the sidebar for the given pageindex.
 */
function render_sidebar($pageindex, $root_pagename = "", $depth = 0)
{
	global $settings;
	
	if($depth > $settings->sidebar_maxdepth)
		return null;
	
	if(mb_strlen($root_pagename) > 0) $root_pagename .= "/";
	
	$result = "<ul";
	// If this is the very root of the tree, add an extra class to it
	if($root_pagename == "") $result .= " class='sidebar-tree'";
	$result .=">";
	$subpages_added = 0;
	foreach ($pageindex as $pagename => $details)
	{
		// If we have a valid root pagename, and it isn't present at the
		// beginning of the current pagename, skip it
		if($root_pagename !== "" && strpos($pagename, $root_pagename) !== 0)
			continue;
		
		// The current page is the same as the root page, skip it
		if($pagename == $root_pagename)
			continue;

		// If the page already appears on the sidebar, skip it
		if(strpos($result, ">$pagename<\a>") !== false)
			continue;
		
		$pagename_relative = substr($pagename, strlen($root_pagename));
		
		// If the part of the current pagename that comes after the root
		// pagename has a slash in it, skip it as it is a sub-sub page.
		if(strpos($pagename_relative, "/") !== false)
			continue;
		
		$subpage_sidebar = render_sidebar($pageindex, $pagename, $depth + 1);
		
		if($subpage_sidebar === null) {
			$result .= "<li><a href='?action=$settings->defaultaction&page=$pagename'>$pagename_relative</a></li>";
		}
		else {
			$result .= "<li><details open>
				<summary><a href='?action=$settings->defaultaction&page=$pagename'>$pagename_relative</a></summary>
					$subpage_sidebar
				</details></li>\n";
		}
		$subpages_added++;
	}
	$result .= "</ul>\n";
	
	if($subpages_added === 0) return null;
	
	return $result == "<ul></ul>\n" ? "" : $result;
}

?>
