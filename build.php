<?php

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

$core = file_get_contents("core.php");
$settings = file_get_contents("settings.fragment.php");
$settings = str_replace([ "<?php", "?>" ], "", $settings);
$core = str_replace("{settings}", $settings, $core);

$result = $core;

foreach($module_list as $module_id)
{
	if($module_id == "") continue;
	
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
	if(file_exists("index.php"))
	{
		echo("index.php already exists, exiting");
		exit(1);
	}
	else
	{
		file_put_contents("index.php", $result);
	}
}
else
{
	exit($result);
}

?>
