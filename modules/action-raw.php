<?php
register_module([
	"name" => "Raw page source",
	"version" => "0.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		add_action("raw", function() {
			global $env;
			
			http_response_code(307);
			header("x-filename: " . rawurlencode($env->page) . ".md");
			header("content-type: text/markdown");
			exit(file_get_contents("$env->page.md"));
			exit();
		});
	}
]);

?>
