<?php
register_module([
	"name" => "Page viewer",
	"version" => "0.16.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to view pages. You really should include this one.",
	"id" => "page-view",
	"code" => function() {
		/**
		 * @api	{get}	?action=view[&page={pageName}][&revision=rid][&printable=yes]	View a page
		 * @apiName			View
		 * @apiGroup		Page
		 * @apiPermission	Anonymous
		 * 
		 * @apiUse PageParameter
		 * @apiParam	{number}	revision	The revision number to display.
		 * @apiParam	{string}	mode		Optional. The display mode to use. Can hld the following values: 'normal' - The default. Sends a normal page. 'printable' - Sends a printable version of the page. 'contentonly' - Sends only the content of the page, not the extra stuff around it. 'parsedsourceonly' - Sends only the raw rendered source of the page, as it appears just after it has come out of the page parser. Useful for writing external tools (see also the `raw` action).
		 *
		 * @apiError	NonExistentPageError	The page doesn't exist and editing is disabled in the wiki's settings. If editing isn't disabled, you will be redirected to the edit page instead.
		 * @apiError	NonExistentRevisionError	The specified revision was not found.
		 */
		
		/*
		 * ██    ██ ██ ███████ ██     ██ 
		 * ██    ██ ██ ██      ██     ██ 
		 * ██    ██ ██ █████   ██  █  ██ 
		 *  ██  ██  ██ ██      ██ ███ ██ 
		 *   ████   ██ ███████  ███ ███  
		 */
		add_action("view", function() {
			global $pageindex, $settings, $env;
			
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
					exit(page_renderer::render_main("404: Page not found - $env->page - $settings->sitename", "<p>$env->page does not exist.</p><p>Since editing is currently disabled on this wiki, you may not create this page. If you feel that this page should exist, try contacting this wiki's Administrator.</p>"));
				}
			}
			
			// Perform a redirect if the requested page is a redirect page
			if(isset($pageindex->$page->redirect) &&
			   $pageindex->$page->redirect === true)
			{
				$send_redirect = true;
				if(isset($_GET["redirect"]) && $_GET["redirect"] == "no")
					$send_redirect = false;
				
				if($send_redirect)
				{
					// Todo send an explanatory page along with the redirect
					http_response_code(307);
					$redirectUrl = "?action=$env->action&redirected_from=$env->page";
					
					$hashCode = "";
					$newPage = $pageindex->$page->redirect_target;
					if(strpos($newPage, "#") !== false)
					{
						// Extract the part after the hash symbol
						$hashCode = substr($newPage, strpos($newPage, "#") + 1);
						// Remove the hash from the new page name
						$newPage = substr($newPage, 0, strpos($newPage, "#"));
					}
					$redirectUrl .= "&page=$newPage";
					if(!empty($pageindex->$newPage->redirect))
						$redirectUrl .= "&redirect=no";
					if(strlen($hashCode) > 0)
						$redirectUrl .= "#$hashCode";
					
					header("location: $redirectUrl");
					exit();
				}
			}
			
			$title = "$env->page - $settings->sitename";
			if(isset($pageindex->$page->protect) && $pageindex->$page->protect === true)
				$title = $settings->protectedpagechar . $title;
			$content = "";
			if(!$env->is_history_revision)
				$content .= "<h1>$env->page</h1>\n";
			else
			{
				$content .= "<h1>Revision #{$env->history->revision_number} of $env->page</h1>\n";
				$content .= "<p class='revision-note'><em>(Revision saved by {$env->history->revision_data->editor} " . render_timestamp($env->history->revision_data->timestamp) . ". <a href='?page=" . rawurlencode($env->page) . "'>Jump to the current revision</a> or see a <a href='?action=history&page=" . rawurlencode($env->page) . "'>list of all revisions</a> for this page.)</em></p>\n";
			}
			
			// Add an extra message if the requester was redirected from another page
			if(isset($_GET["redirected_from"]))
				$content .= "<p><em>Redirected from <a href='?page=" . rawurlencode($_GET["redirected_from"]) . "&redirect=no'>" . $_GET["redirected_from"] . "</a>.</em></p>";
			
			$parsing_start = microtime(true);
			
			$rawRenderedSource = parse_page_source(file_get_contents($env->page_filename));
			$content .= $rawRenderedSource;
			
			if(!empty($pageindex->$page->tags))
			{
				$content .= "<ul class='page-tags-display'>\n";
				foreach($pageindex->$page->tags as $tag)
				{
					$content .= "<li><a href='?action=list-tags&tag=" . rawurlencode($tag) . "'>$tag</a></li>\n";
				}
				$content .= "\n</ul>\n";
			}
			/*else
			{
				$content .= "<aside class='page-tags-display'><small><em>(No tags yet! Add some by <a href='?action=edit&page=" . rawurlencode($env->page) .  "'>editing this page</a>!)</em></small></aside>\n";
			}*/
			
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
			
			$content .= "\n\t\t<!-- Took " . round((microtime(true) - $parsing_start) * 1000, 2) . "ms to parse page source -->\n";
			
			// Prevent indexing of this page if it's still within the noindex
			// time period
			if(isset($settings->delayed_indexing_time) and
				time() - $pageindex->{$env->page}->lastmodified < $settings->delayed_indexing_time)
				header("x-robots-tag: noindex");
			
			$settings->footer_message = "Last edited at " . date('h:ia T \o\n j F Y', $pageindex->{$env->page}->lastmodified) . ".</p>\n<p>" . $settings->footer_message; // Add the last edited time to the footer
			
			$mode = isset($_GET["mode"]) ? strtolower(trim($_GET["mode"])) : "normal";
			switch($mode)
			{
				case "contentonly":
					// Content only mode: Send only the content of the page
					exit($content);
				case "parsedsourceonly":
					// Parsed source only mode: Send only the raw rendered source
					exit($rawRenderedSource);
				case "printable":
					// Printable mode: Sends a printable version of the page
					exit(page_renderer::render_minimal($title, $content));
				case "normal":
				default:
					// Normal mode: Send a normal page
					exit(page_renderer::render_main($title, $content));
			}
		});
	}
]);

?>
