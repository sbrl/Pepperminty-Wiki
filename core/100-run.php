<?php


// Execute each module's code
foreach($modules as $moduledata) {
	$moduledata["code"]();
}
// Make sure that the credits page exists
if(!isset($actions->credits))
{
	exit(page_renderer::render_main("Error - $settings->$sitename", "<p>No credits page detected. The credits page is a required module!</p>"));
}

// Download all the requested remote files
ini_set("user_agent", "$settings->sitename (Pepperminty-Wiki-Downloader; PHP/" . phpversion() . "; +https://github.com/sbrl/Pepperminty-Wiki/) Pepperminty-Wiki/$version");
foreach($remote_files as $remote_file_def) {
	if(file_exists($remote_file_def["local_filename"]) && filesize($remote_file_def["local_filename"]) > 0)
		continue;
	
	error_log("[ Pepperminty-Wiki/$settings->sitename ] Downloading {$remote_file_def["local_filename"]} from {$remote_file_def["remote_url"]}");
	file_put_contents($remote_file_def["local_filename"], fopen($remote_file_def["remote_url"], "rb"));
}

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
