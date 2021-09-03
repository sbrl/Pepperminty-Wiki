<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Export",
	"version" => "0.5.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that you can use to export your wiki as a .zip file. Uses \$settings->export_only_allow_admins, which controls whether only admins are allowed to export the wiki.",
	"id" => "page-export",
	"code" => function() {
		global $settings;
		
		/**
		 * @api		{get}	?action=export	Export the all the wiki's content
		 * @apiDescription	Export all the wiki's content. Please ask for permission before making a request to this URI. Note that some wikis may only allow moderators to export content.
		 * @apiName		Export
		 * @apiGroup	Utility
		 * @apiPermission	Anonymous
		 *
		 * @apiError	InsufficientExportPermissionsError	The wiki has the export_allow_only_admins option turned on, and you aren't logged into a moderator account.
		 * @apiError	CouldntOpenTempFileError		Pepperminty Wiki couldn't open a temporary file to send the compressed archive to.
		 * @apiError	CouldntCloseTempFileError		Pepperminty Wiki couldn't close the temporary file to finish creating the zip archive ready for downloading.
		 */
		
		/*
		 * ███████ ██   ██ ██████   ██████  ██████  ████████ 
		 * ██       ██ ██  ██   ██ ██    ██ ██   ██    ██    
		 * █████     ███   ██████  ██    ██ ██████     ██    
		 * ██       ██ ██  ██      ██    ██ ██   ██    ██    
		 * ███████ ██   ██ ██       ██████  ██   ██    ██    
		 */
		add_action("export", function() {
			global $settings, $pageindex, $env;
			
			if($settings->export_allow_only_admins && !$env->is_admin)
			{
				http_response_code(401);
				exit(page_renderer::render("Export error - $settings->sitename", "Only administrators of $settings->sitename are allowed to export the wiki as a zip. <a href='?action=$settings->defaultaction&page='>Return to the ".htmlentities($settings->defaultpage)."</a>."));
			}
			
			$tmpfilename = tempnam(sys_get_temp_dir(), "pepperminty-wiki-");
			
			$zip = new ZipArchive();
			
			if($zip->open($tmpfilename, ZipArchive::CREATE) !== true) {
				http_response_code(507);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty Wiki was unable to open a temporary file to store the exported data in. Please contact $settings->sitename's administrator (" . htmlentities($settings->admindetails_name) . " at " . hide_email($settings->admindetails_email) . ") for assistance."));
			}
			
			foreach($pageindex as $entry) {
				$zip->addFile("$env->storage_prefix$entry->filename", $entry->filename);
				if(isset($entry->uploadedfilepath))
					$zip->addFile($entry->uploadedfilepath);
			}
			
			if($zip->close() !== true) {
				http_response_code(500);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty wiki was unable to close the temporary zip file after creating it. Please contact $settings->sitename's administrator (" . htmlentities($settings->admindetails_name) . " at " . hide_email($settings->admindetails_email) . ") for assistance (this might be a bug)."));
			}
			
			header("content-type: application/zip");
			header("content-disposition: attachment; filename=".str_replace(["\r", "\n", "\""], "", $settings->sitename)."-export.zip");
			header("content-length: " . filesize($tmpfilename));
			
			$zip_handle = fopen($tmpfilename, "rb");
			fpassthru($zip_handle);
			fclose($zip_handle);
			unlink($tmpfilename);
		});
		
		// Add a section to the help page
		add_help_section("50-export", "Exporting", "<p>$settings->sitename supports exporting the entire wiki's content as a zip. Note that you may need to be a moderator in order to do this. Also note that you should check for permission before doing so, even if you are able to export without asking.</p>
		<p>To perform an export, go to the credits page and click &quot;Export as zip - Check for permission first&quot;.</p>");
	}
]);

?>
