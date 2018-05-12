<?php
register_module([
	"name" => "User Organiser",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a organiser page that lets moderators (or better) control the reegistered user accounts, and perform adminstrative actions such as password resets, and adding / removing accounts.",
	"id" => "feature-user-table",
	"code" => function() {
		global $settings, $env;
		
		/**
		 * @api {get} ?action=user-table	Get the user table
		 * @apiName UserTable
		 * @apiGroup Settings
		 * @apiPermission Moderator
		 */
		
		/*
 	 	 * ██    ██ ███████ ███████ ██████
 		 * ██    ██ ██      ██      ██   ██
 		 * ██    ██ ███████ █████   ██████  █████
 		 * ██    ██      ██ ██      ██   ██
 		 *  ██████  ███████ ███████ ██   ██
 		 *
 		 * ████████  █████  ██████  ██      ███████
		 *    ██    ██   ██ ██   ██ ██      ██
 		 *    ██    ███████ ██████  ██      █████
 		 *    ██    ██   ██ ██   ██ ██      ██
 		 *    ██    ██   ██ ██████  ███████ ███████
		 */
		add_action("user-table", function() {
			global $settings, $env;
			
			if(!$env->is_logged_in || !$env->is_admin) {
				http_response_code(401);
				exit(page_renderer::render_main("Unauthorised - User Table - $settings->sitename", "<p>Only moderators (or better) may access the user table. You could try <a href='?action=logout'>logging out</a> and then <a href='?action=login&returnto%2Findex.php%3Faction%3Duser-table'>logging in</a> again as a moderator, or alternatively visit the <a href='?action=user-list'>user list</a> instead, if that's what you're after.</p>"));
			}
			
			$content = "<h2>User Table</h2>
			<p>(Warning! Deleting a user will wipe <em>all</em> their user data! It won't delete any pages they've created, their user page, or their avatar though, as those are part of the wiki itself.)</p>
			<table class='user-table'>
				<tr><th>Username</th><th>Email Address</th><th></th></tr>\n";
			
			foreach($settings->users as $username => $user_data) {
				$content .= "<tr>";
				$content .= "<td>" . page_renderer::render_username($username) . "</td>";
				if(!empty($user_data->email))
					$content .= "<td><a href='mailto:" . htmlentities($user_data->email, ENT_HTML5 | ENT_QUOTES) . "'>" . htmlentities($user_data->email) . "</a></td>\n";
				else
					$content .= "<td><em>(None provided)</em></td>\n";
				$content .= "<td>";
				if(module_exists("feature-user-preferences"))
					$content .= "<form method='post' action='?action=change-password' class='inline-form'>
						<input type='hidden' name='user' value='$username' />
						<input type='password' name='new-pass' placeholder='New password' />
						<input type='submit' value='Reset Password' />
					</form> | ";
				$content .= "<a href='?action=user-delete&user=" . rawurlencode($username) . "'>Delete User</a>";
				$content .= "</td></tr>";
			}
			
			$content .= "</table>\n";
			
			$content .= "<h3>Add User</h3>
			<form method='post' action='?action=user-add'>
				<input type='text' id='new-username' name='user' placeholder='Username' required />
				<input type='email' id='new-email' name='email' placeholder='Email address - optional' />
				<input type='submit' value='Add user' />
			</form>";
			
			exit(page_renderer::render_main("User Table - $settings->sitename", $content));
		});
		
		add_action("user-add", function() {
			global $settings, $env;
			
			if(!$env->is_admin) {
				http_response_code(401);
				exit(page_renderer::render_main("Error: Unauthorised - Add User - $settings->sitename", "<p>Only moderators (or better) may create users. You could try <a href='?action=logout'>logging out</a> and then <a href='?action=login&returnto%2Findex.php%3Faction%3Duser-table'>logging in</a> again as a moderator, or alternatively visit the <a href='?action=user-list'>user list</a> instead, if that's what you're after.</p>"));
			}
			
			if(!isset($_POST["user"])) {
				http_response_code(400);
				header("content-type: text/plain");
				exit("Error: No username specified in the 'user' post parameter.");
			}
			
			$new_username = $_POST["user"];
			$new_email = $_POST["email"] ?? null;
			
			if(preg_match('/[^0-9a-zA-Z\-_]/', $new_username) !== 0) {
				http_response_code(400);
				exit(page_renderer::render_main("Error: Invalid Username - Add User - $settings->sitename", "<p>The username <code>" . htmlentities($new_username) . "</code> contains some invalid characters. Only <code>a-z</code>, <code>A-Z</code>, <code>0-9</code>, <code>-</code>, and <code>_</code> are allowed in usernames. <a href='javascript:window.history.back();'>Go back</a>.</p>"));
			}
			if(!empty($new_email) && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
				http_response_code(400);
				exit(page_renderer::render_main("Error: Invalid Email Address - Add User - $settings->sitename", "<p>The email address <code>" . htmlentities($new_email) . "</code> appears to be invalid. <a href='javascript:window.history.back();'>Go back</a>.</p>"));
			}
			
			$new_password = generate_password($settings->new_password_length);
			
			$user_data = new stdClass();
			$user_data->password = hash_password($new_password);
			if(!empty($new_email))
				$user_data->email = $new_email;
			
			$settings->users->$new_username = $user_data;
			
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Error: Failed to save settings - Add User - $settings->sitename", "<p>$settings->sitename failed to save the new user's data to disk. Please contact $settings->admindetails_name for assistance (their email address can be found at the bottom of this page).</p>"));
			}
			
			
			$welcome_email_result = email_user($new_username, "Welcome!", "Welcome to $settings->sitename, {username}! $env->user has created you an account. Here are your details:

Url: " . substr(full_url(), 0, strrpos(full_url(), "?")) . "
Username: {username}
Password: $new_password

It is advised that you change your password as soon as you login. You can do this by clicking the cog next to your name once you've logged in, and scrolling to the 'change password' heading.

If you need any assistance, then the help page you can access at the bottom of every page on $settings->sitename has information on most aspects of $settings->sitename.


--$settings->sitename, powered by Pepperminty Wiki
https://github.com/sbrl/Pepperminty-Wiki/
");
			
			$content = "<h2>Add User</h2>
			<p>The new user was added to $settings->sitename sucessfully! Their details are as follows:</p>
			<ul>
				<li>Username: <code>$new_username</code></li>";
			if(!empty($new_email))
				$content .= "	<li>Email Address: <code>$new_email</code></li>\n";
			if(!$welcome_email_result)
				$content .= "	<li>Password: <code>$new_password</code></li>\n";
			$content .= "</ul>\n";
			if($welcome_email_result)
				$content .= "<p>An email has been sent to the email address given above containing their login details.</p>\n";
			
			$content .= "<p><a href='?action=user-table'>Go back</a> to the user table.</p>\n";
			
			http_response_code(201);
			exit(page_renderer::render_main("Add User - $settings->sitename", $content));
		});
		
		if($env->is_admin) add_help_section("949-user-table", "Managing User Accounts", "<p>As a moderator on $settings->sitename, you can use the <a href='?action=user-table'>User Table</a> to adminstrate the user accounts on $settings->sitename. It allows you to perform actions such as adding and removing accounts, and resetting passwords.</p>");
	}
]);
/**
 * Generates a new (cryptographically secure) random password that's also readable (i.e. consonant-vowel-consonant).
 * This implementation may be changed in the future to use random dictionary words instead - ref https://xkcd.com/936/
 * @param	string	$length	The length of password to generate.
 * @return	string	The generated random password.
 */
function generate_password($length) {
	$vowels = "aeiou";
	$consonants = "bcdfghjklmnpqrstvwxyz";
	$result = "";
	for($i = 0; $i < $length; $i++) {
		if($i % 2 == 0)
			$result .= $consonants[random_int(0, strlen($consonants) - 1)];
		else
			$result .= $vowels[random_int(0, strlen($vowels) - 1)];
	}
	return $result;
}
