<?php
$pageindex = json_decode(file_get_contents("./pageindex.json"));

function check_subpage_parents($pagename)
{
	global $pageindex;
	echo("pagename: $pagename\n");
	// Save the new pageindex and return if there aren't any more parent pages to check
	if(strpos($pagename, "/") === false)
	{
		file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
		return;
	}
	
	$parent_pagename = substr($pagename, 0, strrpos($pagename, "/"));
	$parent_page_filename = "$parent_pagename.md";
	echo("parent page name: $parent_pagename, filename: $parent_page_filename\n");
	if(!file_exists($parent_page_filename))
	{
		// This parent page doesn't exist! Create it and add it to the page index.
		touch($parent_page_filename, 0);
		
		$newentry = new stdClass();
		$newentry->filename = $parent_page_filename;
		$newentry->size = 0;
		$newentry->lastmodified = 0;
		$newentry->lasteditor = "none";
		
	}
	
	check_subpage_parents($parent_pagename);
}

check_subpage_parents("New Test\/New");