<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


// If the extra data directory:
//  - doesn't exist already
//  - has an mtime before that of this file
//  - is empty
// ...extract it again
if(!file_exists($paths->extra_data_directory) || 
	filemtime(__FILE__) > filemtime($paths->extra_data_directory)
	|| is_directory_empty($paths->extra_data_directory)) {
	
	$error_message_help = "<p>Have you checked that PHP has write access to the directory that <code>index.php</code> is located in (and all it's contents and subdirectories)? Try <code>sudo chown USERNAME:USERNAME -R path/to/directory</code> and <code>sudo chmod -R 0644 path/to/directory; sudo chmod -R +X path/to/directory</code>, where <code>USERNAME</code> is the username that the PHP process is running under.</p>";
	
	if(file_exists($paths->extra_data_directory))
		delete_recursive($paths->extra_data_directory, false);
	else {
		if(!mkdir($paths->extra_data_directory, 0700)) {
			http_response_code(503);
			exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! It looks like $settings->sitename couldn't create the extra data directory to unpack additional files to.</p>$error_message_help"));
		}	
	}
		
	if(!touch($paths->extra_data_directory)) {
		http_response_code(503);
		exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! It looks like $settings->sitename isn't able to change the last modified time of the extra data directory.</p>$error_message_help"));
	}
	
	$temp_filename = tempnam(sys_get_temp_dir(), "PeppermintExtract");
	$temp_file = fopen($temp_filename, "wb+");
	if($temp_file === false) {
		http_response_code(503);
		exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to create a new temporary file with <code>tempnam()</code>. Perhaps your server is mis-configured?</p>"));
	}
	$source = fopen(__FILE__, "r");
	if($source === false) {
		http_response_code(503);
		exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to open itself (i.e. <code>index.php</code>) for reading. $error_message_help</p>"));
	}
	
	fseek($source, __COMPILER_HALT_OFFSET__);
	stream_copy_to_stream($source, $temp_file);
	fclose($temp_file);
	
	$extractor = new ZipArchive();
	if(!class_exists("ZipArchive") || !($extractor instanceof ZipArchive)) {
		if(file_exists($paths->extra_data_directory))
			delete_recursive($paths->extra_data_directory);
		exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to unpack itself because the ZipArchive doesn't exist or is faulty. Please install the PHP zip extension (on apt-based systems it's the <code>php-zip</code> package) and then try again later. You can check that it's installed by inspecting the output of <code>php -m</code>, or running the <a href='https://www.php.net/manual/en/function.phpinfo.php'><code>phpinfo()</code> command</a>.</p>"));
	}
	$ex_error = $extractor->open($temp_filename);
	if($ex_error !== true) {
		if($ex_error !== false) {
			switch ($ex_error) {
				case ZipArchive::ER_EXISTS:
					$ex_error = "A file already exists";
					break;
				case ZipArchive::ER_INCONS:
					$ex_error = "The zip archive was inconsistent";
					break;
				case ZipArchive::ER_INVAL:
					$ex_error = "An invalid argument was passed";
					break;
				case ZipArchive::ER_MEMORY:
					$ex_error = "A malloc memory error occurred";
					break;
				case ZipArchive::ER_NOENT:
					$ex_error = "No such file exists to open";
					break;
				case ZipArchive::ER_NOZIP:
					$ex_error = "The file was not a zip archive";
					break;
				case ZipArchive::ER_READ:
					$ex_error = "An error occurred when attempting to read the file";
					break;
				case ZipArchive::ER_SEEK:
					$ex_error = "An error occurred when seeking while reading the file";
					break;
				
				default:
					$ex_error = "An unknown error occurred (code $ex_error)";
					break;
			}
		}
		else {
			$ex_error = "A generic and unidentifiable error occurred (code false)";
		}
		if(file_exists($paths->extra_data_directory))
			delete_recursive($paths->extra_data_directory);
		exit(page_renderer::render_minimal("Unpacking error - $settings->sitename", "<p>Oops! Unfortunately, $settings->sitename wasn't able to unpack itself properly. This is likely either a server misconfiguration or a bug. ZipArchive, the extractor class used by $settings->sitename says: ${ex_error}.</p>
		<p>Please check that the <code>zip</code> extension is installed properly by inspecting the output of <code>php -m</code>, or running the <a href='https://www.php.net/manual/en/function.phpinfo.php'><code>phpinfo()</code> command</a> in a <code>.php</code> file on your webserver.</p>"));
	}
	$extractor->extractTo($paths->extra_data_directory);
	$extractor->close();
	
	unlink($temp_filename);
	
	unset($ex_error);
	unset($error_message_help);
}
