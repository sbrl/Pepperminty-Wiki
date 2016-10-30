<?php
register_module([
	"name" => "Settings GUI",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "The module everyone has been waiting for! Adds a web based gui that lets mods change the wiki settings.",
	"id" => "feature-guiconfig",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=configure Change the global wiki settings
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
				$errorMessage = "<p>You don't have permission to change the site settings.</p>\n";
				if(!$env->is_logged_in)
					$errorMessage .= "<p>You could try <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a>.</p>";
				else
					$errorMessage .= "<p>You could try <a href='?action=logout&returnto=%3Faction%3Dconfigure'>logging out</a> and then <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a> again.</a>.</p>";
				exit(page_renderer::render_main("Error - $settings->sitename", $errorMessage));
			}
			
			$content = "<h1>Master Control Panel</h1>";
			$content .= "<p>This page lets you configure the site settings. Please be careful - you can break things easily on this page if you're not careful!</p>";
			
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
						$inputControl = "<input type='$configData->type' id='$configKey' value='{$settings->$configKey}' />";
						break;
					case "textarea":
						$inputControl = "<textarea id='$configKey'>{$settings->$configKey}</textarea>";
						break;
					case "checkbox":
						$reverse = true;
						$inputControl = "<input type='checkbox' id='$configKey' " . ($settings->$configKey ? " checked" : "") . " />";
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
			
			exit(page_renderer::render_main("Master Control Panel - $settings->sitename", $content));
		});
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
