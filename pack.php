<?php

/**
 * Logs a string to stdout, but only on the CLI.
 * @param  string $line The line to log.
 */
function log_str(string $line) {
	if(php_sapi_name() == "cli")
		echo($line);
	//else error_log($line);
}

log_str("*** Beginning main build sequence ***\n");
log_str("Reading in module index...\n");

$module_index = json_decode(file_get_contents("module_index.json"));
$module_list = [];
foreach($module_index as $module)
{
	// If the module is optional, the module's id isn't present in the command line arguments, and the special 'all' module id wasn't passed in, skip it
	if($module->optional &&
		(
			isset($argv) &&
			strrpos(implode(" ", $argv), $module->id) === false &&
			!in_array("all", $argv)
		)
	)
		continue;
	$module_list[] = $module;
}

if(isset($_GET["modules"]))
	$module_list = explode(",", $_GET["modules"]);

if(php_sapi_name() != "cli") {
	header("content-type: text/php");
	header("content-disposition: attachment; filename=\"index.php\"");
}

log_str("Reading in core files...\n");

// We trim from the end here because of the __halt_compiler() directive
$core = rtrim(file_get_contents("core.php"));
$settings = file_get_contents("settings.fragment.php");
$settings = str_replace([ "<?php", "?>" ], "", $settings);
$core = str_replace([
	"//{settings}",
	"{version}",
	"{commit}",
	"{guiconfig}",
	"{default-css}"
], [
	$settings,
	trim(file_get_contents("version")),
	exec("git rev-parse HEAD"),
	trim(file_get_contents("peppermint.guiconfig.json")),
	trim(file_get_contents("theme_default.css"))
], $core);

$result = $core;

$extra_data_archive = new ZipArchive();
if($extra_data_archive->open("php://temp/maxmemory:".(5*1024*1024), ZipArchive::CREATE) !== true) {
	http_response_code(503);
	exit("Error: Failed to create temporary stream to store packing information");
}

// HACK! Determine the file descriptor of the ZipArchvie
$temp = fopen("php://memory", "w");
$archive_file_descriptor = $temp - 1;
fclose($temp);

$module_list_count = count($module_list);
$i = 1;
foreach($module_list as $module)
{
	if($module->id == "") continue;
	
	log_str("[$i / $module_list_count] Adding $module->id      \r");
	
	$module_filepath = "modules/" . preg_replace("[^a-zA-Z0-9\-]", "", $module->id) . ".php";
	
	//log_str("id: $module->id | filepath: $module_filepath\n");
	
	if(!file_exists($module_filepath)) {
		http_response_code(400);
		exit("Failed to load module with name: $module_filepath");
	}
	
	// Pack the module's source code
	$modulecode = file_get_contents($module_filepath);
	$modulecode = str_replace([ "<?php", "?>" ], "", $modulecode);
	$result = str_replace(
		"// %next_module% //",
		"$modulecode\n// %next_module% //",
		$result
	);
	
	
	// Pack the extra files
	foreach($module->extra_data as $filepath_pack => $extra_data_item) {
		if(is_string($extra_data_item)) {
			// TODO: Test whether this works for urls. If not, then we'll need to implement a workaround
			$extra_data_archive->addFile("$paths->extra_data_directory/$module->id/$filepath_pack", "$module->id/$filepath_pack");
		}
	}
	
	$i++;
}
log_str("\n");

$archive_stream = fopen("php://fd/$archive_file_descriptor", "r");

$output_stream = null;
if(php_sapi_name() == "cli") {
	if(file_exists("build/index.php")) {
		log_str("index.php already exists in the build folder, exiting\n");
		exit(1);
	}
	
	log_str("Done. Saving to disk...");
	$output_stream = fopen("build/index.php", "w");
	log_str("complete!\n");
	log_str("*** Build completed! ***\n");
}
else {
	$output_stream = fopen("php://output", "w");
}

fwrite($output_stream, $result);
stream_copy_to_stream($archive_stream, $output_stream);

?>
