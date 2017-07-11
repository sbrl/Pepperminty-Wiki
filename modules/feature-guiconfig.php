<?php
register_module([
	"name" => "Settings GUI",
	"version" => "0.1.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "The module everyone has been waiting for! Adds a web based gui that lets mods change the wiki settings.",
	"id" => "feature-guiconfig",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=configure Get a page to change the global wiki settings
		 * @apiName ConfigureSettings
		 * @apiGroup Utility
		 * @apiPermission Moderator
		 */
		
		/*
		 *  ██████  ██████  ███    ██ ███████ ██  ██████  ██    ██ ██████  ███████
		 * ██      ██    ██ ████   ██ ██      ██ ██       ██    ██ ██   ██ ██
		 * ██      ██    ██ ██ ██  ██ █████   ██ ██   ███ ██    ██ ██████  █████
		 * ██      ██    ██ ██  ██ ██ ██      ██ ██    ██ ██    ██ ██   ██ ██
		 *  ██████  ██████  ██   ████ ██      ██  ██████   ██████  ██   ██ ███████
 	 	 */
		add_action("configure", function() {
			global $settings, $env, $guiConfig;
			
			if(!$env->is_admin)
			{
				$errorMessage = "<p>You don't have permission to change $settings->sitename's master settings.</p>\n";
				if(!$env->is_logged_in)
					$errorMessage .= "<p>You could try <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a>.</p>";
				else
					$errorMessage .= "<p>You could try <a href='?action=logout&returnto=%3Faction%3Dconfigure'>logging out</a> and then <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a> again with a different account that has the appropriate privileges.</a>.</p>";
				exit(page_renderer::render_main("Error - $settings->sitename", $errorMessage));
			}
			
			$content = "<h1>Master Control Panel</h1>\n";
			$content .= "<p>This page lets you configure $settings->sitename's master settings. Please be careful - you can break things easily on this page if you're not careful!</p>\n";
			$content .= "<h2>Actions</h2>";
			
			$content .= "<button class='action-invindex-rebuild'>Rebuild Search Index</button><br />\n";
			$content .= "<output class='action-invindex-rebuild-latestmessage'></output><br />\n";
			$content .= "<progress class='action-invindex-rebuild-progress' min='0' max='100' value='0' style='display: none;'></progress><br />\n";
			
			$invindex_rebuild_script = <<<SCRIPT
window.addEventListener("load", function(event) {
	document.querySelector(".action-invindex-rebuild").addEventListener("click", function(event) {
		var rebuildActionEvents = new EventSource("?action=invindex-rebuild");
		var latestMessageElement = document.querySelector(".action-invindex-rebuild-latestmessage");
		var progressElement = document.querySelector(".action-invindex-rebuild-progress");
		rebuildActionEvents.addEventListener("message", function(event) {
			console.log(event);
			let message = event.data; 
			latestMessageElement.value = event.data;
			let parts = message.match(/^\[\s*(\d+)\s+\/\s+(\d+)\s*\]/);
			if(parts != null) {
				progressElement.style.display = "";
				progressElement.min = 0;
				progressElement.max = parseInt(parts[2]);
				progressElement.value = parseInt(parts[1]);
			}
			if(message.startsWith("Done! Saving new search index to"))
				rebuildActionEvents.close();
		});
	});
});
SCRIPT;

			page_renderer::AddJSSnippet($invindex_rebuild_script);
			
			$content .= "<h2>Settings</h2>";
			$content .= "<p>Mouse over the name of each setting to see a description of what it does.</p>\n";
			$content .= "<form action='?action=configure-save' method='post'>\n";
			
			foreach($guiConfig as $configKey => $configData)
			{
				// Don't display the site secret~!
				// Apparently it got lost in translation, but I'll be re-adding
				// it again at some point I'm sure - so support for it is
				// included here.
				if($configKey == "sitesecret") continue;
				
				$reverse = false;
				$inputControl = "";
				$label = "<label for='setting-$configKey' title=\"$configData->description\" class='cursor-query'>$configKey</label>";
				switch($configData->type)
				{
					case "url":
					case "email":
					case "number":
					case "text":
						$inputControl = "<input type='$configData->type' id='$configKey' name='$configKey' value='{$settings->$configKey}' />";
						break;
					case "textarea":
						$inputControl = "<textarea id='$configKey' name='$configKey'>{$settings->$configKey}</textarea>";
						break;
					case "checkbox":
						$reverse = true;
						$inputControl = "<input type='checkbox' id='$configKey' name='$configKey' " . ($settings->$configKey ? " checked" : "") . " />";
						break;
					default:
						$label = "";
						$inputControl = "<p><em>Sorry! The <code>$configKey</code> setting isn't editable yet through the gui. Please try editing <code>peppermint.json</code> for the time being.</em></p>";
						break;
				}
				
				$content .= "<div class='setting-configurator'>\n\t";
				$content .= $reverse ? "$inputControl\n\t$label" : "$label\n\t$inputControl";
				$content .= "\n</div>\n";
			}
			
			$content .= "<input type='submit' value='Save Settings' />";
			$content .= "</form>\n";
			
			exit(page_renderer::render_main("Master Control Panel - $settings->sitename", $content));
		});
		
		/**
		 * @api {post} ?action=configure-save Save changes to the global wiki settings
		 * @apiName ConfigureSettings
		 * @apiGroup Utility
		 * @apiPermission Moderator
		 */
		
		/*
		 *  ██████  ██████  ███    ██ ███████ ██  ██████  ██    ██ ██████  ███████
		 * ██      ██    ██ ████   ██ ██      ██ ██       ██    ██ ██   ██ ██
		 * ██      ██    ██ ██ ██  ██ █████   ██ ██   ███ ██    ██ ██████  █████ █████
		 * ██      ██    ██ ██  ██ ██ ██      ██ ██    ██ ██    ██ ██   ██ ██
		 *  ██████  ██████  ██   ████ ██      ██  ██████   ██████  ██   ██ ███████
		 * ███████  █████  ██    ██ ███████
		 * ██      ██   ██ ██    ██ ██
		 * ███████ ███████ ██    ██ █████
		 *      ██ ██   ██  ██  ██  ██
		 * ███████ ██   ██   ████   ███████
		 */
 		
		
		add_action("configure-save", function () {
			global $env, $settings, $paths, $defaultCSS;
			
		    // If the user isn't an admin, then the regular configuration page will display an appropriate error
			if(!$env->is_admin)
			{
				http_response_code(307);
				header("location: ?action=configure");
				exit();
			}
			
			// Build a new settings object
			$newSettings = new stdClass();
			foreach($settings as $configKey => $rawValue)
			{
				$configValue = $rawValue;
				if(isset($_POST[$configKey]))
				{
					$decodedConfigValue = json_decode($_POST[$configKey]);
					if(json_last_error() === JSON_ERROR_NONE)
						$configValue = $decodedConfigValue;
					else
						$configValue = $_POST[$configKey];
					
					// Convert boolean settings to a boolean, since POST
					// parameters don't decode correctly.
					if(is_bool($settings->$configKey))
						$configValue = in_array($configValue, [ 1, "on"], true) ? true : false;
					
					// If the CSS hasn't changed, then we can replace it with
					// 'auto' - this will ensure that upon update the new
					// default CSS will be used. Also make sure we ignore line
					// ending nonsense & differences here, since they really
					// don't matter
					if($configKey === "css" && str_replace("\r\n", "\n", $defaultCSS) === str_replace("\r\n", "\n", $configValue))
						$configValue = "auto";
				}
				
				$newSettings->$configKey = $configValue;
			}
			
			// Take a backup of the current settings file
			rename($paths->settings_file, "$paths->settings_file.bak");
			// Save the new settings file
			file_put_contents($paths->settings_file, json_encode($newSettings, JSON_PRETTY_PRINT));
			
			$content = "<h1>Master settings updated sucessfully</h1>\n";
			$content .= "<p>$settings->sitename's master settings file has been updated successfully. A backup of the original settings has been created under the name <code>peppermint.json.bak</code>, just in case. You can <a href='?action=configure'>go back</a> and continue editing the master settings file, or you can go to the <a href='?action=view&page=" . rawurlencode($settings->defaultpage) . "'>" . htmlentities($settings->defaultpage) . "</a>.</p>\n";
			$content .= "<p>For reference, the newly generated master settings file is as follows:</p>\n";
			$content .= "<textarea name='content'>";
				$content .= json_encode($newSettings, JSON_PRETTY_PRINT);
			$content .= "</textarea>\n";
			exit(page_renderer::render_main("Master Settings Updated - $settings->sitename", $content));
		});
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
