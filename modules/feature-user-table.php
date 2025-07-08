<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "User Organiser",
	"version" => "0.1.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a organiser page that lets moderators (or better) control the registered user accounts, and perform administrative actions such as password resets, and adding / removing accounts.",
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
				exit(page_renderer::render_main("Unauthorised - User Table - $settings->sitename", "<p>Only moderators (or better) may access the user table. You could try <a href='?action=logout'>logging out</a> and then <a href='?action=login&returnto=index.php%3Faction%3Duser-table'>logging in</a> again as a moderator, or alternatively visit the <a href='?action=user-list'>user list</a> instead, if that's what you're after.</p>"));
			}
			
			$content = "<h2>User Table</h2>
			<p>(Warning! Deleting a user will wipe <em>all</em> their user data! It won't delete any pages they've created, their user page, or their avatar though, as those are part of the wiki itself.)</p>
			<table class='user-table'>
				<tr><th>Username</th><th>Email Address</th><th></th></tr>\n";
			
			foreach($settings->users as $username => $user_data) {
				$content .= "<tr>";
				$content .= "<td>" . page_renderer::render_username($username) . "</td>";
				if(!empty($user_data->emailAddress))
					$content .= "<td><a href='mailto:" . htmlentities($user_data->emailAddress, ENT_HTML5 | ENT_QUOTES) . "'>" . htmlentities($user_data->emailAddress) . "</a></td>\n";
				else
					$content .= "<td><em>(None provided)</em></td>\n";
				$content .= "<td>";
				if(module_exists("feature-user-preferences"))
					$content .= "<form method='post' action='?action=set-password' class='inline-form'>
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
		
		/**
		 * @api {post} ?action=user-add	Create a user account
		 * @apiName UserAdd
		 * @apiGroup Settings
		 * @apiPermission Moderator
		 *
		 * @apiParam	{string}	user	The username for the new user.
		 * @apiParam	{string}	email	Optional. Specifies the email address for the new user account.
		 */
		
		/*
 		 * ██    ██ ███████ ███████ ██████         █████  ██████  ██████
 		 * ██    ██ ██      ██      ██   ██       ██   ██ ██   ██ ██   ██
 		 * ██    ██ ███████ █████   ██████  █████ ███████ ██   ██ ██   ██
 		 * ██    ██      ██ ██      ██   ██       ██   ██ ██   ██ ██   ██
 		 *  ██████  ███████ ███████ ██   ██       ██   ██ ██████  ██████
		 */
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
				$user_data->emailAddress = $new_email;
			
			$settings->users->$new_username = $user_data;
			
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Error: Failed to save settings - Add User - $settings->sitename", "<p>$settings->sitename failed to save the new user's data to disk. Please contact ".htmlentities($settings->admindetails_name)." for assistance (their email address can be found at the bottom of this page).</p>"));
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
			<p>The new user was added to $settings->sitename successfully! Their details are as follows:</p>
			<ul>
				<li>Username: <code>$new_username</code></li>";
			if(!empty($new_email))
				$content .= "	<li>Email Address: <code>".htmlentities($new_email)."</code></li>\n";
			if(!$welcome_email_result)
				$content .= "	<li>Password: <code>".htmlentities($new_password)."</code></li>\n";
			$content .= "</ul>\n";
			if($welcome_email_result)
				$content .= "<p>An email has been sent to the email address given above containing their login details.</p>\n";
			
			$content .= "<p><a href='?action=user-table'>Go back</a> to the user table.</p>\n";
			
			http_response_code(201);
			exit(page_renderer::render_main("Add User - $settings->sitename", $content));
		});
		
		
		/**
		 * @api {post} ?action=set-password	Set a user's password
		 * @apiName UserAdd
		 * @apiGroup Settings
		 * @apiPermission Moderator
		 *
		 * @apiParam	{string}	user		The username of the account to set the password for.
		 * @apiParam	{string}	new-pass	The new password for the specified username.
		 */
		
		/*
 		 * ███████ ███████ ████████
 		 * ██      ██         ██
 		 * ███████ █████      ██ █████
 		 *      ██ ██         ██
 		 * ███████ ███████    ██
 		 * 
 		 * ██████   █████  ███████ ███████ ██     ██  ██████  ██████  ██████
 		 * ██   ██ ██   ██ ██      ██      ██     ██ ██    ██ ██   ██ ██   ██
 		 * ██████  ███████ ███████ ███████ ██  █  ██ ██    ██ ██████  ██   ██
 		 * ██      ██   ██      ██      ██ ██ ███ ██ ██    ██ ██   ██ ██   ██
 		 * ██      ██   ██ ███████ ███████  ███ ███   ██████  ██   ██ ██████
		 */
		add_action("set-password", function() {
			global $env, $settings;
			
			if(!$env->is_admin) {
				http_response_code(401);
				exit(page_renderer::render_main("Error - Set Password - $settings->sitename", "<p>Error: You aren't logged in as a moderator, so you don't have permission to set a user's password.</p>"));
			}
			if(empty($_POST["user"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Error - Set Password - $settings->sitename", "<p>Error: No username was provided via the 'user' POST parameter.</p>"));
			}
			if(empty($_POST["new-pass"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Error - Set Password - $settings->sitename", "<p>Error: No password was provided via the 'new-pass' POST parameter.</p>"));
			}
			
			if(empty($settings->users->{$_POST["user"]})) {
				http_response_code(404);
				exit(page_renderer::render_main("User not found - Set Password - $settings->sitename", "<p>Error: No user called '".htmlentities($_POST["user"])."' was found, so their password can't be set. Perhaps you forgot to create the user first?</p>"));
			}
			
			$settings->users->{$_POST["user"]}->password = hash_password($_POST["new-pass"]);
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Server Error - Set Password - $settings->sitename", "<p>Error: $settings->sitename couldn't save the settings back to disk! Nothing has been changed. Please context ".htmlentities($settings->admindetails_name).", whose email address can be found at the bottom of this page.</p>"));
			}
			
			exit(page_renderer::render_main("Set Password - $settings->sitename", "<p>" . htmlentities($_POST["user"]) . "'s password has been set successfully. <a href='?action=user-table'>Go back</a> to the user table.</p>"));
		});
		
		
		/**
		 * @api {post} ?action=user-delete	Delete a user account
		 * @apiName UserDelete
		 * @apiGroup Settings
		 * @apiPermission Moderator
		 *
		 * @apiParam	{string}	user		The username of the account to delete. username.
		 */
		
		/*
 		 * ██    ██ ███████ ███████ ██████
 		 * ██    ██ ██      ██      ██   ██
 		 * ██    ██ ███████ █████   ██████  █████
 		 * ██    ██      ██ ██      ██   ██
 		 *  ██████  ███████ ███████ ██   ██
 		 * 
 		 * ██████  ███████ ██      ███████ ████████ ███████
 		 * ██   ██ ██      ██      ██         ██    ██
 		 * ██   ██ █████   ██      █████      ██    █████
 		 * ██   ██ ██      ██      ██         ██    ██
 		 * ██████  ███████ ███████ ███████    ██    ███████
		 */
		add_action("user-delete", function() {
			global $env, $settings;
			
			if(!$env->is_admin || !$env->is_logged_in) {
				http_response_code(403);
				exit(page_renderer::render_main("Error - Delete User - $settings->sitename", "<p>Error: You aren't logged in as a moderator, so you don't have permission to delete a user's account.</p>"));
			}
			if(empty($_GET["user"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Error - Delete User - $settings->sitename", "<p>Error: No username was provided in the <code>user</code> POST variable.</p>"));
			}
			if(empty($settings->users->{$_GET["user"]})) {
				http_response_code(404);
				exit(page_renderer::render_main("User not found - Delete User - $settings->sitename", "<p>Error: No user called ".htmlentities($_GET["user"])." was found, so their account can't be delete. Perhaps you spelt their account name incorrectly?</p>"));
			}
			
			email_user($_GET["user"], "Account Deletion", "Hello, {$_GET["user"]}!

This is a notification email from $settings->sitename to let you know that $env->user has deleted your user account, so you won't be able to log in to your account anymore.

If this was done in error, then please contact a moderator, or $settings->admindetails_name ($settings->sitename's Administrator) - whose email address can be found at the bottom of every page on $settings->sitename.

--$settings->sitename
Powered by Pepperminty Wiki

(Received this email in error? Please contact $settings->sitename's administrator as detailed above, as replying to this email may or may not reach a human at the other end)");
			
			// Actually delete the account
			unset($settings->users->{$_GET["user"]});
			
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Server Error - Delete User - $settings->sitename", "<p>Error: $settings->sitename couldn't save the settings back to disk! Nothing has been changed. Please context ".htmlentities($settings->admindetails_name).", whose email address can be found at the bottom of this page.</p>"));
			}
			
			exit(page_renderer::render_main("Delete User - $settings->sitename", "<p>" . htmlentities($_GET["user"]) . "'s account has been deleted successfully. <a href='?action=user-table'>Go back</a> to the user table.</p>"));
		});
		
		
		if($env->is_admin) add_help_section("949-user-table", "Managing User Accounts", "<p>As a moderator on $settings->sitename, you can use the <a href='?action=user-table'>User Table</a> to administrate the user accounts on $settings->sitename. It allows you to perform actions such as adding and removing accounts, and resetting passwords.</p>");
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
