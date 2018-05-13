<?php
register_module([
	"name" => "Login",
	"version" => "0.9.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a pair of actions (login and checklogin) that allow users to login. You need this one if you want your users to be able to login.",
	"id" => "page-login",
	"code" => function() {
		global $settings;
		
		/**
		 * @api		{get}	?action=login[&failed=yes][&returnto={someUrl}]	Get the login page
		 * @apiName		Login
		 * @apiGroup	Authorisation
		 * @apiPermission	Anonymous
		 * 
		 * @apiParam	{string}	failed		Setting to yes causes a login failure message to be displayed above the login form.
		 * @apiParam	{string}	returnto	Set to the url to redirect to upon a successful login.
		 */
		
		/*
		 * ██       ██████   ██████  ██ ███    ██
		 * ██      ██    ██ ██       ██ ████   ██
		 * ██      ██    ██ ██   ███ ██ ██ ██  ██
		 * ██      ██    ██ ██    ██ ██ ██  ██ ██
		 * ███████  ██████   ██████  ██ ██   ████
		 */
		add_action("login", function() {
			global $settings, $env;
			
			// Build the action url that will actually perform the login
			$login_form_action_url = "index.php?action=checklogin";
			if(isset($_GET["returnto"]))
				$login_form_action_url .= "&returnto=" . rawurlencode($_GET["returnto"]);
			
			if($env->is_logged_in && !empty($_GET["returnto"]))
			{
				http_response_code(307);
				header("location: " . $_GET["returnto"]);
			}
			
			$title = "Login to $settings->sitename";
			$content = "<h1>Login to $settings->sitename</h1>\n";
			if(isset($_GET["failed"]))
				$content .= "\t\t<p><em>Login failed.</em></p>\n";
			if(isset($_GET["required"]))
				$content .= "\t\t<p><em>$settings->sitename requires that you login before continuing.</em></p>\n";
			$content .= "\t\t<form method='post' action='$login_form_action_url'>
				<label for='user'>Username:</label>
				<input type='text' name='user' id='user' autofocus />
				<br />
				<label for='pass'>Password:</label>
				<input type='password' name='pass' id='pass' />
				<br />
				<input type='submit' value='Login' />
			</form>\n";
			exit(page_renderer::render_main($title, $content));
		});
		
		/**
		 * @api		{post}	?action=checklogin	Perform a login
		 * @apiName		CheckLogin
		 * @apiGroup	Authorisation
		 * @apiPermission	Anonymous
		 * 
		 * @apiParam	{string}	user		The user name to login with.
		 * @apiParam	{string}	pass		The password to login with.
		 * @apiParam	{string}	returnto	The URL to redirect to upon a successful login.
		 *
		 * @apiError	InvalidCredentialsError	The supplied credentials were invalid. Note that this error is actually a redirect to ?action=login&failed=yes (with the returnto parameter appended if you supplied one)
		 */
		
		/*
 		 * ██████ ██   ██ ███████  ██████ ██   ██
		 * ██     ██   ██ ██      ██      ██  ██
		 * ██     ███████ █████   ██      █████
		 * ██     ██   ██ ██      ██      ██  ██
 		 * ██████ ██   ██ ███████  ██████ ██   ██
 		 * 
		 * ██       ██████   ██████  ██ ███    ██
		 * ██      ██    ██ ██       ██ ████   ██
		 * ██      ██    ██ ██   ███ ██ ██ ██  ██
		 * ██      ██    ██ ██    ██ ██ ██  ██ ██
		 * ███████  ██████   ██████  ██ ██   ████
		 */
		add_action("checklogin", function() {
			global $settings, $env;
			
			// Actually do the login
			if(isset($_POST["user"]) and isset($_POST["pass"]))
			{
				// The user wants to log in
				$user = $_POST["user"];
				$pass = $_POST["pass"];
				if(!empty($settings->users->$user) && verify_password($pass, $settings->users->$user->password))
				{
					// Success! :D
					
					// Update the environment
					$env->is_logged_in = true;
					$env->user = $user;
					$env->user_data = $settings->users->{$env->user};
					
					$new_password_hash = hash_password_update($pass, $settings->users->$user->password);
					
					// Update the password hash
					if($new_password_hash !== null) {
						$env->user_data->password = $new_password_hash;
						if(!save_userdata()) {
							http_response_code(503);
							exit(page_renderer::render_main("Login Error - $settings->sitename", "<p>Your credentials were correct, but $settings->sitename was unable to log you in as an updated hash of your password couldn't be saved. Updating your password hash to the latest and strongest hashing algorithm is an important part of keeping your account secure.</p>
							<p>Please contact $settings->admindetails_name, $settings->sitename's adminstrator, for assistance (their email address can be found at the bottom of every page, including this one).</p>"));
						}
						error_log("[Pepperminty Wiki] Updated password hash for $user.");
					}
					
					$_SESSION["$settings->sessionprefix-user"] = $user;
					$_SESSION["$settings->sessionprefix-pass"] = $new_password_hash ?? hash_password($pass);
					$_SESSION["$settings->sessionprefix-expiretime"] = time() + 60*60*24*30; // 30 days from now
					
					// Redirect to wherever the user was going
					http_response_code(302);
					header("x-login-success: yes");
					if(isset($_GET["returnto"]))
						header("location: " . $_GET["returnto"]);
					else
						header("location: index.php");
					exit();
				}
				else
				{
					// Login failed :-(
					http_response_code(302);
					header("x-login-success: no");
					$nextUrl = "index.php?action=login&failed=yes";
					if(!empty($_GET["returnto"]))
						$nextUrl .= "&returnto=" . rawurlencode($_GET["returnto"]);
					header("location: $nextUrl");
					exit();
				}
			}
			else
			{
				http_response_code(302);
				$nextUrl = "index.php?action=login&failed=yes&badrequest=yes";
				if(!empty($_GET["returnto"]))
					$nextUrl .= "&returnto=" . rawurlencode($_GET["returnto"]);
				header("location: $nextUrl");
				exit();
			}
		});
		
		// Register a section on logging in on the help page.
		add_help_section("30-login", "Logging in", "<p>In order to edit $settings->sitename and have your edit attributed to you, you need to be logged in. Depending on the settings, logging in may be a required step if you want to edit at all. Thankfully, loggging in is not hard. Simply click the &quot;Login&quot; link in the top left, type your username and password, and then click login.</p>
		<p>If you do not have an account yet and would like one, try contacting <a href='mailto:" . hide_email($settings->admindetails_email) . "'>$settings->admindetails_name</a>, $settings->sitename's administrator and ask them nicely to see if they can create you an account.</p>");
		
		// Re-check the password hashing cost, if necessary
		do_password_hash_code_update();
	}
]);

function do_password_hash_code_update() {
	global $settings, $paths;
	
	// There's no point if we're using Argon2i, as it doesn't take a cost
	if(hash_password_properties()["algorithm"] == PASSWORD_ARGON2I)
		return;
		
	// Skip rechecking if the automatic check has been disabled
	if($settings->password_cost_time_interval == -1)
		return;
	// Skip the recheck if we've done one recently
	if(isset($settings->password_cost_time_lastcheck) &&
		time() - $settings->password_cost_time_lastcheck < $settings->password_cost_time_interval)
		return;
	
	$new_cost = hash_password_compute_cost();
	
	// Save the new cost, but only if it's higher than the old one
	if($new_cost > $settings->password_cost)
		$settings->password_cost = $new_cost;
	// Save the current time in the settings
	$settings->password_cost_time_lastcheck = time();
	file_put_contents($paths->settings_file, json_encode($settings, JSON_PRETTY_PRINT));
}

/**
 * Figures out the appropriate algorithm & options for hashing passwords based 
 * on the current settings.
 * @return array The appropriate password hashing algorithm and options.
 */
function hash_password_properties() {
	global $settings;
	
	$result = [
		"algorithm" => constant($settings->password_algorithm),
		"options" => [ "cost" => $settings->password_cost ]
	];
	if(defined("PASSWORD_ARGON2I") && $result["algorithm"] == PASSWORD_ARGON2I)
		$result["options"] = [];
	return $result;
}
/**
 * Hashes the given password according to the current settings defined
 * in $settings.
 * @package	page-login
 * @param	string	$pass	The password to hash.
 * 
 * @return	string	The hashed password. Uses sha3 if $settings->use_sha3 is
 * 					enabled, or sha256 otherwise.
 */
function hash_password($pass) {
	$props = hash_password_properties();
	return password_hash(
		base64_encode(hash("sha384", $pass)),
		$props["algorithm"],
		$props["options"]
	);
}
/**
 * Verifies a user's password against a pre-generated hash.
 * @param	string	$pass	The user's password.
 * @param	string	$hash	The hash to compare against.
 * @return	bool	Whether the password matches the has or not.
 */
function verify_password($pass, $hash) {
	$pass_transformed = base64_encode(hash("sha384", $pass));
	return password_verify($pass_transformed, $hash);
}
/**
 * Determines if the provided password needs re-hashing or not.
 * @param  string $pass The password to check.
 * @param  string $hash The hash of the provided password to check.
 * @return string|null  Returns null if an updaste is not required - otherwise returns the new updated hash.
 */
function hash_password_update($pass, $hash) {
	$props = hash_password_properties();
	if(password_needs_rehash($hash, $props["algorithm"], $props["options"])) {
		return hash_password($pass);
	}
	return null;
}
/**
 * Computes the appropriate cost value for password_hash based on the settings
 * automatically.
 * Starts at 10 and works upwards in increments of 1. Goes on until a value is 
 * found that's greater than the target - or 10x the target time elapses.
 * @return integer The automatically calculated password hashing cost.
 */
function hash_password_compute_cost() {
	global $settings;
	$props = hash_password_properties();
	if($props["algorithm"] == PASSWORD_ARGON2I)
		return null;
	if(!is_int($props["options"]["cost"]))
		$props["options"]["cost"] = 10;
	
	$target_cost_time = $settings->password_cost_time / 1000; // The setting is in ms
	
	$start = microtime(true);
	do {
		$props["options"]["cost"]++;
		$start_i = microtime(true);
		password_hash("testing", $props["algorithm"], $props["options"]);
		$end_i =  microtime(true);
		// Iterate until we find a cost high enough
		// ....but don't keep going forever - try for at most 10x the target
		// time in total (in case the specified algorithm doesn't take a
		// cost parameter)
	} while($end_i - $start_i < $target_cost_time &&
		$end_i - $start < $target_cost_time * 10);
	
	return $props["options"]["cost"];
}
