<?php

if(php_sapi_name() == "cli") {
	echo("*** Beginning main build sequence ***\n");
	echo("Reading in module index...\n");
}

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

if(php_sapi_name() != "cli")
{
	header("content-type: text/php");
	header("content-disposition: attachment; filename=\"index.php\"");
}

if(php_sapi_name() == "cli") echo("Reading in core files...\n");

$core = file_get_contents("core.php");
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

$module_list_count = count($module_list);
$i = 1;
foreach($module_list as $module)
{
	if($module->id == "") continue;
	
	if(php_sapi_name() == "cli")
		echo("[$i / $module_list_count] Adding $module->id      \r");
	
	$module_filepath = "modules/" . preg_replace("[^a-zA-Z0-9\-]", "", $module->id) . ".php";
	
	//echo("id: $module->id | filepath: $module_filepath\n");
	
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
			$extra_data_archive->addFile($extra_data_item, "$module->id/$filepath_pack");
		}
	}
	
	$i++;
}
echo("\n");

if(php_sapi_name() == "cli")
{
	if(file_exists("build/index.php")) {
		echo("index.php already exists in the build folder, exiting\n");
		exit(1);
	}
	else {
		echo("Done. Saving to disk...");
		file_put_contents("build/index.php", $result);
		echo("complete!\n");
		echo("*** Build completed! ***\n");
	}
}
else {
	exit($result);
}

?>
