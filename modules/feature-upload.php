<?php
register_module([
	"name" => "Uploader",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File:' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		add_action("upload", function() {
			global $settings;
			
			
			switch($_SERVER["REQUEST_METHOD"])
			{
				case "GET":
					// Send upload page
					break;
				
				case "PUT":
				case "POST":
					// Recieve file
					
					
					
					break;
			}
		});
		add_action("preview", function() {
			global $settings;
			
			// todo render a preview here
			
			/*
			 * size (image outputs only, possibly width / height)
				 * 1-2048 (configurable)
			 * filetype
				 * either a mime type or 'native'
			 */
		});
		
		page_renderer::register_part_preprocessor(function(&$parts) {
			// Todo add the preview to the top o fthe page here, but onyl if the current action is view and we are on a page prefixed with file:
		});
	}
]);

?>
