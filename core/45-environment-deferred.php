<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


/// Finish setting up the environment object ///
$env->page = $_GET["page"] ?? $_POST["page"];
if(isset($_GET["revision"]) and is_numeric($_GET["revision"]))
{
	// We have a revision number!
	$env->is_history_revision = true;
	$env->history->revision_number = intval($_GET["revision"]);
	
	// Make sure that the revision exists for later on
	if(!isset($pageindex->{$env->page}->history[$env->history->revision_number]))
	{
		http_response_code(404);
		exit(page_renderer::render_main("404: Revision Not Found - $env->page - $settings->sitename", "<p>Revision #{$env->history->revision_number} of $env->page doesn't appear to exist. Try viewing the <a href='?action=history&page=" . rawurlencode($env->page) . "'>list of revisions for $env->page</a>, or viewing <a href='?page=" . rawurlencode($env->page) . "'>the latest revision</a> instead.</p>"));
	}
	
	$env->history->revision_data = $pageindex->{$env->page}->history[$env->history->revision_number];
}
// Construct the page's filename
$env->page_filename = $env->storage_prefix;
if($env->is_history_revision)
	$env->page_filename .= $pageindex->{$env->page}->history[$env->history->revision_number]->filename;
else if(isset($pageindex->{$env->page}))
	$env->page_filename .= $pageindex->{$env->page}->filename;

$env->action = slugify($_GET["action"]);
