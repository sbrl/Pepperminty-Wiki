<?php
register_module([
	"name" => "Recent Changes",
	"version" => "0.3.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds recent changes. Access through the 'recent-changes' action.",
	"id" => "feature-recent-changes",
	"code" => function() {
		global $settings, $env, $paths;
		
		// Add the recent changes json file to $paths for convenience.
		$paths->recentchanges = $env->storage_prefix . "recent-changes.json";
		// Create the recent changes json file if it doesn't exist
		if(!file_exists($paths->recentchanges))
			file_put_contents($paths->recentchanges, "[]");
		
		/**
		 * @api {get} ?action=recent-changes[&offset={number}][&count={number}][&format={code}] Get a list of recent changes
		 * @apiName RecentChanges
		 * @apiGroup Stats
		 * @apiPermission Anonymous
		 *
		 * @apiParam	{number}	offset	If specified, start returning changes from this many changes in. 0 is the beginning.
		 * @apiParam	{number}	count	If specified, return at most this many changes. A value of 0 means no limit (the default) - apart from the limit on the number of changes stored by the server (configurable in pepppermint.json).
		 * @apiParam	{string}	format	The format to return the recent changes in. Valid values: html, json, csv, atom. Default: html.
		 */
		/*
		 * ██████  ███████  ██████ ███████ ███    ██ ████████         
		 * ██   ██ ██      ██      ██      ████   ██    ██            
		 * ██████  █████   ██      █████   ██ ██  ██    ██            
		 * ██   ██ ██      ██      ██      ██  ██ ██    ██            
		 * ██   ██ ███████  ██████ ███████ ██   ████    ██            
		 * 
		 *  ██████ ██   ██  █████  ███    ██  ██████  ███████ ███████ 
		 * ██      ██   ██ ██   ██ ████   ██ ██       ██      ██      
		 * ██      ███████ ███████ ██ ██  ██ ██   ███ █████   ███████ 
		 * ██      ██   ██ ██   ██ ██  ██ ██ ██    ██ ██           ██ 
		 *  ██████ ██   ██ ██   ██ ██   ████  ██████  ███████ ███████
		 */
		add_action("recent-changes", function() {
			global $settings, $paths, $pageindex;
			
			$format = $_GET["format"] ?? "html";
			$offset = intval($_GET["offset"] ?? 0);
			$count = intval($_GET["count"] ?? 0);
			
			$recent_changes = json_decode(file_get_contents($paths->recentchanges));
			
			// Limit the number of changes displayed if requested
			if($count > 0)
				$recent_changes = array_slice($recent_changes, $offset, $count);
			
			switch($format) {
				case "html":
					$content = "\t\t<h1>Recent Changes</h1>\n";
					
					if(count($recent_changes) > 0)
						$content .= render_recent_changes($recent_changes);
					else // No changes yet :(
						$content .= "<p><em>None yet! Try making a few changes and then check back here.</em></p>\n";
						
					page_renderer::add_header_html("\t<link rel=\"alternate\" type=\"application/atom+xml\" href=\"?action=recent-changes&amp;format=atom\" />
		<link rel=\"alternate\" type=\"text/csv\" href=\"?action=recent-changes&amp;format=csv\" />
		<link rel=\"alternate\" type=\"application/json\" href=\"?action=recent-changes&amp;format=json\" />");
					
					exit(page_renderer::render("Recent Changes - $settings->sitename", $content));
					break;
				case "json":
					$result = json_encode($recent_changes);
					header("content-type: application/json");
					header("content-length: " . strlen($result));
					exit($result);
					break;
				case "csv":
					if(empty($recent_changes)) {
						http_response_code(404);
						header("content-type: text/plain");
						exit("No changes made been recorded yet. Make some changes and then come back later!");
					}
					
					$result = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
					fputcsv($result, array_keys(get_object_vars($recent_changes[0])));
					foreach($recent_changes as $recent_change)
						fputcsv($result, array_values(get_object_vars($recent_change)));
					rewind($result);
					
					header("content-type: text/csv");
					header("content-length: " . fstat($result)["size"]);
					exit(stream_get_contents($result));
					break;
				case "atom":
					$result = render_recent_change_atom($recent_changes);
					header("content-type: application/atom+xml");
					header("content-length: " . strlen($result));
					exit($result);
				default:
					http_response_code(406);
					header("content-type: text/plain");
					header("content-length: 42");
					exit("Error: That format code wasnot recognised.");
			}
			
			
		});
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
			global $env, $settings, $paths;
			
			// Work out the old and new page lengths
			$oldsize = strlen($oldsource);
			$newsize = strlen($newsource);
			// Calculate the page length difference
			$size_diff = $newsize - $oldsize;
			
			$newchange = [
				"type" => "edit",
				"timestamp" => time(),
				"page" => $env->page,
				"user" => $env->user,
				"newsize" => $newsize,
				"sizediff" => $size_diff
			];
			if($oldsize == 0)
				$newchange["newpage"] = true;
			
			add_recent_change($newchange);
		});
		
		add_help_section("800-raw-page-content", "Recent Changes", "<p>The <a href='?action=recent-changes'>recent changes</a> page displays a list of all the most recent changes that have happened around $settings->sitename, arranged in chronological order. It can be found in the \"More...\" menu in the top right by default.</p>
		<p>Each entry displays the name of the page in question, who edited it, how long ago they did so, and the number of characters added or removed. Pages that <em>currently</em> redirect to another page are shown in italics, and hovering over the time since the edit wil show the exact time that the edit was made.</p>");
	}
]);

