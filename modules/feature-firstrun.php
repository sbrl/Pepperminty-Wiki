<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


register_module([
	"name" => "First run wizard",
	"version" => "0.1.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Displays a special page to aid in setting up a new wiki for the first time.",
	"id" => "feature-firstrun",
	"code" => function() {
		global $settings, $env;
		
		
		// NOTE: We auto-detect pre-existing wikis in 01-settings.fragment.php
		if(!$settings->firstrun_complete && preg_match("/^firstrun/", $env->action) !== 1) {
			http_response_code(307);
			header("location: ?action=firstrun");
			exit("Redirecting you to the first-run wizard....");
		}
		
		/**
		 * @api {get} ?action=firstrun	Display the firstrun page
		 * @apiName FirstRun
		 * @apiGroup Settings
		 * @apiPermission Anonymous
		 * 
		 */
		
		/*
 	 	 * ███████ ██ ██████  ███████ ████████ ██████  ██    ██ ███    ██
 		 * ██      ██ ██   ██ ██         ██    ██   ██ ██    ██ ████   ██
 		 * █████   ██ ██████  ███████    ██    ██████  ██    ██ ██ ██  ██
 		 * ██      ██ ██   ██      ██    ██    ██   ██ ██    ██ ██  ██ ██
 		 * ██      ██ ██   ██ ███████    ██    ██   ██  ██████  ██   ████
		 */
		add_action("firstrun", function() {
			global $settings, $settingsFilename, $version;
			
			if($settings->firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
			}
			
			if(!module_exists("page-login")) {
				http_response_code(503);
				exit(page_renderer::render_main("Build error - Pepperminty Wiki", "<p>The <code>page-login</code> wasn't included in this build of Pepperminty Wiki, so the first-run installation wizard will not work correctly.</p>
				<p>You can still complete the setup manually, however! Once done, set <code>firstrun_complete</code> in peppermint.json to <code>true</code>.</p>"));
			}
			
			if(!$settings->disable_peppermint_access_check &&
				php_sapi_name() !== "cli-server") { // The CLI server is single threaded, so it can't support loopback requests
				$request_url = full_url();
				$request_url = preg_replace("/\/(index.php)?\?.*$/", "/$settingsFilename", $request_url);
				@file_get_contents($request_url);
				// $http_response_header is a global reserved variable. More information: https://devdocs.io/php/reserved.variables.httpresponseheader
				$response_code = intval(explode(" ", $http_response_header[0])[1]);
				if($response_code >= 200 && $response_code < 300) {
					file_put_contents("$settingsFilename.compromised", "compromised");
					http_response_code(307);
					header("location: index.php");
					exit();
				}
			}
			else {
				error_log("[PeppermintyWiki/firstrun] Warning: The public peppermint.json access check has been disabled (either manually or because you're using a local PHP development server with php -S ....). It's strongly recommended you ensure that access from outside is blocked to peppermint.json to avoid (many) security issues and other nastiness such as stealing of site secrets and password hashes.");
			}
			
			// TODO: Check the environment here first
			//  - Check for required modules?
			
			// TODO: Add a button to skip the firstrun wizard & do your own manual setup
			
			// TODO: Add option to configure theme auto-update here - make sure it doesn't do anything until configuration it complete!
			
			$result = "<h1>Welcome!</h1>
<p>Welcome to Pepperminty Wiki.</p>
<p>Fill out the below form to get your wiki up and running!</p>
<p>Optionally, <a target='_blank' href='https://starbeamrainbowlabs.com/blog/viewtracker.php?action=record&post-id=pepperminty-wiki/$version&format=text'>click this link</a> to say hi and let Starbeamrainbowlabs know that you're setting up a new Pepperminty Wiki $version instance.</p>
<form method='post' action='?action=firstrun-complete'>
	<fieldset>
		<legend>Authorisation</legend>
		
		<p><em>Find your wiki secret in the <code>secret</code> property inside <code>peppermint.json</code>. Don't forget to avoid copying the quotes surrounding the value!</em></p>
		<label for='secret'>Wiki Secret:</label>
		<input type='password' id='secret' name='secret' />
	</fieldset>
	<fieldset>
		<legend>Admin account details</legend>
		
		<label for='username'>Username:</label>
		<input type='text' id='username' name='username' placeholder='e.g. bob, admin' required />
		<br />
		<label for='username'>Email address:</label>
		<input type='text' id='email-address' name='email-address' required />
		<br />
		<p><em>Longer is better! Aim for at least 14 characters.</em></p>
		<label for='username'>Password:</label>
		<input type='password' id='password' name='password' required />
		<br />
		<label for='username'>Repeat Password:</label>
		<input type='password' id='password-again' name='password-again' required />
	</fieldset>
	<fieldset>
		<legend>Wiki Details</legend>
		
		<label for='wiki-name'>Wiki Name:</label>
		<input type='text' id='wiki-name' name='wiki-name' placeholder=\"e.g. Bob's Rockets Compendium\" required />
		<!-- FUTURE: Have a logo url box here? -->
		<p><em>The location on the server's disk to store the wiki data. Relative paths are ok - the default is <code>.</code> (i.e. the current directory).</em></p>
		<label for='data-dir'>Data Storage Directory:</label>
		<input type='text' id='data-dir' name='data-dir' value='.' required />
	</fieldset>
	
	<input type='submit' value='Create Wiki!' />
</form>";
			
			exit(page_renderer::render_main("Welcome! - Pepperminty Wiki", $result));
		});
		
		
		/**
		 * @api {post} ?action=firstrun-complete	Complete the first-run wizard.
		 * @apiName FirstRunComplete
		 * @apiGroup Settings
		 * @apiPermission Anonymous
		 *
		 * @apiParam	{string}	username		The username for the first admin account
		 * @apiParam	{string}	password		The password for the first admin account
		 * @apiParam	{string}	password-again	The password repeated for the first admin account
		 * @apiParam	{string}	email-address	The email address for the first admin account
		 * @apiParam	{string}	wiki-name		The name of the wiki. Saved to $settings->sitename
		 * @apiParam	{string}	data-dir		The directory on the server to save the wiki data to. Saved to $settings->data_storage_dir.
		 */
		add_action("firstrun-complete", function() {
			global $version, $commit, $settings;
			
			if($settings->firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
			}
			
			if($_POST["secret"] !== $settings->secret) {
				http_response_code(401);
				exit(page_renderer::render_main("Incorrect secret - Pepperminty Wiki", "<p>Oops! That secret was incorrect. Open <code>peppermint.json</code> that is automatically written to the directory alongside the <code>index.php</code> that you uploaded to your web server and copy the value of the <code>secret</code> property into the wiki secret box on the previous page, taking care to avoid copying the quotation marks.</p>"));
			}
			
			// $_POST: username, email-address, password, password-again, wiki-name, data-dir
			
			if(empty($_POST["username"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a username. Try going back in your browser and filling one in.</p>"));
			}
			if(empty($_POST["email-address"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter an email address. Try going back in your browser and filling one in.</p>"));
			}
			if(filter_var($_POST["email-address"], FILTER_VALIDATE_EMAIL) === false) {
				http_response_code(400);
				exit(page_renderer::render_main("Invalid email address - Error - Pepperminty Wiki", "<p>Oops! Looks like that email address isn't valid. Try going back in your browser and correcting it.</p>"));
			}
			if(empty($_POST["password"]) || empty($_POST["password-again"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a password. Try going back in your browser and filling one in.</p>"));
			}
			if($_POST["password"] !== $_POST["password-again"]) {
				http_response_code(422);
				exit(page_renderer::render_main("Password mismatch - Error - Pepperminty Wiki", "<p>Oops! Looks like the passwords you entered aren't the same. Try going back in your browser and entering it again.</p>"));
			}
			if(empty($_POST["wiki-name"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a name for your wiki. Try going back in your browser and filling one in.</p>"));
			}
			if(empty($_POST["data-dir"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a directory on the server to store the wiki's data in. Try going back in your browser and filling one in. Relative paths are ok - the default is <code>.</code> (i.e. the current directory).</p>"));
			}
			
			// Generate the user data object & replace the pre-generated users
			$user_data = new stdClass();
			$user_data->password = hash_password($_POST["password"]);
			$user_data->emailAddress = $_POST["email-address"];
			$settings->users = new stdClass();
			$settings->users->{$_POST["username"]} = $user_data;
			$settings->admins = [ $_POST["username"] ]; // Don't forget to mark them as a mod
			
			// Apply the settings
			$settings->firstrun_complete = true;
			$settings->sitename = $_POST["wiki-name"];
			$settings->data_storage_dir = $_POST["data-dir"];
			
			if(!save_settings()) {
				http_response_code(500);
				exit(page_renderer::render_main("Server Error - Pepperminty Wiki", "<p>Oops! Pepperminty Wiki was unable to save your settings back to disk. This can happen if Pepperminty Wiki does not have write permissions on it's own directory and the files contained within (except <code>index.php</code> of course).</p>
				<p>Try contacting your server owner and ask them to correct it. If you are the server owner, you may need to run <code>sudo chown -R WEBSERVER_USERNAME:WEBSERVER_USERNAME PATH/TO/WIKI/DIRECTORY</code>, replacing the bits in UPPERCASE.</p>"));
			}
			
			http_response_code(201);
			exit(page_renderer::render_main("Setup complete! - Pepperminty Wiki", "<p>Congratulations! You've completed the Pepperminty Wiki setup.</p>
			<p><a href='?action=$settings->defaultaction'>Click here</a> to start using $settings->sitename, your new wiki!</p>"));
		});
	}
]);
