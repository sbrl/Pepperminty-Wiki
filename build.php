<?php

echo("*** Preparing environment ***\n");

$build_env = new stdClass();
$build_env->target = "build/index.php";

if(file_exists($build_env->target))
{
	echo("Deleting old target...\n");
	unlink($build_env->target);
}

//////////////////////////////////////////////////////////////////////
//////////////////////// Rebuild Module Index ////////////////////////
//////////////////////////////////////////////////////////////////////

echo("*** Rebuilding module index ***\n");
$modules = glob("modules/*.php");
$module_index = [];

function register_module($settings)
{
	global $module_index;
	$newmodule = [
		"name" => $settings["name"],
		"version" => $settings["version"],
		"author" => $settings["author"],
		"description" => $settings["description"],
		"id" => $settings["id"],
		"lastupdate" => filemtime("modules/" . $settings["id"] . ".php")
	];
	$module_index[] = $newmodule;
}

foreach($modules as $filename)
{
	echo("Processing $filename\n");
	require($filename);
}

echo("*** Processing complete ***\n");

echo("Writing new module index to disk...");
file_put_contents("module_index.json", json_encode($module_index, JSON_PRETTY_PRINT));
echo("done\n");


//////////////////////////////////////////////////////////////////////
////////////////////////// Build New Target //////////////////////////
//////////////////////////////////////////////////////////////////////
require("pack.php");

?>
