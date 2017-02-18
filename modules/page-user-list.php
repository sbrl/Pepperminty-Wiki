<?php
register_module([
	"name" => "User list",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'user-list' action that generates a list of users. Supports json output with 'format=json' in the queyr string.",
	"id" => "page-user-list",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=user-list[format=json] List all users
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
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
