<?php
register_module([
	"name" => "Uploader",
	"version" => "0.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File:' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		global $settings;
		
		/*
		 * ██    ██ ██████  ██       ██████   █████  ██████  
		 * ██    ██ ██   ██ ██      ██    ██ ██   ██ ██   ██ 
		 * ██    ██ ██████  ██      ██    ██ ███████ ██   ██ 
		 * ██    ██ ██      ██      ██    ██ ██   ██ ██   ██ 
		 *  ██████  ██      ███████  ██████  ██   ██ ██████  
		 */
		add_action("upload", function() {
			global $settings, $env, $pageindex, $paths;
			
			switch($_SERVER["REQUEST_METHOD"])
			{
				case "GET":
					// Send upload page
					
					if(!$settings->upload_enabled)
						exit(page_renderer::render("Upload Disabled - $setting->sitename", "<p>You can't upload anything at the moment because $settings->sitename has uploads disabled. Try contacting " . $settings->admindetails["name"] . ", your site Administrator. <a href='javascript:history.back();'>Go back</a>.</p>"));
					if(!$env->is_logged_in)
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You are not currently logged in, so you can't upload anything.</p>
		<p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first.</p>"));
					
					exit(page_renderer::render("Upload - $settings->sitename", "<p>Select an image below, and then type a name for it in the box. This server currently supports uploads up to " . human_filesize(get_max_upload_size()) . " in size.</p>
		<p>$settings->sitename currently supports uploading of the following file types: " . implode(", ", $settings->upload_allowed_file_types) . ".</p>
		<form method='post' action='?action=upload' enctype='multipart/form-data'>
			<label for='file'>Select a file to upload.</label>
			<input type='file' name='file' />
			<br />
			<label for='name'>Name:</label>
			<input type='text' name='name'  />
			<br />
			<label for='description'>Description:</label>
			<textarea name='description'></textarea>
			<br />
			<input type='submit' value='Upload' />
		</form>"));
					
					break;
				
				case "POST":
					// Recieve file
					
					// Make sure uploads are enabled
					if(!$settings->upload_enabled)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(412);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because uploads are currently disabled on $settings->sitename. <a href='index.php'>Go back to the main page</a>.</p>"));
					}
					
					// Make sure that the user is logged in
					if(!$env->is_logged_in)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(401);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because you are not logged in.</p><p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first."));
					}
					
					// Calculate the target name, removing any characters we
					// are unsure about.
					$target_name = makepathsafe($_POST["name"]);
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
					switch(substr($mime_type, 0, strpos($mime_type, "/")))
					{
						case "image":
							$extra_data = [];
							$imagesize = getimagesize($temp_filename, $extra_data);
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
					if(isset($settings->mime_mappings_overrides[$mime_type]))
						$file_extension = $settings->mime_mappings_overrides[$mime_type];
					
					if(in_array($file_extension, [ "php", ".htaccess", "asp" ]))
					{
						http_response_code(415);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded appears to be dangerous and has been discarded. Please contact $settings->sitename's administrator for assistance.</p>
						<p>Additional information: The file uploaded appeared to be of type <code>$mime_type</code>, which mapped onto the extension <code>$file_extension</code>. This file extension has the potential to be executed accidentally by the web server.</p>"));
					}
					
					$new_filename = "$paths->upload_file_prefix$target_name.$file_extension";
					$new_description_filename = "$new_filename.md";
					
					if(isset($pageindex->$new_filename))
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>A page or file has already been uploaded with the name '$new_filename'. Try deleting it first. If you do not have permission to delete things, try contacting one of the moderators.</p>"));
					
					if(!file_exists("Files"))
						mkdir("Files", 0664);
					
					if(!move_uploaded_file($temp_filename, $env->storage_prefix . $new_filename))
					{
						http_response_code(409);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded was valid, but $settings->sitename couldn't verify that it was tampered with during the upload process. This probably means that $settings->sitename has been attacked. Please contact " . $settings->admindetails . ", your $settings->sitename Administrator.</p>"));
					}
					
					$description = $_POST["description"];
					
					// Escape the raw html in the provided description if the setting is enabled
					if($settings->clean_raw_html)
						$description = htmlentities($description, ENT_QUOTES);
					
					file_put_contents($env->storage_prefix . $new_description_filename, $description);
					
					// Construct a new entry for the pageindex
					$entry = new stdClass();
					// Point to the description's filepath since this property
					// should point to a markdown file
					$entry->filename = $new_description_filename; 
					$entry->size = strlen($description);
					$entry->lastmodified = time();
					$entry->lasteditor = $env->user;
					$entry->uploadedfile = true;
					$entry->uploadedfilepath = $new_filename;
					$entry->uploadedfilemime = $mime_type;
					// Add the new entry to the pageindex
					// Assign the new entry to the image's filepath as that
					// should be the page name.
					$pageindex->$new_filename = $entry;
					
					// Save the pageindex
					file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
					
					header("location: ?action=view&page=$new_filename&upload=success");
					
					break;
			}
		});
		
		/*
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██ 
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██ 
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██ 
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██ 
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███  
		 */
		add_action("preview", function() {
			global $settings, $env, $pageindex;
			
			$filepath = $env->storage_prefix . $pageindex->{$env->page}->uploadedfilepath;
			$mime_type = $pageindex->{$env->page}->uploadedfilemime;
			
			if(isset($_GET["size"]) and $_GET["size"] == "original")
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
			
			$preview_image = false;
			switch(substr($mime_type, 0, strpos($mime_type, "/")))
			{
				case "image":
					// Read in the image
					$image = false;
					switch($mime_type)
					{
						case "image/jpeg":
							$image = imagecreatefromjpeg($filepath);
							break;
						case "image/gif":
							$image = imagecreatefromgif($filepath);
							break;
						case "image/png":
							$image = imagecreatefrompng($filepath);
							break;
						case "image/webp":
							$image = imagecreatefromwebp($filepath);
							break;
						default:
							http_response_code(415);
							$image = errorimage("Unsupported image type.");
							break;
					}
					
					// Get the size of the image for later
					$raw_width = imagesx($image);
					$raw_height = imagesy($image);
					
					// Resize the image
					$preview_image = resize_image($image, $target_size);
					// Delete the temporary image.
					imagedestroy($image);
					break;
				
				default:
					http_response_code(501);
					$preview_image = errorimage("Unrecognised file type '$mime_type'.");
			}
			
			// Send the completed preview image to the user
			header("content-type: $output_mime");
			switch($output_mime)
			{
				case "image/jpeg":
					imagejpeg($preview_image);
					break;
				case "image/png":
					imagepng($preview_image);
					break;
				default:
				case "image/webp":
					imagewebp($preview_image);
					break;
			}
			imagedestroy($preview_image);
		});
		
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $pageindex, $env, $settings;
			// Todo add the preview to the top of the page here, but only if the current action is view and we are on a page that is a file
			if(isset($pageindex->{$env->page}->uploadedfile) and $pageindex->{$env->page}->uploadedfile == true)
			{
				// We are looking at a page that is paired with an uploaded file
				$filepath = $pageindex->{$env->page}->uploadedfilepath;
				$mime_type = $pageindex->{$env->page}->uploadedfilemime;
				$dimensions = getimagesize($env->storage_prefix . $filepath);
				
				$preview_sizes = [ 256, 512, 768, 1024, 1440 ];
				$preview_html = "<figure class='preview'>
			<img src='?action=preview&size=$settings->default_preview_size&page=" . rawurlencode($env->page) . "' />
			<nav class='image-controls'>
				<ul><li><a href='" . ($env->storage_prefix == "./" ? $filepath : "?action=preview&size=original&page=" . rawurlencode($env->page)) . "'>&#x01f304; Original image</a></li>
				<li>Other Sizes: ";
				foreach($preview_sizes as $size)
					$preview_html .= "<a href='?action=preview&page=" . rawurlencode($env->page) . "&size=$size'>$size" . "px</a> ";
				$preview_html .= "</li></ul></nav>
			</figure>
			<h2>File Information</h2>
			<table><tr><th>Name</th><td>" . str_replace("File/", "", $filepath) . "</td>
			<tr><th>Type</th><td>$mime_type</td></tr>
			<tr><th>Size</th><td>" . human_filesize(filesize($filepath)) . "</td></tr>";
			if(substr($mime_type, strpos($mime_type, "/")) == "image")
				$preview_html .= "<tr><th>Original dimensions</th><td>$dimensions[0] x $dimensions[1]</td></tr>";
			$preview_html .= "<tr><th>Uploaded by</th><td>" . $pageindex->{$env->page}->lasteditor . "</td></tr></table>
			<h2>Description</h2>";
				
				$parts["{content}"] = str_replace("</h1>", "</h1>\n$preview_html", $parts["{content}"]);
			}
		});
		
		// Register a section on the help page on uploading files
		add_help_section("28-uploading-files", "Uploading Files", "<p>$settings->sitename supports the uploading of files, though it is up to " . $settings->admindetails["name"] . ", $settings->sitename's administrator as to whether it is enabled or not (uploads are currently " . (($settings->upload_enabled) ? "enabled" : "disabled") . ").</p>
		<p>Currently Pepperminty Wiki (the software that $settings->sitename uses) only supports the uploading of images, although more file types should be supported in the future (<a href='//github.com/sbrl/Pepperminty-Wiki/issues'>open an issue on GitHub</a> if you are interested in support for more file types).</p>
		<p>Uploading a file is actually quite simple. Click the &quot;Upload&quot; option in the &quot;More...&quot; menu to go to the upload page. The upload page will tell you what types of file $settings->sitename allows, and the maximum supported filesize for files that you upload (this is usually set by the web server that the wiki is running on).</p>
		<p>Use the file chooser to select the file that you want to upload, and then decide on a name for it. Note that the name that you choose should not include the file extension, as this will be determined automatically. Enter a description that will appear on the file's page, and then click upload.</p>");
	}
]);

//// Pair of functions to calculate the actual maximum upload size supported by the server
//// Lifted from Drupal by @meustrus from  Stackoverflow. Link to answer:
//// http://stackoverflow.com/a/25370978/1460422
// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
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

function errorimage($text)
{
	$width = 640;
	$height = 480;
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

function resize_image($image, $size)
{
	$cur_width = imagesx($image);
	$cur_height = imagesy($image);
	
	if($cur_width < $size and $cur_height < $size)
		return $image;
	
	$width_ratio = $size / $cur_width;
	$height_ratio = $size / $cur_height;
	$ratio = min($width_ratio, $height_ratio);
	
	$new_height = floor($cur_height * $ratio);
	$new_width = floor($cur_width * $ratio);
	
	header("x-resize-to: $new_width x $new_height\n");
	
	return imagescale($image, $new_width, $new_height);
}

?>
