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
			global $settings, $guiConfig;
			
			$content = "";
			
			foreach($guiConfig as $configKey => $configData)
			{
				$reverse = false;
				$inputControl = "";
				$label = "<label for='setting-$configKey'>$configKey</label>";
				switch($configData->type)
				{
					case "url":
					case "number":
					case "text":
						$inputControl = "<input type='$configData->type' id='$configKey' value='$settings->$configKey' />";
						break;
					case "textarea":
						$inputControl = "<textarea id='$configKey'>$settings->$configKey</textarea>";
					case "checkbox":
						$reverse = true;
						$inputControl = "<input type='checkbox' id='$configKey' " . ($settings->$configKey ? " checked" : "") . " />";
					default:
						$label = "";
						$inputControl = "<p><em>Sorry! The <code>$configKey</code> setting isn't editable yet through the gui. Please try editing <code>peppermint.json</code> for the time being.</p>";
				}
				
				$content .= !$reverse ? "$inputControl\n$label\n" : "$label\n$inputControl\n";
			}
			
			exit(file_get_contents("$env->storage_prefix$env->page.md"));
			exit();
		});
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
