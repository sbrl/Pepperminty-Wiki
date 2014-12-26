<?php
$start_time = microtime(true);
function logstr($str, $newline = true, $showtime = true)
{
	global $start_time;
	if($showtime)
		echo("[ " . round(microtime(true) - $start_time, 4) . " ] ");
	echo($str);
	if($newline)
		echo("\n");
}

header("content-type: text/plain");

logstr("Checking for existing build....", false);
if(file_exists("index.php"))
{
	log_str("fail!", true, false);
	log_str("A build already exists in this directory.");
	log_str("Please delete it and then run this script again.");

	exit();
}

logstr("pass - no other builds were found.", true, false);

logstr("Reading `core.php`...", false);
$build = file_get_contents("core.php");
logstr("done", true, false);
logstr("Reading `settings.fragment.php`...", false);
$settings = file_get_contents("settings.fragment.php");
logstr("done", true, false);

logstr("Building.....", false);
$build = str_replace([
	"{settings}"
], [
	$settings
], $build);
logstr("done", true, false);

logstr("Writing build....", false);
file_put_contents("index.php", $build);
logstr("done!", true, false);
logstr("*** Build Completed ***");
