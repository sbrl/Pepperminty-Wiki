<?php
register_module([
	"name" => "Uploader",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File:' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		add_action("upload", function() {
			global $settings, $env, $pageindex;
			
			
			switch($_SERVER["REQUEST_METHOD"])
			{
				case "GET":
					// Send upload page
					
					if(!$settings->upload_enabled)
						exit(page_renderer::render("Upload Disabled - $setting->sitename", "<p>You can't upload anything at the moment because $settings->sitename has uploads disabled. Try contacting " . $settings->admindetails["name"] . ", your site Administrator. <a href='javascript:history.back();'>Go back</a>.</p>"));
					if(!$env->is_logged_in)
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You are not currently logged in, so you can't upload anything.</p>
		<p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first.</p>"));
					
					exit(page_renderer::render("Upload - $settings->sitename", "<p>Select an image below, and then type a name for it in the box. This server currently supports uploads up to " . get_max_upload_size() . " in size.</p>
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
					
					// Calculate the target ename, removing any characters we
					// are unsure about.
					$target_name = makepathsafe($_POST["name"]);
					$temp_filename = $_FILES["file"]["tmp_name"];
					
					$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
					$mime_type = finfo_file($mimechecker, $temp_filename);
					finfo_close($mimechecker);
					
					// Perform appropriate checks based on the *real* filetype
					switch(substr($mime_type, 0, strpos($mime_type, "/")))
					{
						case "image":
							$extra_data = [];
							$imagesize = getimagesize($temp_filename, $extra_data);
							// Make sure that the image size is defined
							if(!is_int($imagesize[0]) or !is_int($imagesize[1]))
								exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file that you uploaded doesn't appear to be an image. $settings->sitename currently only supports uploading images (videos coming soon). <a href='?action=upload'>Go back to try again</a>.</p>"));
							
							break;
						
						case "video":
							exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You uploaded a video, but $settings->sitename doesn't support them yet. Please try again later.</p>"));
						
						default:
							exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You uploaded an unnknown file type which couldn't be processed. $settings->sitename thinks that the file you uploaded was a(n) '$mime_type', which isn't supported.</p>"));
					}
					
					$file_extension = system_mime_type_extension($mime_type);
					
					$new_filename = "Files/$target_name.$file_extension";
					$new_description_filename = "$new_filename.md";
					
					if(isset($pageindex->$new_filename))
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>A page or file has already been uploaded with the name '$new_filename'. Try deleting it first. If you do not have permission to delete things, try contacting one of the moderators.</p>"));
					
					if(!file_exists("Files"))
						mkdir("Files", 0664);
					
					if(!move_uploaded_file($temp_filename, $new_filename))
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded was valid, but $settings->sitename couldn't verify that it was tampered with during the upload process. This probably means that $settings->sitename has been attacked. Please contact " . $settings->admindetails . ", your $settings->sitename Administrator.</p>"));
					
					file_put_contents($new_description_filename, $_POST["description"]);
					
					$description = $_POST["description"];
					
					if($settings->clean_raw_html)
						$description = htmlentities($description, ENT_QUOTES);
					
					file_put_contents($new_description_filename, $description);
					
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
					file_put_contents("pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
					
					header("location: ?action=view&page=$new_filename&upload=success");
					
					break;
			}
		});
		add_action("preview", function() {
			global $settings, $env, $pageindex;
			
			$filepath = $pageindex->{$env->page}->uploadedfilepath;
			$mime_type = $pageindex->{$env->page}->uploadedfilemime;
			
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
			
			switch(substr($mime_type, 0, strpos($mime_type, "/")))
			{
				case "image":
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
							$image = errorimage("Unsupported image type.");
							break;
					}
					
					$raw_width = imagesx($image);
					$raw_height = imagesy($image);
					
					$image = resize_image($image, $target_size);
					
					header("content-type: $output_mime");
					switch($output_mime)
					{
						case "image/jpeg":
							imagejpeg($image);
							break;
						case "image/png":
							imagepng($image);
							break;
						default:
						case "image/webp":
							imagewebp($image);
							break;
					}
					break;
				
				default:
					http_response_code(501);
					exit("Unrecognised file type.");
			}
			
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
	imagetext($image, 3,
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
	
	$new_height = $cur_height * $ratio;
	$new_width = $cur_width * $ratio;
	
	return imagescale($image, $new_width, $new_height);
}

?>
