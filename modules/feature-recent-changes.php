<?php
register_module([
	"name" => "Recent Changes",
	"version" => "0.1",
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
			global $settings, $paths;
			
			$content = "\t\t<h1>Recent Changes</h1>
		<ul>\n";
			
			$recentchanges = json_decode(file_get_contents($paths->recentchanges));
			foreach($recentchanges as $rchange)
			{
				// The number (and the sign) of the size difference to display
				$size_display = ($rchange->sizediff > 0 ? "+" : ($rchange < 0 ? "-" : "")) . $rchange->sizediff;
				$size_display_class = $rchange->sizediff > 0 ? "larger" : ($rchange < 0 ? "smaller" : "nochange");
				$content .= "\t\t\t<li><span class='editor'>&#9998; $rchange->page $rchange->user</span> " . human_time_since($rchange->timestamp) . " <span class='$size_display_class' title='New size: $rchange->newsize'>($size_display)</span></li>\n";
			}
			
			$content .= "\t\t</ul>";
			
			echo(page_renderer::render("Recent Changes - $settings->sitename", $content));
		});
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
			global $env, $settings, $paths;
			
			// Work out the old and new page lengths
			$oldsize = strlen($oldsource);
			$newsize = strlen($newsource);
			// Calculate the page length difference
			$size_diff = $newsize - $oldsize;
			
			error_log("$oldsize -> $newsize");
			error_log("Size diff: $size_diff");
			
			$recentchanges = json_decode(file_get_contents($paths->recentchanges), true);
			$recentchanges[] = [
				"timestamp" => time(),
				"page" => $env->page,
				"user" => $env->user,
				"newsize" => $newsize,
				"sizediff" => $size_diff
			];
			
			// Limit the number of entries in the recent changes file if we've
			// been asked to.
			if(isset($settings->max_recent_changes))
				$recentchanges = array_slice($recentchanges, -$settings->max_recent_changes);
			
			// Save the recent changes file back to disk
			file_put_contents($paths->recentchanges, json_encode($recentchanges, JSON_PRETTY_PRINT));
		});
		
		add_help_section("800-raw-page-content", "Recent Changes", "<p></p>");
	}
]);

?>
