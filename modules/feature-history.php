<?php
register_module([
	"name" => "Page History",
	"version" => "0.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to keep unlimited page history, limited only by your disk space. Note that this doesn't store file history (yet). Currently depends on feature-recent-changes for rendering of the history page.",
	"id" => "feature-history",
	"code" => function() {
		/**
		 * @api {get} ?action=history&page={pageName} Get a list of revisions for a page
		 * @apiName History
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	page	The page name to return a revision list for.
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
			
			
			$content = "<h1>History for $env->page</h1>\n";
			if(!empty($pageindex->{$env->page}->history))
			{
				$content .= "\t\t<ul class='page-list'>\n";
				foreach(array_reverse($pageindex->{$env->page}->history) as $revisionData)
				{
					// Only display edits for now
					if($revisionData->type != "edit")
					continue;
					
					// The number (and the sign) of the size difference to display
					$size_display = ($revisionData->sizediff > 0 ? "+" : "") . $revisionData->sizediff;
					$size_display_class = $revisionData->sizediff > 0 ? "larger" : ($revisionData->sizediff < 0 ? "smaller" : "nochange");
					if($revisionData->sizediff > 500 or $revisionData->sizediff < -500)
					$size_display_class .= " significant";
					$size_title_display = human_filesize($revisionData->newsize - $revisionData->sizediff) . " -> " .  human_filesize($revisionData->newsize);
					
					$content .= "<li><a href='?page=" . rawurlencode($env->page) . "&revision=$revisionData->rid'>#$revisionData->rid</a> " . render_editor($revisionData->editor) . " " . render_timestamp($revisionData->timestamp) . " <span class='cursor-query $size_display_class' title='$size_title_display'>($size_display)</span>";
				}
			}
			else
			{
				$content .= "<p style='text-align: center;'><em>(None yet! Try editing this page and then coming back here.)</em></p>\n";
			}
			exit(page_renderer::render_main("$env->page - History - $settings->sitename", $content));
		});
		
		
		register_save_preprocessor("history_add_revision");
	}
]);

function history_add_revision(&$pageinfo, &$newsource, &$oldsource, $save_pageindex = true) {
	global $pageindex, $paths, $env;
	
	if(!isset($pageinfo->history))
		$pageinfo->history = [];
	
	// Save the *new source* as a revision
	// This results in 2 copies of the current source, but this is ok
	// since any time someone changes something, it create a new
	// revision
	// Note that we can't save the old source here because we'd have no
	// clue who edited it since $pageinfo has already been updated by
	// this point
	
	// TODO Store tag changes here
	$nextRid = count($pageinfo->history); // The next revision id
	$ridFilename = "$pageinfo->filename.r$nextRid";
	// Insert a new entry into the history
	$pageinfo->history[] = [
		"type" => "edit", // We might want to store other types later (e.g. page moves)
		"rid" => $nextRid,
		"timestamp" => time(),
		"filename" => $ridFilename,
		"newsize" => strlen($newsource),
		"sizediff" => strlen($newsource) - strlen($oldsource),
		"editor" => $pageinfo->lasteditor
	];
	
	// Save the new source as a revision
	file_put_contents("$env->storage_prefix$ridFilename", $newsource);
	
	// Save the edited pageindex
	if($save_pageindex)
		file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
}

?>
