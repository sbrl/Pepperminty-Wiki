<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "User list",
	"version" => "0.1.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'user-list' action that generates a list of users. Supports json output with 'format=json' in the queyr string.",
	"id" => "page-user-list",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=user-list[&format=json] List all users
		 * @apiName UserList
		 * @apiGroup Utility
		 * @apiPermission Anonymous
		 */
		
		/*
		 * ██    ██ ███████ ███████ ██████        ██      ██ ███████ ████████
		 * ██    ██ ██      ██      ██   ██       ██      ██ ██         ██
		 * ██    ██ ███████ █████   ██████  █████ ██      ██ ███████    ██
		 * ██    ██      ██ ██      ██   ██       ██      ██      ██    ██
		 *  ██████  ███████ ███████ ██   ██       ███████ ██ ███████    ██
		 */
		add_action("user-list", function() {
			global $env, $settings;
			
			$userList = array_keys(get_object_vars($settings->users));
			if(!empty($_GET["format"]) && $_GET["format"] === "json")
			{
				header("content-type: application/json");
				exit(json_encode($userList));
			}
			
			$content = "<h1>User List</h1>\n";
			$content .= "<ul class='page-list user-list invisilist'>\n";
			foreach($userList as $username)
				$content .= "\t<li>" . page_renderer::render_username($username) . "</li>\n";
			$content .= "</ul>\n";
			
			exit(page_renderer::render_main("User List - $settings->sitename", $content));
		});
		
		add_help_section("18-user-list", "User list", "<p>$settings->sitename has a page that lists all the users on the site. You can access it here: <a href='?action=user-list'>user list</a>.</p>");
	}
]);

?>
