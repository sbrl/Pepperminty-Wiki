<?php
register_module([
	"name" => "Uploader",
	"version" => "0.5.11",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File/' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=upload[&avatar=yes] Get a page to let you upload a file.
		 * @apiName UploadFilePage
		 * @apiGroup Upload
		 * @apiPermission User
		 *
		 * @apiParam	{boolean}	avatar	Optional. If true then a special page to upload your avatar is displayed instead.
		*/
		
		/**
		 * @api {post} ?action=upload Upload a file
		 * @apiName UploadFile
		 * @apiGroup Upload
		 * @apiPermission User
		 * 
		 * @apiParam {string}	name		The name of the file to upload.
		 * @apiParam {string}	description	A description of the file.
		 * @apiParam {file}		file		The file to upload.
		 * @apiParam {boolean}	avatar		Whether this upload should be uploaded as the current user's avatar. If specified, any filenames provided will be ignored.
		 *
		 * @apiUse	UserNotLoggedInError
		 * @apiError	UploadsDisabledError	Uploads are currently disabled in the wiki's settings.
		 * @apiError	UnknownFileTypeError	The type of the file you uploaded is not currently allowed in the wiki's settings.
		 * @apiError	ImageDimensionsFiledError	PeppermintyWiki couldn't obtain the dimensions of the image you uploaded.
		 * @apiError	DangerousFileError		The file uploaded appears to be dangerous.
		 * @apiError	DuplicateFileError		The filename specified is a duplicate of a file that already exists.
		 * @apiError	FileTamperedError		Pepperminty Wiki couldn't verify that the file wasn't tampered with during theupload process.
		 */
		
		/*
		 * ██    ██ ██████  ██       ██████   █████  ██████  
		 * ██    ██ ██   ██ ██      ██    ██ ██   ██ ██   ██ 
		 * ██    ██ ██████  ██      ██    ██ ███████ ██   ██ 
		 * ██    ██ ██      ██      ██    ██ ██   ██ ██   ██ 
		 *  ██████  ██      ███████  ██████  ██   ██ ██████  
		 */
		add_action("upload", function() {
			global $settings, $env, $pageindex, $paths;
			
			$is_avatar = !empty($_POST["avatar"]) || !empty($_GET["avatar"]);
			
			switch($_SERVER["REQUEST_METHOD"])
			{
				case "GET":
					// Send upload page
					
					if(!$settings->upload_enabled)
						exit(page_renderer::render("Upload Disabled - $setting->sitename", "<p>You can't upload anything at the moment because $settings->sitename has uploads disabled. Try contacting $settings->admindetails_name, your site Administrator. <a href='javascript:history.back();'>Go back</a>.</p>"));
					if(!$env->is_logged_in)
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You are not currently logged in, so you can't upload anything.</p>
		<p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first.</p>"));
					
					if($is_avatar) {
						exit(page_renderer::render("Upload avatar - $settings->sitename", "<h1>Upload avatar</h1>
			<p>Select an image below, and then press upload. $settings->sitename currently supports the following file types (though not all of them may be suitable for an avatar): " . implode(", ", $settings->upload_allowed_file_types) . "</p>
			<form method='post' action='?action=upload' enctype='multipart/form-data'>
				<label for='file'>Select a file to upload.</label>
				<input type='file' name='file' id='file-upload-selector' tabindex='1' />
				<br />
				
				<p class='editing_message'>$settings->editing_message</p>
				<input type='hidden' name='avatar' value='yes' />
				<input type='submit' value='Upload' tabindex='20' />
			</form>"));
					}
					
					exit(page_renderer::render("Upload - $settings->sitename", "<h1>Upload file</h1>
		<p>Select an image or file below, and then type a name for it in the box. This server currently supports uploads up to " . human_filesize(get_max_upload_size()) . " in size.</p>
		<p>$settings->sitename currently supports uploading of the following file types: " . implode(", ", $settings->upload_allowed_file_types) . ".</p>
		<form method='post' action='?action=upload' enctype='multipart/form-data'>
			<label for='file-upload-selector'>Select a file to upload.</label>
			<input type='file' name='file' id='file-upload-selector' tabindex='1' />
			<br />
			<label for='file-upload-name'>Name:</label>
			<input type='text' name='name' id='file-upload-name' tabindex='5'  />
			<br />
			<label for='description'>Description:</label>
			<textarea name='description' tabindex='10'></textarea>
			<p class='editing_message'>$settings->editing_message</p>
			<input type='submit' value='Upload' tabindex='20' />
		</form>
		<script>
			document.getElementById('file-upload-selector').addEventListener('change', function(event) {
				var newName = event.target.value.substring(event.target.value.lastIndexOf(\"\\\\\") + 1, event.target.value.lastIndexOf(\".\"));
				console.log('Changing content of name box to:', newName);
				document.getElementById('file-upload-name').value = newName;
			});
		</script>"));
					
					break;
				
				case "POST":
					// Recieve file
					
					// Make sure uploads are enabled
					if(!$settings->upload_enabled)
					{
						if(!empty($_FILES["file"]))
							unlink($_FILES["file"]["tmp_name"]);
						http_response_code(412);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because uploads are currently disabled on $settings->sitename. <a href='index.php'>Go back to the main page</a>.</p>"));
					}
					
					// Make sure that the user is logged in
					if(!$env->is_logged_in)
					{
						if(!empty($_FILES["file"]))
							unlink($_FILES["file"]["tmp_name"]);
						http_response_code(401);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because you are not logged in.</p><p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first."));
					}

					// Check for php upload errors
					if($_FILES["file"]["error"] > 0)
					{
						if(!empty($_FILES["file"]))
							unlink($_FILES["file"]["tmp_name"]);
						if($_FILES["file"]["error"] == 1 || $_FILES["file"]["error"] == 2)
							http_response_code(413); // file is too large
						else
							http_response_code(500); // something else went wrong
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because " . (($_FILES["file"]["error"] == 1 || $_FILES["file"]["error"] == 2) ? "the file is too large" : "an error occurred") . ".</p><p>Please contact $settings->admindetails_name, $settings->sitename's administrator for help.</p>"));

					}
					
					// Calculate the target name, removing any characters we
					// are unsure about.
					$target_name = makepathsafe($_POST["name"] ?? "Users/$env->user/Avatar");
					$temp_filename = $_FILES["file"]["tmp_name"];
					
					$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
					$mime_type = finfo_file($mimechecker, $temp_filename);
					finfo_close($mimechecker);
					
					if(!in_array($mime_type, $settings->upload_allowed_file_types))
					{
						http_response_code(415);
						exit(page_renderer::render("Unknown file type - Upload error - $settings->sitename", "<p>$settings->sitename recieved the file you tried to upload successfully, but detected that the type of file you uploaded is not in the allowed file types list. The file has been discarded.</p>
						<p>The file you tried to upload appeared to be of type <code>$mime_type</code>, but $settings->sitename currently only allows the uploading of the following file types: <code>" . implode("</code>, <code>", $settings->upload_allowed_file_types) . "</code>.</p>
						<p><a href='?action=$settings->defaultaction'>Go back</a> to the Main Page.</p>"));
					}
					
					// Perform appropriate checks based on the *real* filetype
					if($is_avatar && substr($mime_type, 0, strpos($mime_type, "/")) !== "image") {
						http_response_code(415);
						exit(page_renderer::render_main("Error uploading avatar - $settings->sitename", "<p>That file appears to be unsuitable as an avatar, as $settings->sitename has detected it to be of type <code>$mime_type</code>, which doesn't appear to be an image. Please try <a href='?action=upload&avatar=yes'>uploading a different file</a> to use as your avatar.</p>"));
					}
					
					switch(substr($mime_type, 0, strpos($mime_type, "/")))
					{
						case "image":
							$extra_data = [];
							// Check SVG uploads with a special function
							$imagesize = $mime_type !== "image/svg+xml" ? getimagesize($temp_filename, $extra_data) : upload_check_svg($temp_filename);
							
							// Make sure that the image size is defined
							if(!is_int($imagesize[0]) or !is_int($imagesize[1]))
							{
								http_response_code(415);
								exit(page_renderer::render("Upload Error - $settings->sitename", "<p>Although the file that you uploaded appears to be an image, $settings->sitename has been unable to determine it's dimensions. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>
								<p>You may wish to consider <a href='https://github.com/sbrl/Pepperminty-Wiki'>opening an issue</a> against Pepperminty Wiki (the software that powers $settings->sitename) if this isn't the first time that you have seen this message.</p>"));
							}
							break;
					}
					
					$file_extension = system_mime_type_extension($mime_type);
					
					// Override the detected file extension if a file extension
					// is explicitly specified in the settings
					if(isset($settings->mime_mappings_overrides->$mime_type))
						$file_extension = $settings->mime_mappings_overrides->$mime_type;
					
					if(in_array($file_extension, [ "php", ".htaccess", "asp", "aspx" ]))
					{
						http_response_code(415);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded appears to be dangerous and has been discarded. Please contact $settings->sitename's administrator for assistance.</p>
						<p>Additional information: The file uploaded appeared to be of type <code>$mime_type</code>, which mapped onto the extension <code>$file_extension</code>. This file extension has the potential to be executed accidentally by the web server.</p>"));
					}
					
					// Rewrite the name to include the _actual_ file extension we've cleverly calculated :D
					
					// The path to the place (relative to the wiki data root)
					// that we're actually going to store the uploaded file itself
					$new_filename = "$paths->upload_file_prefix$target_name.$file_extension";
					// The path (relative, as before) to the description file
					$new_description_filename = "$new_filename.md";
					
					// The page path that the new file will be stored under
					$new_pagepath = $new_filename;
					
					// Rewrite the paths to store avatars in the right place
					if($is_avatar) {
						$new_pagepath = $target_name;
						$new_filename = "$target_name.$file_extension";
					}
					
					if(isset($pageindex->$new_pagepath) && !$is_avatar)
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>A page or file has already been uploaded with the name '$new_filename'. Try deleting it first. If you do not have permission to delete things, try contacting one of the moderators.</p>"));
					
					// Delete the previously uploaded avatar, if it exists
					// In the future we _may_ not need this once we have
					// file history online.
					if($is_avatar && isset($pageindex->$new_pagepath) && $pageindex->$new_pagepath->uploadedfile)
						unlink($pageindex->$new_pagepath->uploadedfilepath);
					
					// Make sure that the palce we're uploading to exists
					if(!file_exists(dirname($env->storage_prefix . $new_filename)))
						mkdir(dirname($env->storage_prefix . $new_filename), 0775, true);
					
					if(!move_uploaded_file($temp_filename, $env->storage_prefix . $new_filename))
					{
						http_response_code(409);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded was valid, but $settings->sitename couldn't verify that it was tampered with during the upload process. This probably means that either is a configuration error, or that $settings->sitename has been attacked. Please contact " . $settings->admindetails_name . ", your $settings->sitename Administrator.</p>"));
					}
					
					$description = $_POST["description"] ?? "_(No description provided)_\n";
					
					// Escape the raw html in the provided description if the setting is enabled
					if($settings->clean_raw_html)
						$description = htmlentities($description, ENT_QUOTES);
					
					file_put_contents($env->storage_prefix . $new_description_filename, $description);
					
					// Construct a new entry for the pageindex
					$entry = new stdClass();
					// Point to the description's filepath since this property
					// should point to a markdown file
					$entry->filename = $new_description_filename; 
					$entry->size = strlen($description ?? "(No description provided)");
					$entry->lastmodified = time();
					$entry->lasteditor = $env->user;
					$entry->uploadedfile = true;
					$entry->uploadedfilepath = $new_filename;
					$entry->uploadedfilemime = $mime_type;
					// Add the new entry to the pageindex
					// Assign the new entry to the image's filepath as that
					// should be the page name.
					$pageindex->$new_pagepath = $entry;
					
					// Generate a revision to keep the page history up to date
					if(module_exists("feature-history"))
					{
						$oldsource = ""; // Only variables can be passed by reference, not literals
						history_add_revision($entry, $description, $oldsource, false);
					}
					
					// Save the pageindex
					file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
					
					if(module_exists("feature-recent-changes"))
					{
						add_recent_change([
							"type" => "upload",
							"timestamp" => time(),
							"page" => $new_pagepath,
							"user" => $env->user,
							"filesize" => filesize($env->storage_prefix . $entry->uploadedfilepath)
						]);
					}
					
					header("location: ?action=view&page=$new_pagepath&upload=success");
					
					break;
			}
		});
		
		/**
		 * @api {get} ?action=preview&page={pageName}[&size={someSize}] Get a preview of a file
		 * @apiName PreviewFile
		 * @apiGroup Upload
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	page		The name of the file to preview.
		 * @apiParam {number}	size		Optional. The size fo the resulting preview. Will be clamped to fit within the bounds specified in the wiki's settings. May also be set to the keyword 'original', which will cause the original file to be returned with it's appropriate mime type instead.
		 *
		 * @apiError	PreviewNoFileError	No file was found associated with the specified page.
		 * @apiError	PreviewUnknownFileTypeError	Pepperminty Wiki was unable to generate a preview for the requested file's type.
		 */
		
		/*
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██ 
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██ 
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██ 
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██ 
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███  
		 */
		add_action("preview", function() {
			global $settings, $env, $pageindex, $start_time;
			
			if(empty($pageindex->{$env->page}->uploadedfilepath))
			{
				$im = errorimage("The page '$env->page' doesn't have an associated file.");
				header("content-type: image/png");
				imagepng($im);
				exit();
			}
			
			$filepath = realpath($env->storage_prefix . $pageindex->{$env->page}->uploadedfilepath);
			$mime_type = $pageindex->{$env->page}->uploadedfilemime;
			$shortFilename = substr($filepath, 1 + (strrpos($filepath, '/') !== false ? strrpos($filepath, '/') : -1));
			
			header("content-disposition: inline; filename=\"$shortFilename\"");
			header("last-modified: " . gmdate('D, d M Y H:i:s T', $pageindex->{$env->page}->lastmodified));
			
			// If the size is set or original, then send (or redirect to) the original image
			// Also do the same for SVGs if svg rendering is disabled.
			if(isset($_GET["size"]) and $_GET["size"] == "original" or
				(empty($settings->render_svg_previews) && $mime_type == "image/svg+xml"))
			{
				// Get the file size
				$filesize = filesize($filepath);
				
				// Send some headers
				header("content-length: $filesize");
				header("content-type: $mime_type");
				
				// Open the file and send it to the user
				$handle = fopen($filepath, "rb");
				fpassthru($handle);
				fclose($handle);
				exit();
			}
			
			// Determine the target size of the image
			$target_size = 512;
			if(isset($_GET["size"]))
				$target_size = intval($_GET["size"]);
			if($target_size < $settings->min_preview_size)
				$target_size = $settings->min_preview_size;
			if($target_size > $settings->max_preview_size)
				$target_size = $settings->max_preview_size;
			
			// Determine the output file type
			$output_mime = $settings->preview_file_type;
			if(isset($_GET["type"]) and in_array($_GET["type"], [ "image/png", "image/jpeg", "image/webp" ]))
				$output_mime = $_GET["type"];
			
			/// ETag handling ///
			// Generate the etag and send it to the client
			$preview_etag = sha1("$output_mime|$target_size|$filepath|$mime_type");
			$allheaders = getallheaders();
			$allheaders = array_change_key_case($allheaders, CASE_LOWER);
			if(!isset($allheaders["if-none-match"]))
			{
				header("etag: $preview_etag");
			}
			else
			{
				if($allheaders["if-none-match"] === $preview_etag)
				{
					http_response_code(304);
					header("x-generation-time: " . (microtime(true) - $start_time));
					exit();
				}
			}
			/// ETag handling end ///
			
			/* Disabled until we work out what to do about caching previews *
			$previewFilename = "$filepath.preview.$outputFormat";
			if($target_size === $settings->default_preview_size)
			{
				// The request is for the default preview size
				// Check to see if we have a preview pre-rendered
				
			}
			*/
			
			$preview = new Imagick();
			switch(substr($mime_type, 0, strpos($mime_type, "/")))
			{
				case "image":
					$preview->readImage($filepath);
					break;
				
				case "application":
					if($mime_type == "application/pdf")
					{
						$preview = new imagick();
						$preview->readImage("{$filepath}[0]");
						$preview->setResolution(300,300);
						$preview->setImageColorspace(255);
						break;
					}
				
				case "video":
				case "audio":
					if($settings->data_storage_dir == ".")
					{
						// The data storage directory is the current directory
						// Redirect to the file isntead
						http_response_code(307);
						header("location: " . $pageindex->{$env->page}->uploadedfilepath);
						exit();
					}
					// TODO: Add support for ranges here.
					// Get the file size
					$filesize = filesize($filepath);
					
					// Send some headers
					header("content-length: $filesize");
					header("content-type: $mime_type");
					
					// Open the file and send it to the user
					$handle = fopen($filepath, "rb");
					fpassthru($handle);
					fclose($handle);
					exit();
					break;
				
				default:
					http_response_code(501);
					$preview = errorimage("Unrecognised file type '$mime_type'.", $target_size);
					header("content-type: image/png");
					imagepng($preview);
					exit();
			}
			
			// Scale the image down to the target size
			$preview->resizeImage($target_size, $target_size, imagick::FILTER_LANCZOS, 1, true);
			
			// Send the completed preview image to the user
			header("content-type: $output_mime");
			header("x-generation-time: " . (microtime(true) - $start_time) . "s");
			$outputFormat = substr($output_mime, strpos($output_mime, "/") + 1);
			$preview->setImageFormat($outputFormat);
			echo($preview->getImageBlob());
			/* Disabled while we work out what to do about caching previews *
			// Save a preview file if there isn't one alreaddy
			if(!file_exists($previewFilename))
				file_put_contents($previewFilename, $preview->getImageBlob());
			*/
		});
		
		/*
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███
		 * 
		 * ██████  ██ ███████ ██████  ██       █████  ██    ██ ███████ ██████
		 * ██   ██ ██ ██      ██   ██ ██      ██   ██  ██  ██  ██      ██   ██
		 * ██   ██ ██ ███████ ██████  ██      ███████   ████   █████   ██████
		 * ██   ██ ██      ██ ██      ██      ██   ██    ██    ██      ██   ██
		 * ██████  ██ ███████ ██      ███████ ██   ██    ██    ███████ ██   ██
		 */
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $pageindex, $env, $settings;
			// Don't do anything if the action isn't view
			if($env->action !== "view")
				return;
			
			if(isset($pageindex->{$env->page}->uploadedfile) and $pageindex->{$env->page}->uploadedfile == true)
			{
				// We are looking at a page that is paired with an uploaded file
				$filepath = $pageindex->{$env->page}->uploadedfilepath;
				$mime_type = $pageindex->{$env->page}->uploadedfilemime;
				$dimensions = $mime_type !== "image/svg+xml" ? getimagesize($env->storage_prefix . $filepath) : getsvgsize($env->storage_prefix . $filepath);
				$fileTypeDisplay = substr($mime_type, 0, strpos($mime_type, "/"));
				$previewUrl = "?action=preview&size=$settings->default_preview_size&page=" . rawurlencode($env->page);
				
				$preview_html = "";
				switch($fileTypeDisplay)
				{
					case "application":
					case "image":
						if($mime_type == "application/pdf")
							$fileTypeDisplay = "file";
						
						$preview_sizes = [ 256, 512, 768, 1024, 1440 ];
						$preview_html .= "\t\t\t<figure class='preview'>
				<img src='$previewUrl' />
				<nav class='image-controls'>
					<ul><li><a href='" . ($env->storage_prefix == "./" ? $filepath : "?action=preview&size=original&page=" . rawurlencode($env->page)) . "'>&#x01f304; Original $fileTypeDisplay</a></li>";
						if($mime_type !== "image/svg+xml")
						{
							$preview_html .= "<li>Other Sizes: ";
							foreach($preview_sizes as $size)
								$preview_html .= "<a href='?action=preview&page=" . rawurlencode($env->page) . "&size=$size'>$size" . "px</a> ";
							$preview_html .= "</li>";
						}
						$preview_html .= "</ul></nav>\n\t\t\t</figure>";
						break;
					
					case "video":
						$preview_html .= "\t\t\t<figure class='preview'>
				<video src='$previewUrl' controls preload='metadata'>Your browser doesn't support HTML5 video, but you can still <a href='$previewUrl'>download it</a> if you'd like.</video>
			</figure>";
						break;
						
					case "audio":
						$preview_html .= "\t\t\t<figure class='preview'>
				<audio src='$previewUrl' controls preload='metadata'>Your browser doesn't support HTML5 audio, but you can still <a href='$previewUrl'>download it</a> if you'd like.</audio>
			</figure>";
				}
				
				$fileInfo = [];
				$fileInfo["Name"] = str_replace("File/", "", $filepath);
				$fileInfo["Type"] = $mime_type;
				$fileInfo["Size"] = human_filesize(filesize($env->storage_prefix . $filepath));
				switch($fileTypeDisplay)
				{
					case "image":
						$dimensionsKey = $mime_type !== "image/svg+xml" ? "Original dimensions" : "Native size";
						$fileInfo[$dimensionsKey] = "$dimensions[0] x $dimensions[1]";
						break;
				}
				$fileInfo["Uploaded by"] = $pageindex->{$env->page}->lasteditor;
				
				$preview_html .= "\t\t\t<h2>File Information</h2>
			<table>";
				foreach ($fileInfo as $displayName => $displayValue)
				{
					$preview_html .= "<tr><th>$displayName</th><td>$displayValue</td></tr>\n";
				}
				$preview_html .= "</table>";
				
				$parts["{content}"] = str_replace("</h1>", "</h1>\n$preview_html", $parts["{content}"]);
			}
		});
		
		// Register a section on the help page on uploading files
		add_help_section("28-uploading-files", "Uploading Files", "<p>$settings->sitename supports the uploading of files, though it is up to " . $settings->admindetails_name . ", $settings->sitename's administrator as to whether it is enabled or not (uploads are currently " . (($settings->upload_enabled) ? "enabled" : "disabled") . ").</p>
		<p>Currently Pepperminty Wiki (the software that $settings->sitename uses) only supports the uploading of images, although more file types should be supported in the future (<a href='//github.com/sbrl/Pepperminty-Wiki/issues'>open an issue on GitHub</a> if you are interested in support for more file types).</p>
		<p>Uploading a file is actually quite simple. Click the &quot;Upload&quot; option in the &quot;More...&quot; menu to go to the upload page. The upload page will tell you what types of file $settings->sitename allows, and the maximum supported filesize for files that you upload (this is usually set by the web server that the wiki is running on).</p>
		<p>Use the file chooser to select the file that you want to upload, and then decide on a name for it. Note that the name that you choose should not include the file extension, as this will be determined automatically. Enter a description that will appear on the file's page, and then click upload.</p>");
	}
]);

/**
 * Calculates the actual maximum upload size supported by the server
 * Returns a file size limit in bytes based on the PHP upload_max_filesize and
 * post_max_size
 * @package feature-upload
 * @author	Lifted from Drupal by @meustrus from Stackoverflow
 * @see		http://stackoverflow.com/a/25370978/1460422 Source Stackoverflow answer
 * @return	integer		The maximum upload size supported bythe server, in bytes.
 */
function get_max_upload_size()
{
	static $max_size = -1;
	if ($max_size < 0) {
		// Start with post_max_size.
		$max_size = parse_size(ini_get('post_max_size'));
		// If upload_max_size is less, then reduce. Except if upload_max_size is
		// zero, which indicates no limit.
		$upload_max = parse_size(ini_get('upload_max_filesize'));
		if ($upload_max > 0 && $upload_max < $max_size) {
			$max_size = $upload_max;
		}
	}
	return $max_size;
}
/**
 * Parses a PHP size to an integer
 * @package feature-upload
 * @author	Lifted from Drupal by @meustrus from Stackoverflow
 * @see		http://stackoverflow.com/a/25370978/1460422 Source Stackoverflow answer
 * @param	string	$size	The size to parse.
 * @return	integer			The number of bytees represented by the specified
 * 							size string.
 */
function parse_size($size) {
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	} else {
		return round($size);
	}
}
/**
 * Checks an uploaded SVG file to make sure it's (at least somewhat) safe.
 * Sends an error to the client if a problem is found.
 * @package feature-upload
 * @param  string $temp_filename The filename of the SVG file to check.
 * @return int[]                The size of the SVG image.
 */
function upload_check_svg($temp_filename)
{
	global $settings;
	// Check for script tags
	if(strpos(file_get_contents($temp_filename), "<script") !== false)
	{
		http_response_code(415);
		exit(page_renderer::render("Upload Error - $settings->sitename", "<p>$settings->sitename detected that you uploaded an SVG image and performed some extra security checks on your file. Whilst performing these checks it was discovered that the file you uploaded contains some Javascript, which could be dangerous. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>
		<p>You may wish to consider <a href='https://github.com/sbrl/Pepperminty-Wiki'>opening an issue</a> against Pepperminty Wiki (the software that powers $settings->sitename) if this isn't the first time that you have seen this message.</p>"));
	}
	
	// Find and return the size of the SVG image
	return getsvgsize($temp_filename);
}

/**
 * Calculates the size of the specified SVG file.
 * @package feature-upload
 * @param	string	$svgFilename	The filename to calculate the size of.
 * @return	int[]					The width and height respectively of the
 * 									specified SVG file.
 */
function getsvgsize($svgFilename)
{
	$svg = simplexml_load_file($svgFilename); // Load it as XML
	if($svg === false)
	{
		http_response_code(415);
		exit(page_renderer::render("Upload Error - $settings->sitename", "<p>When $settings->sitename tried to open your SVG file for checking, it found some invalid syntax. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>"));
	}
	$rootAttrs = $svg->attributes();
	$imageSize = false;
	if(isset($rootAttrs->width) and isset($rootAttrs->height))
		$imageSize = [ intval($rootAttrs->width), intval($rootAttrs->height) ];
	else if(isset($rootAttrs->viewBox))
		$imageSize = array_map("intval", array_slice(explode(" ", $rootAttrs->viewBox), -2, 2));
	
	return $imageSize;
}

/**
 * Creates an images containing the specified text.
 * Useful for sending errors back to the client.
 * @package feature-upload
 * @param	string	$text			The text to include in the image.
 * @param	integer	$target_size	The target width to aim for when creating
 * 									the image.
 * @return	image					The handle to the generated GD image.
 */
function errorimage($text, $target_size = null)
{
	$width = 640;
	$height = 480;
	
	if(!empty($target_size))
	{
		$width = $target_size;
		$height = $target_size * (2 / 3);
	}
	
	$image = imagecreatetruecolor($width, $height);
	imagefill($image, 0, 0, imagecolorallocate($image, 238, 232, 242)); // Set the background to #eee8f2
	$fontwidth = imagefontwidth(3);
	imagestring($image, 3,
		($width / 2) - (($fontwidth * strlen($text)) / 2),
		($height / 2) - (imagefontheight(3) / 2),
		$text,
		imagecolorallocate($image, 17, 17, 17) // #111111
	);
	
	return $image;
}

?>
