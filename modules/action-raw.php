<?php
register_module([
	"name" => "Raw page source",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		add_action("raw", function() {
			global $page;
			
			http_response_code(307);
			header("x-filename: " . rawurlencode($page) . ".md");
			header("content-type: text/markdown");
			exit(file_get_contents("$page.md"));
			exit();
		});
	}
]);

?>
