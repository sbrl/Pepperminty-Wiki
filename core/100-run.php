<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */



// Execute each module's code
foreach($modules as $moduledata) {
	$moduledata["code"]();
}
// Make sure that the credits page exists
if(!isset($actions->credits))
{
	exit(page_renderer::render_main("Error - $settings->$sitename", "<p>No credits page detected. The credits page is a required module!</p>"));
}

// If we're on the CLI, then start it
if(!defined("PEPPERMINTY_WIKI_BUILD") &&
	module_exists("feature-cli") &&
	$settings->cli_enabled &&
	php_sapi_name() == "cli")
	cli();

//////////////////////////////////
/// Final Consistency Measures ///
//////////////////////////////////

if(!isset($pageindex->{$env->page}) && isset($pageindex->{ucwords($env->page)})) {
	http_response_code(307);
	header("location: ?page=" . ucwords($env->page));
	header("content-type: text/plain");
	exit("$env->page doesn't exist on $settings->sitename, but " . ucwords($env->page) . " does. You should be redirected there automatically.");
}

// Redirect to the search page if there isn't a page with the requested name
if(!isset($pageindex->{$env->page}) and isset($_GET["search-redirect"]))
{
	http_response_code(307);
	$url = "?action=search&query=" . rawurlencode($env->page);
	header("location: $url");
	exit(page_renderer::render_minimal("Non existent page - $settings->sitename", "<p>There isn't a page on $settings->sitename with that name. However, you could <a href='$url'>search for this page name</a> in other pages.</p>
		<p>Alternatively, you could <a href='?action=edit&page=" . rawurlencode($env->page) . "&create=true'>create this page</a>.</p>"));
}

//////////////////////////////////


// Perform the appropriate action
$action_name = $env->action;
if(isset($actions->$action_name)) {
	$req_action_data = $actions->$action_name;
	$req_action_data();
}
else {
	exit(page_renderer::render_main("Error - $settings->sitename", "<p>No action called " . strtolower($_GET["action"]) ." has been registered. Perhaps you are missing a module?</p>"));
}
