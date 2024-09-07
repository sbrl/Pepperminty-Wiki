<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

// This is the Pepperminty Wiki build environment
define("PEPPERMINTY_WIKI_BUILD", true);

echo("*** Preparing environment ***\n");

ini_set("user_agent", "Pepperminty-Wiki-Downloader PHP/" . phpversion() . "; +https://github.com/sbrl/Pepperminty-Wiki/ Pepperminty-Wiki/" . file_get_contents("version"));

$build_env = new stdClass();
$build_env->target = "build/index.php";

if(file_exists($build_env->target)) {
	echo("Deleting old target...\n");
	unlink($build_env->target);
}

//////////////////////////////////////////////////////////////////////
//////////////////////// Rebuild Module Index ////////////////////////
//////////////////////////////////////////////////////////////////////

echo("*** Rebuilding module index ***\n");
$modules = glob("modules/*.php");
$module_index = [];
// Defined just in case a module needs to reference them when we require() them
// to gain information
$env = new stdClass(); $paths = new stdClass();
$paths->extra_data_directory = "build/._extra_data";

/**
 * Registers a new Pepperminty Wiki module. All module files should call this first.
 * @param	array	$settings An associative array defining the module.
 */
function register_module($settings)
{
	global $module_index, $paths;
	
	// Prepare any extra files
	if(!file_exists($paths->extra_data_directory))
		mkdir($paths->extra_data_directory, 0750, true);
	
	foreach($settings["extra_data"] ?? [] as $filename => $file_def) {
		$destination_filename = "$paths->extra_data_directory/{$settings["id"]}/$filename";
		if(!file_exists(dirname($destination_filename)))
			mkdir(dirname($destination_filename), 0750, true);
		
		$source = fopen($file_def, "r");
		$destination = fopen($destination_filename, "w");
		
		stream_copy_to_stream($source, $destination);
		fclose($source);
		fclose($destination);
	}
	
	$newmodule = [
		"id" => $settings["id"],
		"name" => $settings["name"],
		"version" => $settings["version"],
		"author" => $settings["author"],
		"description" => $settings["description"],
		"lastupdate" => filemtime("modules/" . $settings["id"] . ".php"),
		// May not be set. Defaults to false
		"optional" => $settings["optional"] ?? false,
		"extra_data" => $settings["extra_data"] ?? [],
		"depends" => $settings["depends"] ?? []
	];
	$module_index[] = $newmodule;
}

$module_count = count($modules);
$i = 1;
foreach($modules as $filename) {
	echo("[$i / $module_count] Processing $filename          \r");
	require($filename);
	$i++;
}

echo("\n*** Processing complete ***\n");

echo("Writing new module index to disk...");
file_put_contents("module_index.json", json_encode($module_index, JSON_PRETTY_PRINT));
echo("done\n");


//////////////////////////////////////////////////////////////////////
////////////////////////// Build New Target //////////////////////////
//////////////////////////////////////////////////////////////////////
require("pack.php");

?>
