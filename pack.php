<?php

// If this script is called directly, it must be because of the downloader.
// If this script is to be called directly, it MUST have been called previously 
// via build.php at least once first.
if(!isset($paths)) {
	$paths = new stdClass();
	$paths->extra_data_directory = "build/._extra_data";
}
$theme_id = $_ENV["PEPPERMINT_THEME"] ?? "default";

if(isset($_GET["determine-latest-version"])) {
	header("content-type: application/json");
	exit(json_encode([
		"latest_version" => trim(file_get_contents("https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/version")),
		"local_version" => trim(file_get_contents("version"))
	]));
}

/**
 * Logs a string to stdout, but only on the CLI.
 * @param  string $line The line to log.
 */
function log_str(string $line) {
	if(php_sapi_name() == "cli")
		echo($line);
	//else error_log($line);
}

/*
███    ███  ██████  ██████  ██    ██ ██      ███████
████  ████ ██    ██ ██   ██ ██    ██ ██      ██
██ ████ ██ ██    ██ ██   ██ ██    ██ ██      █████
██  ██  ██ ██    ██ ██   ██ ██    ██ ██      ██
██      ██  ██████  ██████   ██████  ███████ ███████

██       ██████   █████  ██████  ██ ███    ██  ██████
██      ██    ██ ██   ██ ██   ██ ██ ████   ██ ██
██      ██    ██ ███████ ██   ██ ██ ██ ██  ██ ██   ███
██      ██    ██ ██   ██ ██   ██ ██ ██  ██ ██ ██    ██
███████  ██████  ██   ██ ██████  ██ ██   ████  ██████
*/
	
log_str("*** Beginning main build sequence ***\n");
log_str("Reading in module index...\n");

if(isset($_GET["modules"]))
	$requested_modules = explode(",", $_GET["modules"]);

$module_index = json_decode(file_get_contents("module_index.json"));
$module_list = [];
foreach($module_index as $module) {
	/*
	 * If:
	 *  - The module is optional
	 *  - ...AND we're on the command line
	 *  - ...AND the module isn't specified in the CLI arguments
	 *  - ...AND the special keyword "all" isn't specified in the CLI arguments
	 * ....then skip it.
	 */
	if(
		php_sapi_name() == "cli" &&
		$module->optional &&
		(
			isset($argv) &&
			strrpos(implode(" ", $argv), $module->id) === false &&
			!in_array("all", $argv)
		)
	)
		continue;
	
	/*
	 * If:
	 *  - We're NOT on the command line
	 *  - ...AND the module isn't requested
	 * ...then skip it.
	 */
	if(php_sapi_name() != "cli" && !in_array($module->id, $requested_modules))
		continue;
	
	$module_list[] = $module;
}

/*
██████  ███████ ██████  ███████ ███    ██ ██████  ███████ ███    ██  ██████ ██    ██
██   ██ ██      ██   ██ ██      ████   ██ ██   ██ ██      ████   ██ ██       ██  ██
██   ██ █████   ██████  █████   ██ ██  ██ ██   ██ █████   ██ ██  ██ ██        ████
██   ██ ██      ██      ██      ██  ██ ██ ██   ██ ██      ██  ██ ██ ██         ██
██████  ███████ ██      ███████ ██   ████ ██████  ███████ ██   ████  ██████    ██

███████  ██████  █████  ███    ██ ███    ██ ██ ███    ██  ██████
██      ██      ██   ██ ████   ██ ████   ██ ██ ████   ██ ██
███████ ██      ███████ ██ ██  ██ ██ ██  ██ ██ ██ ██  ██ ██   ███
     ██ ██      ██   ██ ██  ██ ██ ██  ██ ██ ██ ██  ██ ██ ██    ██
███████  ██████ ██   ██ ██   ████ ██   ████ ██ ██   ████  ██████
*/

	
function module_list_search(array $list, string $id) : bool {
	foreach($list as $item) {
		if($item->id === $id)
			return true;
	}
	return false;
}
function module_list_find(array $list, string $id) {
	foreach($list as $item) {
		if($item->id === $id)
			return $item;
	}
	return null;
}

