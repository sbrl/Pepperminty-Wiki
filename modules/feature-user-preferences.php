<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "User Preferences",
	"version" => "0.4.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a user preferences page, letting people do things like change their email address and password.",
	"id" => "feature-user-preferences",
	"code" => function() {
		global $env, $settings;
		/**
		 * @api {get} ?action=user-preferences Get a user preferences configuration page
		 * @apiName UserPreferences
		 * @apiGroup Settings
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
			global $env, $settings;
			
			if(!$env->is_logged_in)
			{
				exit(page_renderer::render_main("Error  - $settings->sitename", "<p>Since you aren't logged in, you can't change your preferences. This is because stored preferences are tied to each registered user account. You can login <a href='?action=login&returnto=" . rawurlencode("?action=user-preferences") . "'>here</a>.</p>"));
			}
			
			$statusMessages = [
				"change-password" => "Password changed successfully!"
			];
			
			if(!isset($env->user_data->emailAddress)) {
				$env->user_data->emailAddress = "";
				save_userdata();
			}
			
			$content = "<h2>User Preferences</h2>\n";
			if(isset($_GET["success"]) && $_GET["success"] === "yes") {
				$content .= "<p class='user-prefs-status-message'><em>" . $statusMessages[$_GET["operation"]] . "</em></p>\n";
			}
			
			if(has_action("watchlist") && module_exists("feature-watchlist")) {
				$content .= "<p><em>Looking for your watchlist? Find it <a href='?action=watchlist'>here</a>!</em></p>";
			}
			
			// If avatar support is present, allow the user to upload a new avatar
			if(has_action("avatar") && module_exists("feature-upload")) {
				$content .= "<a href='?action=upload&avatar=yes' class='preview'><figure>\n";
				$content .= "\t<img class='avatar' src='?action=avatar&user=" . urlencode($env->user) . "&size=256' title='Your current avatar - click to upload a new one' />\n";
				$content .= "<figcaption>Upload a new avatar</figcaption>\n";
				$content .= "</figure></a><br />\n";
			}
			$content .= "<label for='username'>Username:</label>\n";
			$content .= "<input type='text' name='username' value='".htmlentities($env->user)."' readonly />\n";
			$content .= "<form method='post' action='?action=save-preferences'>\n";
			$content .= "	<label for='email-address'>Email Address:</label>\n";
			$content .= "	<input type='email' id='email-address' name='email-address' placeholder='e.g. bob@bobsrockets.com' value='".htmlentities($env->user_data->emailAddress)."}' />\n";
			$content .= "	<p><small>Used to send you notifications etc. Never shared with anyone except ".htmlentities($settings->admindetails_name).", $settings->sitename's administrator.</small></p>\n";
			if($settings->email_verify_addresses) {
				$content .= "	<p>Email verification status: <strong>".(empty($env->user_data->emailAddressVerified) ? "not " : "")."verified</strong> <small><em>(Email address verification is required in order to receive emails (other than the verification email itself, of course). Click the link in the verification email sent to you to verify your address, or change it here to get another verification email - changing it to the same email address is ok)</em></small></p>";
			}
			$content .= "	<input type='submit' value='Save Preferences' />\n";
			$content .= "</form>\n";
			$content .= "<h3>Change Password</h3\n>";
			$content .= "<form method='post' action='?action=change-password'>\n";
			$content .= "	<label for='old-pass'>Current Password:</label>\n";
			$content .= "	<input type='password' name='current-pass'  />\n";
			$content .= "	<br />\n";
			$content .= "	<label for='new-pass'>New Password:</label>\n";
			$content .= "	<input type='password' name='new-pass' />\n";
			$content .= "	<br />\n";
			$content .= "	<label for='new-pass-confirm'>Confirm New Password:</label>\n";
			$content .= "	<input type='password' name='new-pass-confirm' />\n";
			$content .= "	<br />\n";
			$content .= "	<input type='submit' value='Change Password' />\n";
			$content .= "</form>\n";
			
			if($env->is_admin)
				$content .= "<p>As an admin, you can also <a href='?action=configure'>edit $settings->sitename's master settings</a>.</p>\n";
			
			exit(page_renderer::render_main("User Preferences - $settings->sitename", $content));
		});
		
		/**
		 * @api {post} ?action=save-preferences Save your user preferences
		 * @apiName UserPreferencesSave
		 * @apiGroup Settings
		 * @apiPermission User
		 */
		add_action("save-preferences", function() {
			global $env, $settings;
			
			if(!$env->is_logged_in)
			{
				http_response_code(400);
				exit(page_renderer::render_main("Error Saving Preferences - $settings->sitename", "<p>You aren't logged in, so you can't save your preferences. Try <a href='?action=login&returnto=" . rawurlencode("?action=user-preferences") . "'>logging in</a> first.</p>"));
			}
			
			if(isset($_POST["email-address"])) {
				if(mb_strlen($_POST["email-address"]) > 320) {
					http_response_code(413);
					exit(page_renderer::render_main("Error Saving Email Address - $settings->sitename", "<p>The email address you supplied (<code>".htmlentities($_POST['email-address'])."</code>) is too long. Email addresses can only be 320 characters long. <a href='javascript:window.history.back();'>Go back</a>."));
				}
				
				if(mb_strpos($_POST["email-address"], "@") === false) {
					http_response_code(422);
					exit(page_renderer::render_main("Error Saving Email Address - $settings->sitename", "<p>The email address you supplied (<code>".htmlentities($_POST['email-address'])."</code>) doesn't appear to be valid. <a href='javascript:window.history.back();'>Go back</a>."));
				}
				$old_address = $env->user_data->emailAddress ?? null;
				$env->user_data->emailAddress = $_POST["email-address"];
				
				// If email address verification is required and the email 
				// address has changed, send a verification email now
				if($settings->email_verify_addresses) {
					if(empty($env->user_data->emailAddressVerified) && $old_address !== $_POST["email-address"])
						$env->user_data->emailAddressVerified = false;
					
					if(empty($env->user_data->emailAddressVerified) && !email_verify_addresses($env->user)) {
						http_response_code(503);
						exit(page_renderer::render_main("Server error sending verification code - $settings->sitename", "<p>$settings->sitename tried to send you an email to verify your email address, but was unable to do so. The changes to your settings have not been saved. Please contact ".htmlentities($settings->admindetails_name).", whose email address can be found at the bottom of this page.</p>"));
					}
				}
			}
			
			// Save the user's preferences
			if(!save_userdata()) {
				http_response_code(503);
				exit(page_renderer::render_main("Error Saving Preferences - $settings->sitename", "<p>$settings->sitename had some trouble saving your preferences! Please contact ".htmlentities($settings->admindetails_name).", $settings->sitename's administrator and tell them about this error if it still occurs in 5 minutes. They can be contacted by email at this address: ".hide_email($settings->admindetails_email, $settings->admindetails_name).".</p>"));
			}
			
			exit(page_renderer::render_main("Preferences Saved Successfully - $settings->sitename", "<p>Your preferences have been saved successfully! You could go back your <a href='?action=user-preferences'>preferences page</a>, or on to the <a href='?page=" . rawurlencode($settings->defaultpage) . "'>".htmlentities($settings->defaultpage)."</a>.</p>
<p>If you changed your email address, a verification code will have been sent to the email address you specified. Click on the link provided to verify your new email address.</p>"));
		});
		
		/**
		 * @api {get}	?action=email-address-verify&code={code}	Verify the current user's email address
		 * @apiName			EmailAddressVerify
		 * @apiGroup		Settings
		 * @apiPermission	User
		 *
		 * @apiParam	{string}	code	The verfication code.
		 *
		 * @apiError	VerificationCodeIncorrect	The supplied verification code is not correct.
		 */
		add_action("email-address-verify", function() {
			global $env, $settings;
			
			if(!$env->is_logged_in) {
				http_response_code(307);
				header("x-status: failed");
				header("x-problem: not-logged-in");
				exit(page_renderer::render_main("Not logged in - $settings->sitename", "<p>You aren't logged in, so you can't verify your email address. Try <a href='?action=login&amp;returnto=".rawurlencode("?action=email-address-verify&code=".rawurlencode($_GET["code"]??""))."'>logging in</a>.</p>"));
			}
			
			if($env->user_data->emailAddressVerified) {
				header("x-status: success");
				exit(page_renderer::render_main("Already verified - $settings->sitename", "<p>Your email address is already verified, so you don't need to verify it again.</p>\n<p> <a href='index.php'>Go to the main page</a>.</p>"));
			}
			
			if(empty($_GET["code"])) {
				http_response_code(400);
				header("x-status: failed");
				header("x-problem: no-code-specified");
				exit(page_renderer::render_main("No verification code specified  - $settings->sitename", "<p>No verification code specified. Do so with the <code>code</code> GET parameter, or try making sure you copied the email address from the email you were sent correctly.</p>"));
			}
			
			if($env->user_data->emailAddressVerificationCode !== $_GET["code"]) {
				http_resonse_code(400);
				header("x-status: failed");
				header("x-problem: code-incorrect");
				exit(page_renderer::render_main("Verification code incorrect", "<p>That  verification code was incorrect. Try specifying another one, or going to your <a href='?action=user-preferences'>user preferences</a> and changing your email address to re-send another code (changing it to the same email address is ok).</p>"));
			}
			
			// The code supplied must be valid
			unset($env->user_data->emailAddressVerificationCode);
			$env->user_data->emailAddressVerified = true;
			
			if(!save_settings()) {
				http_response_code(503);
				header("x-status: failed");
				header("x-problem: server-error-disk-io");
				exit(page_renderer::render_main("Server error - $settings->sitename", "<p>Your verification code was correct, but $settings->sitename was unable to update your user details because it failed to write the changes to disk. Please contact ".htmlentities($settings->admindetails_name).", whose email address can be found at the bottom of the page.</p>"));
			}
			
			header("x-status: success");
			exit(page_renderer::render_main("Email Address Verified - $settings->sitename", "<p>Your email address was verified successfully. <a href='index.php'>Go to the main page</a>, or to your <a href='?action=user-preferences'>user preferences</a> to make further changes.</p>"));
		});
		
		/**
		 * @api	{post}	?action=change-password	Change your password
		 * @apiName			ChangePassword
		 * @apiGroup		Settings
		 * @apiPermission	User
		 *
		 * @apiParam	{string}	current-pass		Your current password.
		 * @apiParam	{string}	new-pass			Your new password.
		 * @apiParam	{string}	new-pass-confirm	Your new password again, to make sure you've typed it correctly.
		 *
		 * @apiError	PasswordMismatchError	The new password fields don't match.
		 */
		add_action("change-password", function() {
		    global $env, $settings;
			
			// Make sure the new password was typed correctly
			// This comes before the current password check since that's more intensive
			if($_POST["new-pass"] !== $_POST["new-pass-confirm"]) {
				exit(page_renderer::render_main("Password mismatch - $settings->sitename", "<p>The new password you typed twice didn't match! <a href='javascript:history.back();'>Go back</a>.</p>"));
			}
			// Check the current password
			if(!verify_password($_POST["current-pass"], $env->user_data->password)) {
				exit(page_renderer::render_main("Password mismatch - $settings->sitename", "<p>Error: You typed your current password incorrectly! <a href='javascript:history.back();'>Go back</a>.</p>"));
			}
			
			// All's good! Go ahead and change the password.
			$env->user_data->password = hash_password($_POST["new-pass"]);
			// Save the userdata back to disk
			if(!save_userdata()) {
				http_response_code(503);
				exit(page_renderer::render_main("Error Saving Password - $settings->sitename", "<p>While you entered your old password correctly, $settings->sitename encountered an error whilst saving your password to disk! Your password has not been changed. Please contact ".htmlentities($settings->admindetails_name)." for assistance (you can find their email address at the bottom of this page)."));
			}
			
			http_response_code(307);
			header("location: ?action=user-preferences&success=yes&operation=change-password");
			exit(page_renderer::render_main("Password Changed Successfully", "<p>You password was changed successfully. <a href='?action=user-preferences'>Go back to the user preferences page</a>.</p>"));
		});
		
		
		/*
		 *  █████  ██    ██  █████  ████████  █████  ██████
		 * ██   ██ ██    ██ ██   ██    ██    ██   ██ ██   ██
		 * ███████ ██    ██ ███████    ██    ███████ ██████
		 * ██   ██  ██  ██  ██   ██    ██    ██   ██ ██   ██
		 * ██   ██   ████   ██   ██    ██    ██   ██ ██   ██
		 */
		
 		/**
 		 * @api	{get}	?action=avatar&user={username}[&size={size}]	Get a user's avatar
 		 * @apiName			Avatar
 		 * @apiGroup		Upload
 		 * @apiPermission	Anonymous
 		 *
 		 * @apiParam	{string}	user			The username to fetch the avatar for
 		 * @apiParam	{string}	size			The preferred size of the avatar
 		 */
		add_action("avatar", function() {
			global $settings, $pageindex;
			
			$size = intval($_GET["size"] ?? 32);
			
			/// Use gravatar if there's some issue with the requested user
			
			// No user specified
			if(empty($_GET["user"])) {
				http_response_code(200);
				header("x-reason: no-user-specified");
				header("content-type: image/png");
				header("content-length: 101");
				exit(base64_decode("iVBORw0KGgoAAAANSUhEUgAAAFAAAABQAQMAAAC032DuAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAABBJREFUGBljGAWjYBTQDQAAA3AAATXTgHYAAAAASUVORK5CYII="));
			}
			
			$requested_username = $_GET["user"];
			$has_avatar = !empty($pageindex->{"Users/$requested_username/Avatar"}) && $pageindex->{"Users/$requested_username/Avatar"}->uploadedfile === true;
			
			if(!$settings->avatars_gravatar_enabled && !$has_avatar) {
				http_response_code(404);
				header("x-reason: no-avatar-found-gravatar-disabled");
				header("content-type: image/png");
				header("content-length: 101");
				exit(base64_decode("iVBORw0KGgoAAAANSUhEUgAAAFAAAABQAQMAAAC032DuAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAABBJREFUGBljGAWjYBTQDQAAA3AAATXTgHYAAAAASUVORK5CYII=")); // TODO: Refactor out into a separate function
			}
			
			// The user hasn't uploaded an avatar
			if(!$has_avatar) {
				$user_fragment = !empty($settings->users->$requested_username->emailAddress) ? $settings->users->$requested_username->emailAddress : $requested_username;
				
				http_response_code(307);
				header("x-reason: no-avatar-found");
				header("x-hash-method: " . ($user_fragment === $requested_username ? "username" : "email_address"));
				header("location: https://gravatar.com/avatar/" . md5($user_fragment) . "?default=identicon&rating=g&size=$size");
				exit();
			}
			
			// The user has uploaded an avatar, so we can redirec to the regular previewer :D
			
			http_response_code(307);
			header("x-reason: found-local-avatar");
			header("location: ?action=preview&size=$size&page=" . urlencode("Users/$requested_username/Avatar"));
			header("content-type: text/plain");
			exit("This user's avatar can be found at Files/$requested_username/Avatar");
		});
		
		// Display a help section on the user preferences, but only if the user
		// is logged in and so able to access them
		if($env->is_logged_in) {
			add_help_section("910-user-preferences", "User Preferences", "<p>As you are logged in, $settings->sitename lets you configure a selection of personal preferences. These can be viewed and tweaked to you liking over on the <a href='?action=user-preferences'>preferences page</a>, which can be accessed at any time by clicking the cog icon (it looks something like this: <a href='?action=user-preferences'>$settings->user_preferences_button_text</a>), though the administrator of $settings->sitename (".htmlentities($settings->admindetails_name).") may have changed its appearance.</p>");
		}
		
		if($settings->avatars_show) {
			add_help_section("915-avatars", "Avatars", "<p>$settings->sitename allows you to upload an avatar and have it displayed next to your name. If you don't have an avatar uploaded yet, then $settings->sitename will take a <a href='https://www.techopedia.com/definition/19744/hash-function'>hash</a> of your email address and ask <a href='https://gravatar.com'>Gravatar</a> for for your Gravatar instead. If you haven't told $settings->sitename what your email address is either, a hash of your username is used instead. If you don't have a gravatar, then $settings->sitename asks Gravatar for an identicon instead.</p>
			<p>Your avatar on $settings->sitename currently looks like this: <img class='avatar' src='?action=avatar&user=" . rawurlencode($env->user) . "' />" . ($settings->upload_enabled ? " - you can upload a new one by going to your <a href='?action=user-preferences'>preferences</a>, or <a href='?action=upload&avatar=yes' />clicking here</a>." : ", but $settings->sitename currently has uploads disabled, so you can't upload a new one directly to $settings->sitename. You can, however, set your email address in your <a href='?action=user-preferences'>preferences</a> and <a href='https://en.gravatar.com/'>create a Gravatar</a>, and then it should show up here on $settings->sitename shortly.") . "</p>");
		}
	}
]);

/**
 * Sends a verification email to the specified user, assuming they need to 
 * verify their email address.
 * If a user does not need to verify their email address, no verification email 
 * is sent and true is returned.
 * @param	string	$username	The name of the user to send the verification code to.
 * @return	bool	Whether the verification code was sent successfully. If a user does not need to verify their email address, this returns true.
 */
function email_user_verify(string $username) : bool {
	global $settings;
	
	$user_data = $settings->users->$username;
	
	if(!empty($user_data->emailAddressVerified) &&
		$user_data->emailAddressVerified === true) {
		return true;
	}
	
	// Generate a verification code
	$user_data->emailAddressVerificationCode = crypto_id(64);
	if(!save_settings())
		return false;
	
	return email_user(
		$username,
		"Verify your account - $settings->sitename",
		"Hey there! Click this link to verify your account on $settings->sitename:

".url_stem()."?action=email-address-verify&code=$user_data->emailAddressVerificationCode

$settings->sitename requires that you verify your email address in order to use it.

--$settings->sitename
Powered by Pepperminty Wiki",
		true // ignore that the user's email address isn't verified
	);
}

?>