/**
 * Adds a new recent change to the recent changes file.
 * @package	feature-recent-changes
 * @param	array	$rchange	The new change to add.
 */
function add_recent_change($rchange)
{
	global $settings, $paths;
	
	$recentchanges = json_decode(file_get_contents($paths->recentchanges), true);
	array_unshift($recentchanges, $rchange);
	
	// Limit the number of entries in the recent changes file if we've
	// been asked to.
	if(isset($settings->max_recent_changes))
		$recentchanges = array_slice($recentchanges, 0, $settings->max_recent_changes);
	
	// Save the recent changes file back to disk
	file_put_contents($paths->recentchanges, json_encode($recentchanges, JSON_PRETTY_PRINT));
}

/**
 * Renders a list of recent changes to HTML.
 * @package	feature-recent-changes
 * @param	array	$recent_changes		The recent changes to render.
 * @return	string	The given recent changes as HTML.
 */
function render_recent_changes($recent_changes)
{
	global $pageindex;
	
	// Cache the number of recent changes we are dealing with
	$rchange_count = count($recent_changes);
	
	// Group changes made on the same page and the same day together
	for($i = 0; $i < $rchange_count; $i++)
	{
		for($s = $i + 1; $s < $rchange_count; $s++)
		{
			// Break out if we have reached the end of the day we are scanning
			if(date("dmY", $recent_changes[$i]->timestamp) !== date("dmY", $recent_changes[$s]->timestamp))
				break;
			
			// If we have found a change that has been made on the same page and
			// on the same day as the one that we are scanning for, move it up
			// next to the change we are scanning for.
			if($recent_changes[$i]->page == $recent_changes[$s]->page &&
				date("j", $recent_changes[$i]->timestamp) === date("j", $recent_changes[$s]->timestamp))
			{
				// FUTURE: We may need to remove and insert instead of swapping changes around if this causes some changes to appear out of order.
				$temp = $recent_changes[$i + 1];
				$recent_changes[$i + 1] = $recent_changes[$s];
				$recent_changes[$s] = $temp;
				$i++;
			}
		}
	}
	
	$content = "<ul class='page-list'>\n";
	$last_time = 0;
	for($i = 0; $i < $rchange_count; $i++)
	{
		$rchange = $recent_changes[$i];
		
		if($last_time !== date("dmY", $rchange->timestamp))
			$content .= "<li class='header'><h2>" . date("jS F", $rchange->timestamp) . "</h2></li>\n";
		
		$rchange_results = [];
		for($s = $i; $s < $rchange_count; $s++)
		{
			if($recent_changes[$s]->page !== $rchange->page)
				break;
			
			$rchange_results[$s] = render_recent_change($recent_changes[$s]);
			$i++;
		}
		// Take one from i to account for when we tick over to the next
		// iteration of the main loop
		$i -= 1;
		
		$next_entry = implode("\n", $rchange_results);
		// If the change count is greater than 1, then we should enclose it
		// in a <details /> tag.
		if(count($rchange_results) > 1)
		{
			reset($rchange_results);
			$rchange_first = $recent_changes[key($rchange_results)];
			end($rchange_results);
			$rchange_last = $recent_changes[key($rchange_results)];
			
			$pageDisplayHtml = render_pagename($rchange_first);
			$timeDisplayHtml = render_timestamp($rchange_first->timestamp);
			$users = [];
			foreach($rchange_results as $key => $rchange_result)
			{
				if(!in_array($recent_changes[$key]->user, $users))
					$users[] = $recent_changes[$key]->user; 
			}
			foreach($users as &$user)
				$user = page_renderer::render_username($user);
			$userDisplayHtml = render_editor(implode(", ", $users));
			
			$next_entry = "<li><details><summary><a href='?page=" . rawurlencode($rchange_first->page) . "'>$pageDisplayHtml</a> $userDisplayHtml $timeDisplayHtml</summary><ul class='page-list'>$next_entry</ul></details></li>";
			
			$content .= "$next_entry\n";
		}
		else
		{
			$content .= implode("\n", $rchange_results);
		}
		
		$last_time = date("dmY", $rchange->timestamp);
	}
	$content .= "\t\t</ul>";
	
	return $content;
}

