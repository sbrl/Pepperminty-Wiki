<?php
register_module([
	"name" => "Update",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an update page that downloads the latest stable version of Pepperminty Wiki. This module is currently outdated as it doesn't save your module preferences.",
	"id" => "page-update",
	"code" => function() {
		add_action("update", function() {
			global $settings, $isadmin;
			
			if(!$isadmin)
			{
				http_response_code(401);
				exit(page_renderer::render_main("Update - Error", "<p>You must be an administrator to do that.</p>"));
			}
			
			if(!isset($_GET["do"]) or $_GET["do"] !== "true")
			{
				exit(page_renderer::render_main("Update $settings->sitename", "<p>This page allows you to update $settings->sitename.</p>
				<p>Currently, $settings->sitename is using $settings->version of Pepperminty Wiki.</p>
				<p>This script will automatically download and install the latest version of Pepperminty Wiki from the url of your choice (see settings), regardless of whether an update is actually needed (version checking isn't implemented yet).</p>
				<p>To update $settings->sitename, fill out the form below and click click the update button.</p>
				<p>Note that a backup system has not been implemented yet! If this script fails you will loose your wiki's code and have to re-build it.</p>
				<form method='get' action=''>
					<input type='hidden' name='action' value='update' />
					<input type='hidden' name='do' value='true' />
					<label for='secret'>$settings->sitename's secret code</label>
					<input type='text' name='secret' value='' />
					<input type='submit' value='Update' />
				</form>"));
			}
			
			if(!isset($_GET["secret"]) or $_GET["secret"] !== $settings->sitesecret)
			{
				exit(page_renderer::render_main("Update $settings->sitename - Error", "<p>You forgot to enter $settings->sitename's secret code or entered it incorrectly. $settings->sitename's secret can be found in the settings portion of <code>index.php</code>.</p>"));
			}
			
			$settings_separator = "/////////////// Do not edit below this line unless you know what you are doing! ///////////////";
			
			$log = "Beginning update...\n";
			
			$log .= "I am <code>" . __FILE__ . "</code>.\n";
			$oldcode = file_get_contents(__FILE__);
			$log .= "Fetching new code...";
			$newcode = file_get_contents($settings->updateurl);
			$log .= "done.\n";
			
			$log .= "Rewriting <code>" . __FILE__ . "</code>...";
			$settings = substr($oldcode, 0, strpos($oldcode, $settings_separator));
			$code = substr($newcode, strpos($newcode, $settings_separator));
			$result = $settings . $code;
			$log .= "done.\n";
			
			$log .= "Saving...";
			file_put_contents(__FILE__, $result);
			$log .= "done.\n";
			
			$log .= "Update complete. I am now running on the latest version of Pepperminty Wiki.";
			$log .= "The version number that I have updated to can be found on the credits or help ages.";
			
			exit(page_renderer::render_main("Update - Success", "<ul><li>" . implode("</li><li>", explode("\n", $log)) . "</li></ul>"));
		});
	}
]);
?>
