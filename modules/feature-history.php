<?php
register_module([
	"name" => "Page History",
	"version" => "0.4.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to keep unlimited page history, limited only by your disk space. Note that this doesn't store file history (yet). Currently depends on feature-recent-changes for rendering of the history page.",
	"id" => "feature-history",
	"code" => function() {
		/**
		 * @api {get} ?action=history&page={pageName}[&format={format}] Get a list of revisions for a page
		 * @apiName History
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 * 
		 * @apiUse PageParameter
		 * @apiParam {string}	format	The format to return the list of pages in. available values: html, json, text. Default: html
		 */
		
		/*
		 * ██   ██ ██ ███████ ████████  ██████  ██████  ██    ██
		 * ██   ██ ██ ██         ██    ██    ██ ██   ██  ██  ██
		 * ███████ ██ ███████    ██    ██    ██ ██████    ████
		 * ██   ██ ██      ██    ██    ██    ██ ██   ██    ██
		 * ██   ██ ██ ███████    ██     ██████  ██   ██    ██
		 */
		add_action("history", function() {
			global $settings, $env, $pageindex;
			
			$supported_formats = [ "html", "json", "text" ];
			$format = $_GET["format"] ?? "html";
			
			switch($format) {
				case "html":
					$content = "<h1>History for $env->page</h1>\n";
					if(!empty($pageindex->{$env->page}->history))
					{
						$content .= "\t\t<ul class='page-list'>\n";
						foreach(array_reverse($pageindex->{$env->page}->history) as $revisionData)
						{
							// Only display edits & reverts for now
							if(!in_array($revisionData->type, [ "edit", "revert" ]))
								continue;
							
							// The number (and the sign) of the size difference to display
							$size_display = ($revisionData->sizediff > 0 ? "+" : "") . $revisionData->sizediff;
							$size_display_class = $revisionData->sizediff > 0 ? "larger" : ($revisionData->sizediff < 0 ? "smaller" : "nochange");
							if($revisionData->sizediff > 500 or $revisionData->sizediff < -500)
							$size_display_class .= " significant";
							$size_title_display = human_filesize($revisionData->newsize - $revisionData->sizediff) . " -> " .  human_filesize($revisionData->newsize);
							
							$content .= "\t\t\t<li>";
							$content .= "<a href='?page=" . rawurlencode($env->page) . "&revision=$revisionData->rid'>#$revisionData->rid</a> " . render_editor(page_renderer::render_username($revisionData->editor)) . " " . render_timestamp($revisionData->timestamp) . " <span class='cursor-query $size_display_class' title='$size_title_display'>($size_display)</span>";
							if($env->is_logged_in || ($settings->history_revert_require_moderator && $env->is_admin && $env->is_logged_in))
								$content .= " <small>(<a class='revert-button' href='?action=history-revert&page=" . rawurlencode($env->page) . "&revision=$revisionData->rid'>restore this revision</a>)</small>";
							$content .= "</li>\n";
						}
						$content .= "\t\t</ul>";
					}
					else
					{
						$content .= "<p style='text-align: center;'><em>(None yet! Try editing this page and then coming back here.)</em></p>\n";
					}
					exit(page_renderer::render_main("$env->page - History - $settings->sitename", $content));
				
				case "json":
					$page_history = $pageindex->{$env->page}->history ?? [];
					
					foreach($page_history as &$history_entry) {
						unset($history_entry->filename);
					}
					header("content-type: application/json");
					exit(json_encode($page_history, JSON_PRETTY_PRINT));
				
				case "csv":
					$page_history = $pageindex->{$env->page}->history ?? [];
					
					header("content-type: text/csv");
					echo("revision_id,timestamp,type,editor,newsize,sizediff\n");
					foreach($page_history as $hentry) {
						echo("$hentry->rid,$hentry->timestamp,$hentry->type,$hentry->editor,$hentry->newsize,$hentry->sizediff\n");
					}
					exit();
				
				default:
					http_response_code(400);
					exit(page_renderer::render_main("Format Error - $env->page - History - $settings->sitename", "<p>The format <code>" . htmlentities($format) . "</code> isn't currently supported. Supported formats: html, json, csv"));
			}
			
		});
		
		/**
		 * @api {get} ?action=history-revert&page={pageName}&revision={rid}	Revert a page to a previous version
		 * @apiName HistoryRevert
		 * @apiGroup Editing
		 * @apiPermission User
		 * @apiUse	PageParameter
		 * @apiUse	UserNotLoggedInError
		 * @apiUse	UserNotModeratorError
		 * 
		 * @apiParam {string}	revision	The page revision number to revert to.
		 */
		/*
		 * ██   ██ ██ ███████ ████████  ██████  ██████  ██    ██
		 * ██   ██ ██ ██         ██    ██    ██ ██   ██  ██  ██
		 * ███████ ██ ███████    ██    ██    ██ ██████    ████  █████
		 * ██   ██ ██      ██    ██    ██    ██ ██   ██    ██
		 * ██   ██ ██ ███████    ██     ██████  ██   ██    ██
		 * 
		 * ██████  ███████ ██    ██ ███████ ██████  ████████
		 * ██   ██ ██      ██    ██ ██      ██   ██    ██
		 * ██████  █████   ██    ██ █████   ██████     ██
		 * ██   ██ ██       ██  ██  ██      ██   ██    ██
		 * ██   ██ ███████   ████   ███████ ██   ██    ██
		 */
		add_action("history-revert", function() {
			global $env, $settings, $pageindex;
			
			if((!$env->is_admin && $settings->history_revert_require_moderator) ||
				!$env->is_logged_in) {
				http_response_code(401);
				exit(page_renderer::render_main("Unauthorised - $settings->sitename", "<p>You can't revert pages to a previous revision because " . ($settings->history_revert_require_moderator && $env->is_logged_in ? "you aren't logged in as a moderator. You can try <a href='?action=logout'>logging out</a> and then" : "you aren't logged in. You can try") . " <a href='?action=login&returnto=" . rawurlencode("?action=history-revert&revision={$env->history->revision_number}&page=" . rawurlencode($env->page)) . "'>logging in</a>.</p>"));
			}
			
			$current_revision_filepath = "$env->storage_prefix/{$pageindex->{$env->page}->filename}";
			
			// Figure out what we're saving
			$newsource = file_get_contents($env->page_filename); // The old revision content - the Pepperminty Wiki core sorts this out for us
			$oldsource = file_get_contents($current_revision_filepath); // The current revision's content
			
			// Save the old content over the current content
			file_put_contents($current_revision_filepath, $newsource);
			
			// NOTE: We don't run the save preprocessors here because they are run when a page is edited - reversion is special and requires different treatment.
			// FUTURE: We may want ot refactor the save preprocessor system ot take a single object instead - then we can add as many params as we like and we could execute the save preprocessors as normal :P
			
			// Add the old content as a new revision
			$result = history_add_revision(
				$pageindex->{$env->page},
				$newsource,
				$oldsource,
				true, // Yep, go ahead and save the page index
				"revert" // It's a revert, not an edit
			);
			
			// Update the redirect metadata, if the redirect module is installed
			if(module_exists("feature-redirect"))
				update_redirect_metadata($pageindex->{$env->page}, $newsource);
			
			// Add an entry to the recent changes log, if the module exists
			if($result !== false && module_exists("feature-recent-changes"))
				add_recent_change([
					"type" => "revert",
					"timestamp" => time(),
					"page" => $env->page,
					"user" => $env->user,
					"newsize" => strlen($newsource),
					"sizediff" => strlen($newsource) - strlen($oldsource)
				]);
			
			if($result === false) {
				http_response_code(503);
				exit(page_renderer::render_main("Server Error - Revert - $settings->sitename", "<p>A server error occurred when $settings->sitename tried to save the reversion of <code>" . htmlentities($env->page) . "</code>. Please contact $settings->sitename's administrator $settings->admindetails_name, whose email address can be found at the bottom of every page (including this one).</p>"));
			}
			
			http_response_code(201);
			exit(page_renderer::render_main("Reverting " . htmlentities($env->page) . " - $settings->sitename", "<p>" . htmlentities($env->page) . " has been reverted back to revision {$env->history->revision_number} successfully.</p>
			<p><a href='?page=" . rawurlencode($env->page) . "'>Go back</a> to the page, or continue <a href='?action=history&page = " . rawurlencode($env->page) . "'>reviewing its history</a>.</p>"));
			
			// $env->page_filename
			// 
		});
		
		register_save_preprocessor("history_add_revision");
		
		if(module_exists("feature-stats")) {
			statistic_add([
				"id" => "history_most_revisions",
				"name" => "Most revised page",
				"type" => "scalar",
				"update" => function($old_stats) {
					global $pageindex;
					
					$target_pagename = "";
					$target_revisions = -1;
					foreach($pageindex as $pagename => $pagedata) {
						if(!isset($pagedata->history))
							continue;
						
						$revisions_count = count($pagedata->history);
						if($revisions_count > $target_revisions) {
							$target_revisions = $revisions_count;
							$target_pagename = $pagename;
						}
					}
					
					$result = new stdClass(); // completed, value, state
					$result->completed = true;
					$result->value = "(no revisions saved yet)";
					if($target_revisions > -1) {
						$result->value = "$target_revisions (<a href='?page=" . rawurlencode($target_pagename) . "'>" . htmlentities($target_pagename) . "</a>)";
					}
					
					return $result;
				}
			]);
		}
	}
]);

/**
 * Adds a history revision against a page.
 * Note: Does not update the current page content! This function _only_ 
 * records a new revision against a page name. Thus it is possible to have a 
 * disparaty between the history revisions and the actual content displayed in 
 * the current revision if you're not careful!
 * @package	feature-history
 * @param	object	$pageinfo		The pageindex object of the page to operate on.
 * @param	string	$newsource		The page content to save as the new revision.
 * @param	string	$oldsource		The old page content that is the current revision (before the update).
 * @param	bool	$save_pageindex	Whether the page index should be saved to disk.
 * @param	string	$change_type	The type of change to record this as in the history revision log
 */
function history_add_revision(&$pageinfo, &$newsource, &$oldsource, $save_pageindex = true, $change_type = "edit") {
	global $env, $paths, $settings, $pageindex;
	
	if(!isset($pageinfo->history))
		$pageinfo->history = [];
	
	// Save the *new source* as a revision
	// This results in 2 copies of the current source, but this is ok
	// since any time someone changes something, it creates a new revision
	// Note that we can't save the old source here because we'd have no
	// clue who edited it since $pageinfo has already been updated by
	// this point
	
	// TODO Store tag changes here
	// Calculate the next revision id - we can't just count the revisions here because we might have a revision limit
	$nextRid = !empty($pageinfo->history) ? end($pageinfo->history)->rid + 1 : 0;
	$ridFilename = "$pageinfo->filename.r$nextRid";
	// Insert a new entry into the history
	$pageinfo->history[] = [
		"type" => $change_type, // We might want to store other types later (e.g. page moves)
		"rid" => $nextRid,
		"timestamp" => time(),
		"filename" => $ridFilename,
		"newsize" => strlen($newsource),
		"sizediff" => strlen($newsource) - strlen($oldsource),
		"editor" => $pageinfo->lasteditor
	];
	
	// Save the new source as a revision
	$result = file_put_contents("$env->storage_prefix$ridFilename", $newsource);
	
	if($result !== false &&
		$settings->history_max_revisions > -1) {
		while(count($pageinfo->history) > $settings->history_max_revisions) {
			// We've got too many revisions - trim one off & delete it
			$oldest_revision = array_shift($pageinfo->history);
			unlink("$env->storage_prefix/$oldest_revision->filename");
		}
	}
	
	// Save the edited pageindex
	if($result !== false && $save_pageindex)
		$result = save_pageindex();
	
	
	return $result;
}

?>
