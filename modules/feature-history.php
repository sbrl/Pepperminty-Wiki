<?php
register_module([
	"name" => "Page History",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to keep unlimited page history, limited only by your disk space. Note that this doesn't store file history (yet).",
	"id" => "feature-history",
	"code" => function() {
		
		/*
		 * ██   ██ ██ ███████ ████████  ██████  ██████  ██    ██
		 * ██   ██ ██ ██         ██    ██    ██ ██   ██  ██  ██
		 * ███████ ██ ███████    ██    ██    ██ ██████    ████
		 * ██   ██ ██      ██    ██    ██    ██ ██   ██    ██
		 * ██   ██ ██ ███████    ██     ██████  ██   ██    ██
		 */
		add_action("history", function() {
			global $settings;
			
			http_response_code(501);
			exit(page_renderer::render_main("Coming soon", "<p>Page history is coming soon!</p>"));
		});
		
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
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
				"bytechange" => strlen($newsource) - strlen($oldsource),
				"editor" => $pageinfo->lasteditor
			];
			
			// Save the new source as a revision
			file_put_contents("$env->storage_prefix$ridFilename", $newsource);
			
			// Save the edited pageindex
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
		});
	}
]);

?>
