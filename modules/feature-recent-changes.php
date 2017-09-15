<?php
register_module([
	"name" => "Recent Changes",
	"version" => "0.3.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds recent changes. Access through the 'recent-changes' action.",
	"id" => "feature-recent-changes",
	"code" => function() {
		global $settings, $env, $paths;
		/**
		 * @api {get} ?action=recentchanges Get a list of recent changes
		 * @apiName RecentChanges
		 * @apiGroup Stats
		 * @apiPermission Anonymous
		 */
		
		// Add the recent changes json file to $paths for convenience.
		$paths->recentchanges = $env->storage_prefix . "recent-changes.json";
		// Create the recent changes json file if it doesn't exist
		if(!file_exists($paths->recentchanges))
			file_put_contents($paths->recentchanges, "[]");
		
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
			
			$content = "\t\t<h1>Recent Changes</h1>\n";
			
			$recent_changes = json_decode(file_get_contents($paths->recentchanges));
			
			if(count($recent_changes) > 0)
			{
				$content .= render_recent_changes($recent_changes);
			}
			else
			{
				// No changes yet :(
				$content .= "<p><em>None yet! Try making a few changes and then check back here.</em></p>\n";
			}
			
			exit(page_renderer::render("Recent Changes - $settings->sitename", $content));
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
	
	$revisionId = false;
	if(isset($pageindex->{$rchange->page}) && isset($pageindex->{$rchange->page}->history))
	{
		foreach($pageindex->{$rchange->page}->history as $historyEntry)
		{
			if($historyEntry->timestamp == $rchange->timestamp)
			{
				$revisionId = $historyEntry->rid;
				break;
			}
		}
	}
	
	$result = "";
	$resultClasses = [];
	switch(isset($rchange->type) ? $rchange->type : "edit")
	{
		case "edit":
			// The number (and the sign) of the size difference to display
			$size_display = ($rchange->sizediff > 0 ? "+" : "") . $rchange->sizediff;
			$size_display_class = $rchange->sizediff > 0 ? "larger" : ($rchange->sizediff < 0 ? "smaller" : "nochange");
			if($rchange->sizediff > 500 or $rchange->sizediff < -500)
				$size_display_class .= " significant";
			
			
			$size_title_display = human_filesize($rchange->newsize - $rchange->sizediff) . " -> " .  human_filesize($rchange->newsize);
			
			if(!empty($rchange->newpage))
				$resultClasses[] = "newpage";
			
			$result .= "<a href='?page=" . rawurlencode($rchange->page) . ($revisionId !== false ? "&revision=$revisionId" : "") . "'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml <span class='$size_display_class' title='$size_title_display'>($size_display)</span>";
			break;
		
		case "deletion":
			$resultClasses[] = "deletion";
			$result .= "$pageDisplayHtml $editorDisplayHtml $timeDisplayHtml";
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

?>
