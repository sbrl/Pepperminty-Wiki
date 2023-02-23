<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/**
* Rebuilds the page index based on what files are found
* @param	bool	$output	Whether to send progress information to the user's browser.
*/
function pageindex_rebuild(bool $output = true) : void {

	global $env, $pageindex;

	if($output && !is_cli()) {
		header("content-type: text/event-stream");
		ob_end_flush();
	}

	$glob_str = $env->storage_prefix . "*.md";
	$existingpages = glob_recursive($glob_str);
	$existingpages_count = count($existingpages);

	// Debug statements. Uncomment when debugging the pageindex regenerator.
	// var_dump($env->storage_prefix);
	// var_dump($glob_str);
	// var_dump($existingpages);

	// save our existing pageindex, if it is available at this point
	// we will use it to salvage some data out of it, like tags and authors
	if (is_a($pageindex, 'stdClass')) $old_pageindex = $pageindex;
	else $old_pageindex = new stdClass();


	// compose a new pageindex into a global variable
	$pageindex = new stdClass();
	// We use a for loop here because foreach doesn't loop over new values inserted
	// while we were looping
	for($i = 0; $i < $existingpages_count; $i++)
	{
		$pagefilename = $existingpages[$i];

		// Create a new entry for each md file we found
		$newentry = new stdClass();

		// glob_recursive() returns values like "./storage_prefix/folder/filename.md"
		// in the pageindex we save them as "folder/filename.md"
		$newentry->filename = normalize_filename($pagefilename);

		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified

		// Extract the name of the (sub)page without the ".md"
		$pagekey = filepath_to_pagename($newentry->filename);
		error_log("pagename '$newentry->filename' → filepath '$pagekey'");

		if(file_exists($env->storage_prefix . $pagekey) && // If it exists...
			!is_dir($env->storage_prefix . $pagekey)) // ...and isn't a directory
		{
			// This page (potentially) has an associated file!
			// Let's investigate.

			// Blindly add the file to the pageindex for now.
			// Future We might want to do a security check on the file later on.
			// File a bug if you think we should do this.
			$newentry->uploadedfile = true; // Yes this page does have an uploaded file associated with it
			$newentry->uploadedfilepath = $pagekey; // It's stored here

			// Work out what kind of file it really is
			$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
			$newentry->uploadedfilemime = finfo_file($mimechecker, $env->storage_prefix . $pagekey);
		}

		// Subpage parent checker
		if(strpos($pagekey, "/") !== false)
		{
			// We have a sub page people
			// Work out what our direct parent's key must be in order to check to
			// make sure that it actually exists. If it doesn't, then we need to
			// create it.
			$subpage_parent_key = substr($pagekey, 0, strrpos($pagekey, "/"));
			$subpage_parent_filename = "$env->storage_prefix$subpage_parent_key.md";
			if(array_search($subpage_parent_filename, $existingpages) === false)
			{
				// Our parent page doesn't actually exist - create it
				touch($subpage_parent_filename, 0);
				// Furthermore, we should add this page to the list of existing pages
				// in order for it to be indexed
				$existingpages[] = $subpage_parent_filename;
			}
		}

        // Attempt to salvage tags and lasteditor from the previous pageindex
        if (@$old_pageindex->$pagekey->tags)
		  $newentry->tags = $old_pageindex->$pagekey->tags;
		$newentry->lasteditor = "unknown";
		if (@$old_pageindex->$pagekey->lasteditor)
		  $newentry->lasteditor = $old_pageindex->$pagekey->lasteditor;


		// If the initial revision doesn't exist on disk, create it (if it does, then we handle that later)
		if(function_exists("history_add_revision") && !file_exists("{$pagefilename}.r0")) { // Can't use module_exists - too early
			copy($pagefilename, "{$pagefilename}.r0");
			$newentry->history = [ (object) [
				"type" => "edit",
				"rid" => 0,
				"timestamp" => $newentry->lastmodified,
				"filename" => normalize_filename("{$pagefilename}.r0"),
				"newsize" => $newentry->size,
				"sizediff" => $newentry->size,
				"editor" => $newentry->lasteditor
			] ];
		}

		// Store the new entry in the new page index
		$pageindex->$pagekey = $newentry;

		if($output) {
				$message = "[" . ($i + 1) . " / $existingpages_count] Added $pagefilename to the pageindex.";
				if(!is_cli()) $message = "data: $message\n\n";
				else $message = "$message\r";
				echo($message);
				flush();
			}
	}

	if(function_exists("history_add_revision")) {

		// collect from the filesystem what revision files we have
		$history_revs = glob_recursive($env->storage_prefix . "*.md.r*");

		// sort them in the ascending order of their revision numbers - it's very important for further processing
		usort($history_revs, function($a, $b) {
			preg_match("/[0-9]+$/", $a, $revid_a);
			$revid_a = intval($revid_a[0]);
			preg_match("/[0-9]+$/", $b, $revid_b);
			$revid_b = intval($revid_b[0]);
			return $revid_a - $revid_b;
		});


		foreach($history_revs as $filename) {
			preg_match("/[0-9]+$/", $filename, $revid);
			error_log("raw revid | ".var_export($revid, true));
			if(count($revid) === 0) continue;
			$revid = intval($revid[0]);

			$pagename = filepath_to_pagename($filename);
			$filepath_stripped = normalize_filename($filename);

			if(!isset($pageindex->$pagename->history))
				$pageindex->$pagename->history = [];

			if(isset($pageindex->$pagename->history[$revid]))
				continue;

			error_log("pagename: $pagename, revid: $revid, pageindex entry: ".var_export($pageindex->$pagename, true));
			$newsize = filesize($filename);
			$prevsize = 0;
			if($revid > 0 && isset($pageindex->$pagename->history[$revid - 1])) {
				$prevsize = filesize(end($pageindex->$pagename->history)->filename);
			}

			// Let's attempt to salvage the editor for this revision from the old pageindex
			// For that we walk through history of edits from old pageindex to find what editor was set for this specific file
			$revision_editor = "unknown";
			if ($old_pageindex->$pagename->history) {
				foreach ($old_pageindex->$pagename->history as $revision)
					if ($revision->filename == $filepath_stripped && isset($revision->editor))
						$revision_editor = $revision->editor;
			}

			// save the revision into history
			$pageindex->$pagename->history[$revid] = (object) [
				"type" => "edit",
				"rid" => $revid,
				"timestamp" => filemtime($filename),
				"filename" => $filepath_stripped,
				"newsize" => $newsize,
				"sizediff" => $newsize - $prevsize,
				"editor" => $revision_editor
			];
		}
	}

	save_pageindex();
	unset($existingpages);


	if($output && !is_cli()) {
		echo("data: Done! \n\n");
		flush();
	}


}

/*
 * Sort out the pageindex. Create it if it doesn't exist, and load + parse it
 * if it does.
 */
if(!file_exists($paths->pageindex))
{
	pageindex_rebuild(false);
}
else
{
	$pageindex_read_start = microtime(true);
	$pageindex = json_decode(file_get_contents($paths->pageindex));
	$env->perfdata->pageindex_decode_time = round((microtime(true) - $pageindex_read_start)*1000, 3);
	header("x-pageindex-decode-time: " . $env->perfdata->pageindex_decode_time . "ms");
	unset($pageindex_read_start);
}
