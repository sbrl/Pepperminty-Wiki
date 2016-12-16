<?php
register_module([
	"name" => "User Preferences",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a user preferences page, letting pople do things like change their email address and password.",
	"id" => "feature-user-preferences",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=user-preferences Get a user preferences configuration page.
		 * @apiName UserPreferences
		 * @apiGroup Utility
		 * @apiPermission User
		 */
		
		 /*
 		 * ██    ██ ███████ ███████ ██████
 		 * ██    ██ ██      ██      ██   ██
 		 * ██    ██ ███████ █████   ██████  █████
 		 * ██    ██      ██ ██      ██   ██
 		 *  ██████  ███████ ███████ ██   ██
 		 * 
 		 * ██████  ██████  ███████ ███████ ███████
 		 * ██   ██ ██   ██ ██      ██      ██
 		 * ██████  ██████  █████   █████   ███████
 		 * ██      ██   ██ ██      ██           ██
 		 * ██      ██   ██ ███████ ██      ███████
 		 */
		add_action("user-preferences", function() {
			global $env;
			
			if(!$env->is_logged_in)
			{
				exit(page_renderer::render_main("Error  - $settings->sitename", "<p>Since you aren't logged in, you can't change your preferences. This is because stored preferences are tied to each registered user account. You can login <a href='?action=login&returnto=" . rawurlencode("?action=user-preferences") . "'>here</a>.</p>"));
			}
			
			$content = "<h2>User Preferences</h2>\n";
			$content .= "<label for='username'>Username:</label>\n";
			$content .= "<input type='text' name='username' value='$env->user' readonly />\n";
			$content .= "<h3>Change Password</h3\n>";
			$content .= "<form method='post' action='?action=change-password'>\n";
			$content .= "<label for='old-pass'>Old Password:</label>\n";
			$content .= "<input type='password' name='old-pass'  />\n";
			$content .= "<br />\n";
			$content .= "<label for='new-pass'>New Password:</label>\n";
			$content .= "<input type='password' name='new-pass' />\n";
			$content .= "<br />\n";
			$content .= "<label for='new-pass-confirm'>Confirm New Password:</label>\n";
			$content .= "<input type='password' name='new-pass-confirm' />\n";
			$content .= "</form>\n";
			
			exit(page_renderer::render_main("User Preferences - $settings->sitename", $content));
		});
		
		add_help_section("910-user-preferences", "User Preferences", "<p>(help text coming soon)</p>");
	}
]);

?>