log_str("Scanning for dependencies...\n");
$module_count = count($module_list);
for($i = 0; $i < $module_count; $i++) {
	foreach($module_list[$i]->depends as $dependency) {
		echo("scanning {$module_list[$i]->id}: $dependency\n");
		if(!module_list_search($module_list, $dependency)) {
			log_str("Adding missing dependency $dependency for {$module_list[$i]->id}\n");
			$missing_dependency = module_list_find($module_index, $dependency);
			if($missing_dependency == null) {
				if(php_sapi_name() != "cli") header("content-type: text/plain");
				echo("Error: {$module_list[$i]->id} requires $dependency as a dependency, but it couldn't be found in the module index. This looks like a bug.\n");
				if(php_sapi_name() == "cli") exit(2);
				else exit();
			}
			$module_list[] = $missing_dependency;
			$module_count++;
		}
	}
}

if(php_sapi_name() != "cli") {
	header("content-type: text/php");
	header("content-disposition: attachment; filename=\"index.php\"");
}


/*
 ██████  ██████  ██████  ███████
██      ██    ██ ██   ██ ██
██      ██    ██ ██████  █████
██      ██    ██ ██   ██ ██
 ██████  ██████  ██   ██ ███████
*/
log_str("Reading in core files...\n");

$core_files_list = glob("core/*.php"); natsort($core_files_list);

$core = "<?php\n";
foreach($core_files_list as $core_filename)
	$core .= str_replace([ "<?php", "?>" ], "", file_get_contents($core_filename));


$core = str_replace([
	"{version}",
	"{commit}",
	"{guiconfig}",
	"{default-css}"
], [
	trim(file_get_contents("version")),
	exec("git rev-parse HEAD"),
	trim(file_get_contents("peppermint.guiconfig.json")),
	trim(file_get_contents("themes/$theme_id/theme.css"))
], $core);

$result = $core;


/*
██████   █████   ██████ ██   ██ ███████ ██████
██   ██ ██   ██ ██      ██  ██  ██      ██   ██
██████  ███████ ██      █████   █████   ██████
██      ██   ██ ██      ██  ██  ██      ██   ██
██      ██   ██  ██████ ██   ██ ███████ ██   ██
*/

$extra_data_archive = new ZipArchive();
// Use dev/shm if possible (it's *always* in memory). PHP will default to the system's temporary directory if it's not available
$temp_filename = tempnam("/dev/shm", "pepperminty-wiki-pack");
if($extra_data_archive->open($temp_filename, ZipArchive::CREATE) !== true) {
	http_response_code(503);
	exit("Error: Failed to create temporary stream to store packing information");
}

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
	
	
	// Pack the extra files that were downloaded in build.php
	foreach($module->extra_data as $filepath_pack => $extra_data_item) {
		if(is_string($extra_data_item)) {
			// TODO: Test whether this works for urls. If not, then we'll need to implement a workaround
			log_str("\n[pack] $paths->extra_data_directory/$module->id/$filepath_pack -> $module->id/$filepath_pack\n");
			$extra_data_archive->addFile("$paths->extra_data_directory/$module->id/$filepath_pack", "$module->id/$filepath_pack");
		}
	}
	
	$i++;
}
log_str("\n");

$extra_data_archive->close();


/*
███████ ██ ███    ██ ██ ███████ ██   ██ ███████ ██████
██      ██ ████   ██ ██ ██      ██   ██ ██      ██   ██
█████   ██ ██ ██  ██ ██ ███████ ███████ █████   ██████
██      ██ ██  ██ ██ ██      ██ ██   ██ ██      ██   ██
██      ██ ██   ████ ██ ███████ ██   ██ ███████ ██   ██
*/

$archive_stream = fopen($temp_filename, "r");

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

// Write the built code
fwrite($output_stream, $result);
// Write the delimiter
fwrite($output_stream, "__halt_compiler();");
// Write the extra data
stream_copy_to_stream($archive_stream, $output_stream);

// Cleanup
unlink($temp_filename);
?>
