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
			
			$content = "\t\t<h1>Recent Changes</h1>\n";
			
			$recentchanges = json_decode(file_get_contents($paths->recentchanges));
			
			if(count($recentchanges) > 0)
			{
				$content .= "<ul class='page-list'>\n";
				foreach($recentchanges as $rchange)
				{
					// The number (and the sign) of the size difference to display
					$size_display = ($rchange->sizediff > 0 ? "+" : "") . $rchange->sizediff;
					$size_display_class = $rchange->sizediff > 0 ? "larger" : ($rchange->sizediff < 0 ? "smaller" : "nochange");
					if($rchange->sizediff > 500 or $rchange->sizediff < -500)
					$size_display_class .= " significant";
					
					
					$title_display = human_filesize($rchange->newsize - $rchange->sizediff) . " -> " .  human_filesize($rchange->newsize);
					
					$content .= "\t\t\t<li><a href='?page=" . rawurlencode($rchange->page) . "'>$rchange->page</a> <span class='editor'>&#9998; $rchange->user</span> " . human_time_since($rchange->timestamp) . " <span class='$size_display_class' title='$title_display'>($size_display)</span></li>\n";
				}
				$content .= "\t\t</ul>";
			}
			else
			{
				// No changes yet :(
				$content .= "<p><em>None yet! Try making a few changes and then check back here.</em></p>\n";
			}
			
			echo(page_renderer::render("Recent Changes - $settings->sitename", $content));
		});
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
			global $env, $settings, $paths;
			
			// Work out the old and new page lengths
			$oldsize = strlen($oldsource);
			$newsize = strlen($newsource);
			// Calculate the page length difference
			$size_diff = $newsize - $oldsize;
			
			$recentchanges = json_decode(file_get_contents($paths->recentchanges), true);
			array_unshift($recentchanges, [
				"timestamp" => time(),
				"page" => $env->page,
				"user" => $env->user,
				"newsize" => $newsize,
				"sizediff" => $size_diff
			]);
			
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
