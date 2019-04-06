<?php

// If the extra data directory:
//  - doesn't exist already
//  - has an mtime before that of this file
// ...extract it again
if(!file_exists($paths->extra_data_directory) || 
	filemtime(__FILE__) > filemtime($paths->extra_data_directory)) {
	if(file_exists($paths->extra_data_directory))
		delete_recursive($paths->extra_data_directory, false);
	else
		mkdir($paths->extra_data_directory, 0700);
	touch($paths->extra_data_directory);
		
	$temp_file = tmpfile();
	$source = fopen(__FILE__, "r");
	
	fseek($source, __COMPILER_HALT_OFFSET__);
	stream_copy_to_stream($source, $temp_file);
	
	$temp_filename = stream_get_meta_data($temp_file)["uri"];
	
	$extractor = new ZipArchive();
	$extractor->open($temp_filename);
	$extractor->extractTo($paths->extra_data_directory);
	$extractor->close();
	fclose($temp_file);
}
