<?php

/*
 * Sort out the pageindex. Create it if it doesn't exist, and load + parse it
 * if it does.
 */
if(!file_exists($paths->pageindex))
{
	$glob_str = $env->storage_prefix . "*.md";
	$existingpages = glob_recursive($glob_str);
	$existingpages_count = count($existingpages);
	// Debug statements. Uncomment when debugging the pageindex regenerator.
	// var_dump($env->storage_prefix);
	// var_dump($glob_str);
	// var_dump($existingpages);
	$pageindex = new stdClass();
	// We use a for loop here because foreach doesn't loop over new values inserted
	// while we were looping
	for($i = 0; $i < $existingpages_count; $i++)
	{
		$pagefilename = $existingpages[$i];
		
		// Create a new entry
		$newentry = new stdClass();
		$newentry->filename = mb_substr( // Store the filename, whilst trimming the storage prefix
			$pagefilename,
			mb_strlen(preg_replace("/^\.\//iu", "", $env->storage_prefix)) // glob_recursive trim the ./ from returned filenames , so we need to as well
		);
		// Remove the `./` from the beginning if it's still hanging around
		if(mb_substr($newentry->filename, 0, 2) == "./")
			$newentry->filename = mb_substr($newentry->filename, 2);
		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified
		// Todo find a way to keep the last editor independent of the page index
		$newentry->lasteditor = "unknown"; // Set the editor to "unknown"
		
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
		
		// Debug statements. Uncomment when debugging the pageindex regenerator.
		// echo("pagekey: ");
		// var_dump($pagekey);
		// echo("newentry: ");
		// var_dump($newentry);
		
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
		
		// If the initial revision doesn't exist on disk, create it (if it does, then we handle that later)
		if(function_exists("history_add_revision") && !file_exists("{$pagefilename}.r0")) { // Can't use module_exists - too early
			copy($pagefilename, "{$pagefilename}.r0");
			$newentry->history = [ (object) [
				"type" => "edit",
				"rid" => 0,
				"timestamp" => $newentry->lastmodified,
				"filename" => "{$pagefilename}.r0",
				"newsize" => $newentry->size,
				"sizediff" => $newentry->size,
				"editor" => "unknown"
			] ];
		}

		// Store the new entry in the new page index
		$pageindex->$pagekey = $newentry;
	}
	
	if(function_exists("history_add_revision")) {
		$history_revs = glob_recursive($env->storage_prefix . "*.r*");
		// It's very important that we read the history revisions in the right order and that we don't skip any
		usort($history_revs, function($a, $b) {
			preg_match("/[0-9]+$/", $a, $revid_a);
			$revid_a = intval($revid_a[0]);
			preg_match("/[0-9]+$/", $b, $revid_b);
			$revid_b = intval($revid_b[0]);
			return $revid_a - $revid_b;
		});
		// We can guarantee that the direcotry separator is present on the end - it's added explicitly earlier
		$strlen_storageprefix = strlen($env->storage_prefix);
		foreach($history_revs as $filename) {
			preg_match("/[0-9]+$/", $filename, $revid);
			error_log("raw revid | ".var_export($revid, true));
			if(count($revid) === 0) continue;
			$revid = intval($revid[0]);
			
			$pagename = filepath_to_pagename($filename);
			$filepath_stripped = substr($filename, $strlen_storageprefix);
			
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
			$pageindex->$pagename->history[$revid] = (object) [
				"type" => "edit",
				"rid" => $revid,
				"timestamp" => filemtime($filename),
				"filename" => $filepath_stripped,
				"newsize" => $newsize,
				"sizediff" => $newsize - $prevsize,
				"editor" => "unknown"
			];
		}
	}
	
	save_pageindex();
	unset($existingpages);
}
else
{
	$pageindex_read_start = microtime(true);
	$pageindex = json_decode(file_get_contents($paths->pageindex));
	$env->perfdata->pageindex_decode_time = round((microtime(true) - $pageindex_read_start)*1000, 3);
	header("x-pageindex-decode-time: " . $env->perfdata->pageindex_decode_time . "ms");
	unset($pageindex_read_start);
}
