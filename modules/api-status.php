<?php
register_module([
	"name" => "API status",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Provides a basic JSON status action that provices a few useful bits of information for API consumption.",
	"id" => "api-status",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=status[&minified=type]	Get the json-formatted status of this wiki
		 * @apiName Status
		 * @apiGroup Stats
		 * @apiPermission Anonymous
		 *
		 * @apiParam	{boolean}	Whether or not the result should be minified JSON. Default: false
		 */
		
		
		add_action("status", function() {
			global $version, $env, $settings, $actions;
			
			$minified = ($_GET["minified"] ?? "false") == "true";
			
			$action_names = array_keys(get_object_vars($actions));
			sort($action_names);
			
			$result = new stdClass();
			$result->status = "ok";
			$result->version = $version;
			$result->available_actions = $action_names;
			$result->wiki_name = $settings->sitename;
			$result->logo_url = $settings->favicon;
			
			header("content-type: application/json");
			exit($minified ? json_encode($result) : json_encode($result, JSON_PRETTY_PRINT) . "\n");
		});
		
		add_help_section("960-api-status", "Wiki Status API", "<p>$settings->sitename has a <a href='?action=status'>status page</a> that returns some basic information about the current state of the wiki in <a href='http://www.secretgeek.net/json_3mins'>JSON</a>. It can be used as a connection tester - as the Pepperminty Wiki Android app does.</p>");
	}
]);

?>
