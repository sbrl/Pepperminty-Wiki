<?php
register_module([
	"name" => "Interwiki links",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds interwiki link support. Point the se",
	"id" => "feature-interwiki-links",
	"code" => function() {
		global $settings;
		if(!empty($settings->interwiki_index_location)) {
			// Generate the interwiki index cache file if it doesn't exist already
			// NOTE: If you want to update the cache file, just delete it & it'll get regenerated automagically :-)
			if(!file_exists($paths->interwiki_index))
				interwiki_index_update();
			else
				$env->interwiki_index = json_decode(file_get_contents($paths->interwiki_index));
		}
	}
]);

/**
 * Updates the interwiki index cache file.
 */
function interwiki_index_update() {
	$env->interwiki_index = new stdClass();
	$interwiki_csv_handle = fopen($settings->interwiki_index_location, "r");
	if($interwiki_csv_handle === false)
		throw new Exception("Error: Failed to read interwiki index from '{$settings->interwiki_index_location}'.");
	
	fgetcsv($interwiki_csv_handle); // Discard the header line
	while(($interwiki_data = fgetcsv($interwiki_csv_handle))) {
		$interwiki_def = new stdClass();
		$interwiki_def->name = $interwiki_data[0];
		$interwiki_def->prefix = $interwiki_data[1];
		$interwiki_def->root_url = $interwiki_data[2];
		
		$env->interwiki_index->$prefix = $interwiki_def;
	}
	
	file_put_contents($paths->interwiki_index, json_encode($env->interwiki_index, JSON_PRETTY_PRINT));
}

function interwiki_pagename_resolve($interwiki_pagename) {
	// If it's not an interwiki link, then don't bother confusing ourselves
	if(strpos($interwiki_pagename, ":") === false)
		return null;
	
	$parts = explode(":", $interwiki_pagename, 2);
	$prefix = $parts[0];
	$pagename = $parts[1];
	
	throw new Exception("Not implemented yet :-\\")
}

?>
