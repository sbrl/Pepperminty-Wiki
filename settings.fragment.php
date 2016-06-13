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
$guiConfig = <<<GUICONFIG
{guiconfig}
GUICONFIG;
$guiConfig = json_decode($guiConfig);
$settings = new stdClass();
if(!file_exists("peppermint.json"))
{
	// Copy the default settings over to the main settings array
	foreach ($guiConfig as $key => $value)
		$settings->$key = $value->default;
	// Generate a random secret
	$settings->secret = bin2hex(openssl_random_pseudo_bytes($bits));
	file_put_contents("peppermint.json", json_encode($settings, JSON_PRETTY_PRINT));
}
else
	$settings = json_decode(file_get_contents("peppermint.json"));

if($settings->css === "auto")
{
	$settings->css = <<<THEMECSS
	{defaults-css}
	THEMECSS;
}

/*** Notes ***
Actions:
	view - view a page
		page - page name
		printable=[yes/no] - make output printable
	edit - open editor for page
		page - page name
	save - save edits to page
		page - page name
	list - list pages
		category - the category to list [optional] [unimplemented]
	login - login to the site
	logout - logout
	checklogin - check login credentials and set cookie
	hash - hash a string with sha256
		string - string to hash
	help - get help
	update - update the wiki
		do - set to `true` to actually update the wiki
		secret - set to the value of the site's secret
	credits - view the credits
	delete - delete a page
		page - page name
		delete=yes - actually do the deletion (otherwise we display a prompt)
 *************/
?>
