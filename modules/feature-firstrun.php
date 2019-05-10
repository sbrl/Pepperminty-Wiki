<?php

register_module([
	"name" => "First run wizard",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Displays a special page to aid in setting up a new wiki for the first time.",
	"id" => "feature-firstrun",
	"code" => function() {
		
		return true; // Stop this module from actually being executed - it's not ready yet!
		
		// TODO: Figure out how to detect pre-existing wikis here
		// Perhaps this could be a setting instead? We'd need to update the settings logic a bit
		$firstrun_complete = file_exists("._peppermint_installed");
		
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
		add_action("firstrun", function() use($firstrun_complete) {
			global $settings;
			
			if($firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
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
		});
		
		
		add_action("firstrun-complete", function() {
			global $version, $commit;
			
			if($firstrun_complete) {
				http_response_code(400);
				exit(page_renderer::render_main("Setup complete - Error - $settings->sitename", "<p>Oops! Looks like $settings->sitename is already setup and ready to go! Go to the <a href='?action=$settings->defaultaction&page=".rawurlencode($settings->defaultpage)."'>" . htmlentities($settings->defaultpage)."</a> to get started!</p>"));
			}
			
			// $_POST: username, password, password-again, wiki-name, data-dir
			
			if(empty($_POST["username"])) {
				http_response_code(422);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a username. Try going back in your browser and filling one in.</p>"));
			}
			if(empty($_POST["password"]) || empty($_POST["password-again"])) {
				http_response_code(422);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a password. Try going back in your browser and filling one in.</p>"));
			}
			if(empty($_POST["wiki-name"])) {
				http_response_code(422);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a name for your wiki. Try going back in your browser and filling one in.</p>"));
			}
			if(empty($_POST["data-dir"])) {
				http_response_code(422);
				exit(page_renderer::render_main("Missing information - Error - Pepperminty Wiki", "<p>Oops! Looks like you forgot to enter a directory on the server to store the wiki's data in. Try going back in your browser and filling one in. Relative paths are ok - the default is <code>.</code> (i.e. the current directory).</p>"));
			}
			
			
			
			// ----------------------------------------------------------------
			
			file_put_contents("._peppermint_installed", "Install complete at " . date("c") . "with Pepperminty Wiki v$version-$commit");
		});
	}
]);
