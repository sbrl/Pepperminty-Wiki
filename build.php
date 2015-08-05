<?php

if(php_sapi_name() == "cli")
{
	echo("Beginning build...\n");
	echo("Reading in module index...\n");
}

$module_index = json_decode(file_get_contents("module_index.json"));
$module_list = [];
foreach($module_index as $module)
{
	$module_list[] = $module->id;
}

if(isset($_GET["modules"]))
{
	$module_list = explode(",", $_GET["modules"]);
}

if(php_sapi_name() != "cli")
{
	header("content-type: text/php");
}

if(php_sapi_name() == "cli") echo("Reading in core files...");

$core = file_get_contents("core.php");
$settings = file_get_contents("settings.fragment.php");
$settings = str_replace([ "<?php", "?>" ], "", $settings);
$core = str_replace("{settings}", $settings, $core);

$result = $core;

foreach($module_list as $module_id)
{
	if($module_id == "") continue;
	
	if(php_sapi_name() == "cli") echo("Adding $module_id\n");
	
	$module_filepath = "modules/" . preg_replace("[^a-zA-Z0-9\-]", "", $module_id) . ".php";
	
	//echo("id: $module_id | filepath: $module_filepath\n");
	
	if(!file_exists($module_filepath))
	{
		http_response_code(400);
		exit("Failed to load module with name: $module_filepath");
	}
	
	$modulecode = file_get_contents($module_filepath);
	$modulecode = str_replace([ "<?php", "?>" ], "", $modulecode);
	$result = str_replace(
		"// %next_module% //",
		"$modulecode\n// %next_module% //",
		$result);
}

if(php_sapi_name() == "cli")
{
	if(file_exists("build/index.php"))
	{
		echo("index.php already exists in the build folder, exiting\n");
		exit(1);
	}
	else
	{
		echo("Done. Saving to disk...");
		file_put_contents("build/index.php", $result);
		echo("complete!\n");
		echo("*** Build Completed ***\n");
	}
}
else
{
	exit($result);
}

?>
