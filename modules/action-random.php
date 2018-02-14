<?php
register_module([
	"name" => "Random Page",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action called 'random' that redirects you to a random page.",
	"id" => "action-random",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=random[&mode={modeName}] Redirects to a random page
		 * @apiName Random
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 *
		 * @apiParam	{string}	mode	The view mode to redirect to. This parameter is basically just passed through to the direct. It works in the same way as the mode parameter on the view action does.
		 */
		
		add_action("random", function() {
			global $pageindex;
			
			$mode = preg_replace("/[^a-z-_]/i", "", $_GET["mode"] ?? "");
			
			$pageNames = array_keys(get_object_vars($pageindex));
			
			// Filter out pages we shouldn't send the user to
			$pageNames = array_values(array_filter($pageNames, function($pagename) {
				global $settings;
				return preg_match($settings->random_page_exclude, $pagename) === 0 ? true : false;
			}));
			
			$randomPageName = $pageNames[array_rand($pageNames)];
			
			http_response_code(307);
			$redirect_url = "?page=" . rawurlencode($randomPageName);
			if(!empty($mode)) $redirect_url .= "&mode=$mode";
			header("location: $redirect_url");
		});
		
		add_help_section("26-random-redirect", "Jumping to a random page", "<p>$settings->sitename has a function that can send you to a random page. To use it, click <a href='?action=random'>here</a>. $settings->admindetails_name ($settings->sitename's adminstrator) may have added it to one of the menus.</p>");
	}
]);

?>
