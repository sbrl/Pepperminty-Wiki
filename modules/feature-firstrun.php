<?php

register_module([
	"name" => "First run wizard",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Displays a special page to aid in setting up a new wiki for the first time.",
	"id" => "feature-firstrun",
	"code" => function() {
		
		// TODO: Remove this line once it's ready
		return true; // Stop this module from actually being executed - it's not ready yet!
		
		// TODO: Figure out how to detect pre-existing wikis here
		
		if(!$firstrun_complete && count(glob("._peppermint_secret_*")) == 0) {
			
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
			global $settings;
			
			if($settings->firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
			}
			
			if(!module_exists("page-login")) {
				http_response_code(503);
				exit(page_renderer::render_main("Build error - Pepperminty Wiki", "<p>The <code>page-login</code> wasn't included in this build of Pepperminty Wiki, so the first-run installation wizard will not work correctly.</p>
				<p>You can still complete the setup manually, however! Once done, set <code>firstrun_complete</code> in peppermint.json to <code>true</code>.</p>"));
			}
			
			// TODO: Check the environment here first
			//  - Make sure peppermint.json isn't accessible
			//  - Check for required modules?
			
			// TODO: Add a button to skip the firstrun wizard & do your own manual setup
			
			$result = "<h1>Welcome!</h1>
<p>Welcome to Pepperminty Wiki.</p>
<p>Fill out the below form to get your wiki up and running!</p>
<form method='post' action='?action=firstrun-complete'>
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
		<input type='text' id='password' name='password' required />
		<br />
		<label for='username'>Repeat Password:</label>
		<input type='text' id='password-again' name='password-again' required />
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
		
		
		add_action("firstrun-complete", function() {
			global $version, $commit, $settings;
			
			if($settings->firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
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
			if(!filter_var($_POST["email-address"], FILTER_VALIDATE_EMAIL)) {
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
