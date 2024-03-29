<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Page protection",
	"version" => "0.2.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Exposes Pepperminty Wiki's new page protection mechanism and makes the protect button in the 'More...' menu on the top bar work.",
	"id" => "action-protect",
	"code" => function() {
		/**
		 * @api {get} ?action=protect&page={pageName} Toggle the protection of a page.
		 * @apiName Protect
		 * @apiGroup Page
		 * @apiPermission Moderator
		 * 
		 * @apiParam {string}	page	The page name to toggle the protection of.
		 */
		
		/*
		 * ██████  ██████   ██████  ████████ ███████  ██████ ████████ 
		 * ██   ██ ██   ██ ██    ██    ██    ██      ██         ██    
		 * ██████  ██████  ██    ██    ██    █████   ██         ██    
		 * ██      ██   ██ ██    ██    ██    ██      ██         ██    
		 * ██      ██   ██  ██████     ██    ███████  ██████    ██    
		 */
		add_action("protect", function() {
			global $env, $pageindex, $paths, $settings;

			// Make sure that the user is logged in as an admin / mod.
			if($env->is_admin)
			{
				// They check out ok, toggle the page's protection.
				$page = $env->page;
				
				if(!isset($pageindex->$page->protect))
				{
					$pageindex->$page->protect = true;
				}
				else if($pageindex->$page->protect === true)
				{
					$pageindex->$page->protect = false;
				}
				else if($pageindex->$page->protect === false)
				{
					$pageindex->$page->protect = true;
				}
				
				// Save the pageindex
				save_pageindex();
				
				$state = ($pageindex->$page->protect ? "enabled" : "disabled");
				$title = "Page protection $state.";
				exit(page_renderer::render_main($title, "<p>Page protection for $env->page_safe has been $state.</p><p><a href='?action=$settings->defaultaction&page=".rawurlencode($env->page)."'>Go back</a>."));
			}
			else
			{
				exit(page_renderer::render_main("Error protecting page", "<p>You are not allowed to protect pages because you are not logged in as a mod or admin. Please try logging out if you are logged in and then try logging in as an administrator.</p>"));
			}
		});
	}
]);

?>
