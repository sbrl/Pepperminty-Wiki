<?php
register_module([
	"name" => "Export",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that you can use to export your wiki as a .zip file. Uses \$settings->export_only_allow_admins, which controls whether only admins are allowed to export the wiki.",
	"id" => "page-export",
	"code" => function() {
		add_action("export", function() {
			global $settings, $pageindex, $isadmin;
			
			if($settings->export_allow_only_admins && !$isadmin)
			{
				http_response_code(401);
				exit(page_renderer::render("Export error - $settings->sitename", "Only administrators of $settings->sitename are allowed to export the wiki as a zip. <a href='?action=$settings->defaultaction&page='>Return to the $settings->defaultpage</a>."));
			}
			
			$tmpfilename = tempnam(sys_get_temp_dir(), "pepperminty-wiki-");
			
			$zip = new ZipArchive();
			
			if($zip->open($tmpfilename, ZipArchive::CREATE) !== true)
			{
				http_response_code(507);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty Wiki was unable to open a temporary file to store the exported data in. Please contact $settings->sitename's administrator (" . $settings->admindetails["name"] . " at " . hide_email($settings->admindetails["email"]) . ") for assistance."));
			}
			
			foreach($pageindex as $entry)
			{
				$zip->addFile("./$entry->filename", $entry->filename);
			}
			
			if($zip->close() !== true)
			{
				http_response_code(500);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty wiki was unable to close the temporary zip file after creating it. Please contact $settings->sitename's administrator (" . $settings->admindetails["name"] . " at " . hide_email($settings->admindetails["email"]) . ") for assistance."));
			}
			
			header("content-type: application/zip");
			header("content-disposition: attachment; filename=$settings->sitename-export.zip");
			header("content-length: " . filesize($tmpfilename));
			
			$zip_handle = fopen($tmpfilename, "rb");
			fpassthru($zip_handle);
			fclose($zip_handle);
			unlink($tmpfilename);
		});
	}
]);

?>
