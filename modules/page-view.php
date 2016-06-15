<?php
register_module([
	"name" => "Page viewer",
	"version" => "0.14",
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
		 * @apiParam	{string}	printable	Set to 'yes' to get a printable version of the specified page instead.
		 *
		 * @apiError	NonExistentPageError	The page doesn't exist and editing is disabled in the wiki's settings. If editing isn't disabled, you will be redirected to the edit apge instead.
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
					header("location: ?action=$env->action&page=" . $pageindex->$page->redirect_target . "&redirected_from=$env->page");
					exit();
				}
			}
			
			$isHistoryRevision = false;
			if(isset($_GET["revision"]) and is_numeric($_GET["revision"]))
			{
				// We have a revision number!
				$isHistoryRevision = true;
				$revisionNumber = intval($_GET["revision"]);
				$revisionData = $pageindex->{$env->page}->history[$revisionNumber];
				
				// Make sure that the revision exists for later on
				if(!isset($pageindex->{$env->page}->history[$revisionNumber]))
				{
					http_response_code(404);
					exit(page_renderer::render_main("404: Revision Not Found - $env->page - $settings->sitename", "<p>Revision #$revisionNumer of $env->page doesn't appear to exist. Try viewing the <a href='?action=history&page=" . rawurlencode($env->page) . "'>list of revisions for $env->page</a>, or viewing <a href='?page=" . rawurlencode($env->page) . "'>the latest revision</a> instead.</p>"));
				}
			}
			
			
			$title = "$env->page - $settings->sitename";
			if(isset($pageindex->$page->protect) && $pageindex->$page->protect === true)
				$title = $settings->protectedpagechar . $title;
			$content = "";
			if(!$isHistoryRevision)
				$content .= "<h1>$env->page</h1>\n";
			else
			{
				$content .= "<h1>Revision #$revisionNumber of $env->page</h1>\n";
				$content .= "<p class='revision-note'><em>(Revision created by $revisionData->editor " . render_timestamp($revisionData->timestamp) . ". <a href='?page=" . rawurlencode($env->page) . "'>Jump to the current revision</a> or see a <a href='?action=history&page=" . rawurlencode($env->page) . "'>list of all revisions</a> for this page.)</em></p>\n";
			}
			
			// Add an extra message if the requester was redirected from another page
			if(isset($_GET["redirected_from"]))
				$content .= "<p><em>Redirected from <a href='?page=" . rawurlencode($_GET["redirected_from"]) . "&redirect=no'>" . $_GET["redirected_from"] . "</a>.</em></p>";
			
			// Construct the filename
			$pageFilename = "$env->storage_prefix";
			if($isHistoryRevision)
			{
				$pageFilename .= $pageindex->{$env->page}->history[$revisionNumber]->filename;
			}
			else
				$pageFilename .= $pageindex->{$env->page}->filename;
			
			$parsing_start = microtime(true);
			
			$content .= parse_page_source(file_get_contents($pageFilename));
			
			if(!empty($pageindex->$page->tags))
			{
				$content .= "<ul class='page-tags-display'>\n";
				foreach($pageindex->$page->tags as $tag)
				{
					$content .= "<li><a href='?action=list-tags&tag=$tag'>$tag</a></li>\n";
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
			
			// Content only mode: Send only the raw rendered page
			if(isset($_GET["contentonly"]) and $_GET["contentonly"] === "yes")
				exit(parse_page_source($content));
			// Printable: Sends a printable version of the page
			if(isset($_GET["printable"]) and $_GET["printable"] === "yes")
				exit(page_renderer::render_minimal($title, $content));
			// Normal page
			$settings->footer_message = "Last edited at " . date('h:ia T \o\n j F Y', $pageindex->{$env->page}->lastmodified) . ".</p>\n<p>"; // Add the last edited time to the footer
			exit(page_renderer::render_main($title, $content));
		});
	}
]);

?>
