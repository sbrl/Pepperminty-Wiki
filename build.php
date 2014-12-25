<?php
header("content-type: text/plain");
echo("Checking for existing build....");
if(file_exists("index.php"))
	exit("fail!\nA build already exists in this directory.\nPlease delete it and then run this script again.");

echo("pass - no other builds were found.\n");

echo("Reading core.php...");
$build = file_get_contents("core.php");
echo("done\n");

echo("Writing build....");
file_put_contents("index.php", $build);
echo("done!\n*** Build Completed ***\n");
