<?php
register_module([
	"name" => "First Run Interface",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Provides the first-run interface some thigns might be a be skew-whiff if you don't go through the first-run interface, but if you know what you're doing you shouldn't need this module. Currently in development.",
	"id" => "action-first-run",
	"optional" => true,
	"code" => function() {
		global $settings;
		
		// Force the user to the first-run interface
		if(!$settings->setup_complete)
			$env->action = "first-run";
		
		/**
		 * @api {get} ?action=first-run		Show the first-run interface
		 * @apiName Hash
		 * @apiGroup Utility
		 * @apiPermission Anonymous
		 */
		
		/*
		 * ███████ ██ ██████  ███████ ████████    ██████  ██    ██ ███    ██
		 * ██      ██ ██   ██ ██         ██       ██   ██ ██    ██ ████   ██
		 * █████   ██ ██████  ███████    ██ █████ ██████  ██    ██ ██ ██  ██
		 * ██      ██ ██   ██      ██    ██       ██   ██ ██    ██ ██  ██ ██
		 * ██      ██ ██   ██ ███████    ██       ██   ██  ██████  ██   ████
		 */
		add_action("first-run", function() {
			global $settings, $paths;
			
			$stage = intval($_GET["stage"] ?? 0);
			$stage_count = 4; // The number of setup stages
			
			switch($stage) {
				case 0:
					
					$peppermint_config_url = dirname(full_url()) . "/peppermint.json";
					$config_checker = curl_init($peppermint_config_url);
					curl_setopt($config_checker, CURL_HEADER, true);
					curl_setopt($config_checker, CURL_NOBODY, true);
					curl_setopt($config_checker, CURL_RETURNTRANSFER, 1);
					curl_setopt($config_checker, CURL_TIMEOUT, 5);
					curl_exec($config_checker);
					
					$peppermint_config_returnstatus = intval(curl_getinfo($config_checker, CURLINFO_HTTP_CODE));
					
					if($peppermint_config_returnstatus >= 200 &&
						$peppermint_config_returnstatus < 300) {
						http_response_code(500);
						
						if(!rename($paths->settings_file, "$paths->settings_file.compromised")) {
							exit(page_renderer::render_minimal("Configuration Error - Pepperminty Wiki", "<h1>0: Configuration Error</h1>
							<p>Welcome to Pepperminty Wiki! Unforutnately, your setup doesn't appear to be quite right, as not only is your new <code>peppermint.json</code> configuration file exposed to the internet (causing your site's secret to be divulged), but Pepperminty Wiki doesn't appear to have write access to rename it either.</p>
							<p>You might need to check the permissions on the directory you've copied Pepperminty Wiki to.</p>
							<p>Once you've fixed these issues, simply delete the created <code>peppermint.json</code> file and reload this page.</p>"));
						}
						
						exit(page_renderer::render_minimal("Security Error - Pepperminty Wiki", "<h1>0: Security Error</h1>
						<p>Welcome to Pepperminty Wiki! Unforutnately, your setup doesn't appear to be quite right, as your the new <code>peppermint.json</code> configuration file for your brand-new Pepperminty Wiki instance appears to be accessible from the internet. This means that anyone could get a hold of your site secret and password hashes! To protect your installation, it's been moved to <code>peppermint.json.compromised</code> - and you'll need to delete (or move it) out of the way to continue.</p>
						<p>Please block access from the internet to this file - Pepperminty Wiki reads it directly from disk.</p>"));
					}
					
					$content = "<h1>0: Begin!</h1>";
					$content .= "<p>Welcome to Pepperminty Wiki! This page is a first-run page that will be shown just this once (if you want to access it again, visit the <code>first-run</code> action), and will guide you through the setup of your new Pepperminty Wiki instance.</p>
					<p>To get started, enter the site secret into the box below to prove that you own the site. It was can found in the <code>peppermint.json</code> file that Pepperminty Wiki has just created, under the name <code>secret</code>.</p>
					<p>Security check: &#10004; - <code>peppermint.json</code> doesn't appear to be accessible form the internet (though it doesn't hurt to check yourself)</p>
					<form method='POST' action='?action=first-run&stage=1'>
						<label for='secret'>Site Secret:</label>
						<input type='text' id='secret' name='secret' placeholder='e.g. 170cc5fdef9075a0d9510e3' />
						<input type='submit' value='Continue &raquo;' />
					</form>";
					
					break;
				
				case 1:
					if(empty($_POST["secret"]) || $_POST["secret"] !== $settings->secret)
						exit(page_renderer::render_main("Error - Setup - Pepperminty Wiki", "<p>That site secret doesn't appear to match what's stored in <code>pepperminty.json</code>. <a href='?action=first-run&stage=0'>Go back</a>.</p>"));
					
					$content .= "<h1>1: Get Ready!</h1>
					<p>Cool! Now that we've got you verified, let's get started! Fill out the form below to start to customise your Pepperminty Wiki instance.</p>
					<form method='POST' action='?action=first-run&stage=2'>
						<label><h3>Wiki name:</h3></label>
						<p>The name of your wiki.</p>
						<input type='text' id='sitename' name='sitename' value='e.g. CrossCode Wiki' />
						
						<h3>Administrator Account</h3>
						<p>It's time to create your very first account! This account is special - it's your administrator account (you can promote more users to be administrators by editing <code>peppermint.json</code> after completing this setup). The username must not contain spaces - they'll be stripped out if you include them!</p>
						
						<label for='admin-username'>Username:</label>
						<input type='text' id='admin-username' name='admin-username' placeholder='e.g. jebediah' />
						
						<p>Your email address. Users will be invited to contact you with this address if they experience issues.</p>
						<label for='admin-email'>Email address:</label>
						<input type='email' id='admin-email' name='admin-email' />
						
						<p>The password for your new account. Make sure it's secure!</p>
						<label for='password'>Password:</label>
						<input type='password' id='password' name='password' />
						<br />
						<label for='password-repeat'>Repeat Password:</label>
						<input type='password' id='password-repeat' name='password-repeat' />
						
						<br />
						<input type='submit' value='Continue &raquo;' />
					</form>";
					
					break;
				
				case 2:
					if($_GET["password"] !== $_GET["password-repeat"])
						exit(page_renderer::render_minimal("Password mismatch - Pepperminty Wiki", "<p>Those passwords don't seem to match! <a href='?action=first-run&stage=0'>Go back</a>.</p>"));
					
					$admin_username = $_GET["admin-username"];
					$admin_email = $_GET["admin-email"];
					
					// Set the sitename
					$settings->sitename = $_GET["sitename"];
					// Set the admin details
					$settings->admindetails_name = $admin_username;
					$settings->admindetails_email = $admin_email;
					// Setup a new users table
					$settings->users = [
						$admin_username => [
							"email" => $admin_email,
							"password" => hash_password($_GET["password"])
						]
					];
					// Save the new settings
					file_put_contents($paths->settings_file, json_encode($settings, JSON_PRETTY_PRINT));
					
					$content .= "<p>Brilliant! You're practically all set. There's just some small step to complete though: Pepperminty Wiki needs to download a few resources from the internet. To do this, Pepperminty Wiki will need access to the following domains:</p>";
					
					$content .= "<p>If you're not sure what this means, then the server $settings->sitename is running on is probably already configured correctly.</p>";
					break;
					
			}
			
			exit(page_renderer::render_minimal("Setup [ $stage / $stage_count ] - $settings->sitename", $content));
		});
	}
]);

?>
