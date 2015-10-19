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
					
					if($settings->upload_enabled && $env->is_logged_in)
						exit(page_renderer::render("Upload - $settings->sitename", "<p>Select an image below, and then type a name for it in the box. This server currently supports uploads up to " . get_max_upload_size() . " in size.</p>
		<p>$settings->sitename currently supports uploading of the following file types: " . implode(", ", $settings->upload_allowed_file_types) . ".</p>
		<form method='post' action='?action=upload' enctype='multipart/form-data'>
			<label for='file'>Select a file to upload.</label>
			<input type='file' name='file' />
			<br />
			<label for='filename'>File Name:</label>
			<input type='text' name='filename'  />
			<br />
			<input type='submit' value='Upload' />
		</form>"));
					else
						exit(page_renderer::render("Error - Upload - $settings->sitename", "<p>$settings->sitename does not currently have uploads enabled, or you do not currently have permission to upload files because you are not logged in. <a href='javascript:history.back();'>Go back</a>.</p>"));
					
					break;
				
				case "POST":
					// Recieve file
					
					if(!$settings->allow_uploads)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(412);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because uploads are currently disabled on $settings->sitename. <a href='index.php'>Go back to the main page</a>.</p>"));
					}
					
					if(!$env->is_logged_in)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(401);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because you are not logged in.</p><p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first."));
					}
					
					// Calculate the target filename, removing any characters we
					// are unsure about.
					$target_filename = preg_replace("/[^a-z0-9\-_]/i", "", $_POST["filename"]);
					
					$extra_data = [];
					$imagesize = getimagesize($_FILES["file"]["tmp_name"], $extra_data);
					echo("Raw file information: ");
					var_dump($_FILES);
					echo("Image sizing information: ");
					var_dump($imagesize);
					echo("Extra embedded information: ");
					var_dump($extra_data);
					
					unlink($_FILES["file"]["tmp_name"]);
					
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

?>