/**
 * Given a page name and timestamp, returns the associated page revision number.
 * @param	string	$pagename	The page name to obtain the revision number for.
 * @param	int		$timestamap	The timestamp at which the revision was saved.
 * @return	int		The revision number of the given page at the given time.
 */
function find_revisionid_timestamp($pagename, $timestamp) {
	global $pageindex;
	
	if(!isset($pageindex->$pagename) || !isset($pageindex->$pagename->history))
		return null;
	
	foreach($pageindex->$pagename->history as $historyEntry){
		if($historyEntry->timestamp == $timestamp) {
			return $historyEntry->rid;
			break;
		}
	}
}

/**
 * Renders a single recent change
 * @package	feature-recent-changes
 * @param	object	$rchange	The recent change to render.
 * @return	string				The recent change, rendered to HTML.
 */
function render_recent_change($rchange)
{
	global $pageindex;
	$pageDisplayHtml = render_pagename($rchange);
	$editorDisplayHtml = render_editor(page_renderer::render_username($rchange->user));
	$timeDisplayHtml = render_timestamp($rchange->timestamp);
	
	$revisionId = find_revisionid_timestamp($rchange->page, $rchange->timestamp);
	
	$result = "";
	$resultClasses = [];
	$rchange_type = isset($rchange->type) ? $rchange->type : "edit";
	switch($rchange_type)
	{
		case "revert":
		case "edit":
			// The number (and the sign) of the size difference to display
			$size_display = ($rchange->sizediff > 0 ? "+" : "") . $rchange->sizediff;
			$size_display_class = $rchange->sizediff > 0 ? "larger" : ($rchange->sizediff < 0 ? "smaller" : "nochange");
			if($rchange->sizediff > 500 or $rchange->sizediff < -500)
				$size_display_class .= " significant";
			
			
			$size_title_display = human_filesize($rchange->newsize - $rchange->sizediff) . " -> " .  human_filesize($rchange->newsize);
			
			if(!empty($rchange->newpage))
				$resultClasses[] = "newpage";
			if($rchange_type === "revert")
				$resultClasses[] = "reversion";
			
			$result .= "<a href='?page=" . rawurlencode($rchange->page) . (!empty($revisionId) ? "&revision=$revisionId" : "") . "'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml <span class='$size_display_class' title='$size_title_display'>($size_display)</span>";
			break;
			
		case "deletion":
			$resultClasses[] = "deletion";
			$result .= "$pageDisplayHtml $editorDisplayHtml $timeDisplayHtml";
			break;
		
		case "move":
			$resultClasses[] = "move";
			$result .= "$rchange->oldpage &#11106; <a href='?page=" . rawurlencode($rchange->page) . "'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml";
			break;
		
		case "upload":
			$resultClasses[] = "upload";
			$result .= "<a href='?page=$rchange->page'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml (" . human_filesize($rchange->filesize) . ")";
			break;
		case "comment":
			$resultClasses[] = "new-comment";
			$result .= "<a href='?page=$rchange->page#comment-" . (!empty($rchange->comment_id) ? "$rchange->comment_id" : "unknown_comment_id") . "'>$pageDisplayHtml</a> $editorDisplayHtml";
	}
	
	$resultAttributes = " " . (count($resultClasses) > 0 ? "class='" . implode(" ", $resultClasses) . "'" : "");
	$result = "\t\t\t<li$resultAttributes>$result</li>\n";
	
	return $result;
}

/**
 * Renders a list of recent changes as an Atom 1.0 feed.
 * Requires the XMLWriter PHP class.
 * @param	array	$recent_changes		The array of recent changes to render.
 * @return	string	The recent changes as an Atom 1.0 feed.
 */
