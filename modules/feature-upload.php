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
					
					if($settings->allow_uploads)
						exit(page_renderer::render("Upload - $settings->sitename", "<form method='post' action='?action=upload' enctype='multipart/form-data'>
			<label for='file'>Select a file to upload.</label>
			<input type='file' name='file' />
			<br />
			<label for='filename'>File Name:</label>
			<input type='text' name='filename'  />
			<br />
			<input type='submit' value='Upload' />
		</form>"));
					else
						exit(page_renderer::render("Error - Upload - $settings->sitename", "<p>$settings->sitename does not currently have uploads enabled. <a href='javascript:history.back();'>Go back</a>.</p>"));
					
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

//// Pair of functions to calculate the actual maximum upload size supported by the server
//// Lifted from Drupal by @meustrus from  Stackoverflow. Link to answer:
//// http://stackoverflow.com/a/25370978/1460422
// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size()
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
