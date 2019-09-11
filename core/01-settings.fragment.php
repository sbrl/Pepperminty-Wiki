<?php
/*
 * Pepperminty Wiki
 * ================
 * Inspired by Minty Wiki by am2064
	* Link: https://github.com/am2064/Minty-Wiki
 * 
 * Credits:
	* Code by @Starbeamrainbowlabs
	* Parsedown - by erusev and others on github from http://parsedown.org/
	* Mathematical Expression rendering
		* Code: @con-f-use <https://github.com/con-f-use>
		* Rendering: MathJax (https://www.mathjax.org/)
 * Bug reports:
	* #2 - Incorrect closing tag - nibreh <https://github.com/nibreh/>
	* #8 - Rogue <datalist /> tag - nibreh <https://github.com/nibreh/>
 */
$guiConfig = <<<'GUICONFIG'
{guiconfig}
GUICONFIG;

$settingsFilename = "peppermint.json";

if(file_exists("$settingsFilename.compromised")) {
	http_response_code(500);
	header("content-type: text/plain");
	exit("Error: $settingsFilename.compromised exists on disk, so it's likely you need to block access to 'peppermint.json' from the internet. If you've done this already, please delete $settingsFilename.compromised and reload this page.\n\nThis check was done as part of the first run wizard.");
}

$guiConfig = json_decode($guiConfig);
$settings = new stdClass();
if(!file_exists($settingsFilename)) {
	// Copy the default settings over to the main settings array
	foreach ($guiConfig as $key => $value)
		$settings->$key = $value->default;
	// Generate a random secret
	$settings->secret = bin2hex(random_bytes(16));
	if(file_put_contents("peppermint.json", json_encode($settings, JSON_PRETTY_PRINT)) === false) {
		http_response_code(503);
		exit("Oops! It looks like $settings->sitename wasn't able to write peppermint.json to disk.\nThis file contains all of $settings->sitename's settings, so it's really important!\nHave you checked that PHP has write access to the directory that index.php is located in (and all it's contents and subdirectories)? Try\n\nsudo chown USERNAME:USERNAME -R path/to/directory\n\nand\n\nsudo chmod -R 0644 path/to/directory;\nsudo chmod -R +X path/too/directory\n\n....where USERNAME is the username that the PHP process is running under.");
	}
}
else
	$settings = json_decode(file_get_contents("peppermint.json"));

if($settings === null) {
	header("content-type: text/plain");
	exit("Error: Failed to decode the settings file! Does it contain a syntax error?");
}

// Fill in any missing properties
$settings_upgraded = false;
foreach($guiConfig as $key => $propertyData) {
	if(!property_exists($settings, $key)) {
		error_log("[settings] Upgrading $key");
		$settings->$key = $propertyData->default;
		$settings_upgraded = true;
	}
}
if($settings_upgraded)
	file_put_contents("peppermint.json", json_encode($settings, JSON_PRETTY_PRINT));

// If the first-run wizard hasn't been completed but we've filled in 1 or more new settings, then we must be a pre-existing wiki upgrading from a previous version. We can guarantee this because of the new firstrun_complete setting	
if(!$settings->firstrun_complete && $settings_upgraded) {
	$settings->firstrun_complete = true;
	file_put_contents("peppermint.json", json_encode($settings, JSON_PRETTY_PRINT));
}

// Insert the default CSS if requested
$defaultCSS = <<<THEMECSS
{default-css}
THEMECSS;

// This will automatically save to peppermint.json if an automatic takes place 
// for another reason (such as password rehashing or user data updates), but it 
// doesn't really matter because the site name isn't going to change all that 
// often, and even if it does it shouldn't matter :P
if($settings->sessionprefix == "auto")
	$settings->sessionprefix = "pepperminty-wiki-" . preg_replace('/[^a-z0-9\-_]/', "-", strtolower($settings->sitename));

?>