function render_recent_change_atom($recent_changes) {
	global $version, $settings;
	// See http://www.atomenabled.org/developers/syndication/#sampleFeed for easy-to-read Atom 1.0 docs
	
	$full_url_stem = full_url();
	$full_url_stem = substr($full_url_stem, 0, strpos($full_url_stem, "?"));
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(true); $xml->setIndentString("\t");
	$xml->startDocument("1.0", "utf-8");
	
	$xml->startElement("feed");
	$xml->writeAttribute("xmlns", "http://www.w3.org/2005/Atom");
	
	$xml->startElement("generator");
	$xml->writeAttribute("uri", "https://github.com/sbrl/Pepperminty-Wiki/");
	$xml->writeAttribute("version", $version);
	$xml->text("Pepperminty Wiki");
	$xml->endElement();
	
	$xml->startElement("link");
	$xml->writeAttribute("rel", "self");
	$xml->writeAttribute("type", "application/atom+xml");
	$xml->writeAttribute("href", full_url());
	$xml->endElement();
	
	$xml->startElement("link");
	$xml->writeAttribute("rel", "alternate");
	$xml->writeAttribute("type", "text/html");
	$xml->writeAttribute("href", "$full_url_stem?action=recent-changes&format=html");
	$xml->endElement();
	
	$xml->startElement("link");
	$xml->writeAttribute("rel", "alternate");
	$xml->writeAttribute("type", "application/json");
	$xml->writeAttribute("href", "$full_url_stem?action=recent-changes&format=json");
	$xml->endElement();
	
	$xml->startElement("link");
	$xml->writeAttribute("rel", "alternate");
	$xml->writeAttribute("type", "text/csv");
	$xml->writeAttribute("href", "$full_url_stem?action=recent-changes&format=csv");
	$xml->endElement();
	
	$xml->writeElement("updated", date(DateTime::ATOM));
	$xml->writeElement("id", full_url());
	$xml->writeElement("icon", $settings->favicon);
	$xml->writeElement("title", "$settings->sitename - Recent Changes");
	$xml->writeElement("subtitle", "Recent Changes on $settings->sitename");
	
	foreach($recent_changes as $recent_change) {
		if(empty($recent_change->type))
			$recent_change->type = "edit";
		
		$xml->startElement("entry");
		
		// Change types: revert, edit, deletion, move, upload, comment
		$type = $recent_change->type;
		$url = "$full_url_stem?page=".rawurlencode($recent_change->page);
		
		$content = "<ul>
	<li><strong>Change type:</strong> $recent_change->type</li>
	<li><strong>User:</strong>  $recent_change->user</li>
	<li><strong>Page name:</strong> $recent_change->page</li>
	<li><strong>Timestamp:</strong> ".date(DateTime::RFC1123, $recent_change->timestamp)."</li>\n";
		
		switch($type) {
			case "revert":
			case "edit":
				$type = ($type == "revert" ? "Reversion of" : "Edit to");
				$revision_id = find_revisionid_timestamp($recent_change->page, $recent_change->timestamp);
				if(!empty($revision_id))
					$url .= "&revision=$revision_id";
				$content .= "<li><strong>New page size:</strong> ".human_filesize($recent_change->newsize)."</li>
			<li><strong>Page size difference:</strong> ".($recent_change->sizediff > 0 ? "+" : "")."$recent_change->sizediff</li>\n";
				break;
			case "deletion": $type = "Deletion of"; break;
			case "move": $type = "Movement of"; break;
			case "upload":
				$type = "Upload of";
				$content .= "\t<li><strong>File size:</strong> ".human_filesize($recent_change->filesize)."</li>\n";
				break;
			case "comment":
				$type = "Comment on";
				$url .= "#comment-$recent_change->comment_id";
				break;
		}
		$content .= "</ul>";
		
		
		$xml->startElement("title");
		$xml->writeAttribute("type", "text");
		$xml->text("$type $recent_change->page by $recent_change->user");
		$xml->endElement();
		
		$xml->writeElement("id", $url);
		$xml->writeElement("updated", date(DateTime::ATOM, $recent_change->timestamp));
		
		$xml->startElement("content");
		$xml->writeAttribute("type", "html");
		$xml->text($content);
		$xml->endElement();
		
		$xml->startElement("link");
		$xml->writeAttribute("rel", "alternate");
		$xml->writeAttribute("type", "text/html");
		$xml->writeAttribute("href", $url);
		$xml->endElement();
		
		$xml->startElement("author");
		$xml->writeElement("name", $recent_change->user);
		$xml->writeElement("uri", "$full_url_stem?page=".rawurlencode("$settings->user_page_prefix/$recent_change->page"));
		$xml->endElement();
		
		$xml->endElement();
	}
	
	$xml->endElement();
	
	return $xml->flush();
}
