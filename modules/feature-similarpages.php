<?php
register_module([
	"name" => "Similar Pages",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a few suggestions of similar pages below the main content and above the comments of a page. Requires the search engine.",
	"id" => "feature-similarpages",
	"depends" => [ "lib-search-engine", "feature-search" ],
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=suggest-similar&page={pageName} Get similar page suggestions
		 * @apiName SuggestSimilar
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	page	The page to return suggestions for.
		 */
		
		/*
		 * ███████ ██    ██  ██████   ██████  ███████ ███████ ████████
		 * ██      ██    ██ ██       ██       ██      ██         ██
		 * ███████ ██    ██ ██   ███ ██   ███ █████   ███████    ██
		 *      ██ ██    ██ ██    ██ ██    ██ ██           ██    ██
		 * ███████  ██████   ██████   ██████  ███████ ███████    ██
		 * 
		 * ███████ ██ ███    ███ ██ ██       █████  ██████
		 * ██      ██ ████  ████ ██ ██      ██   ██ ██   ██
		 * ███████ ██ ██ ████ ██ ██ ██      ███████ ██████
		 *      ██ ██ ██  ██  ██ ██ ██      ██   ██ ██   ██
		 * ███████ ██ ██      ██ ██ ███████ ██   ██ ██   ██
		 */
		add_action("suggest-similar", function() {
			global $pageindex, $env;
			
			$format = $_GET["format"] ?? "text";
			
			
			// TODO: Supportr history revisions here? $env->page_filename might do this for us - we should check into the behaviour here
			$similarpages = similar_suggest(
				$env->page,
				file_get_contents($env->page_filename)
			);
			
			switch ($format) {
				case "text":
					header("content-type: text/plain");
					foreach($similarpages as $pagename => $rank) {
						echo("$pagename | $rank\n");
					}
					break;
				
				case "csv":
					header("content-type: text/csv");
					echo("pagename,rank\n");
					foreach($similarpages as $pagename => $rank)
						echo("$pagename,$rank\n");
					break;
				
				case "json":
					header("content-type: application/json");
					echo(json_encode($similarpages));
				
				default:
					http_response_code(400);
					header("content-type: text/plain");
					exit("Error: The format $format wasn't recognised.\nAvailable formats for this action: text, json, csv");
					break;
			}
		});
		
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $env;
			if($env->action !== "view")
				return;
			
			$html = "<aside class='similar-page-suggestions'><h2>Other pages to explore</h2>\n\t\t<ul class='similar-page-suggestions-list'>\n";
			$start_time = microtime(true);
			$suggestions = similar_suggest(
				$env->page,
				file_get_contents($env->page_filename)
			);
			$env->perfdata->similar_suggest = round((microtime(true) - $start_time) * 1000, 2);
			foreach($suggestions as $suggested_pagename => $rank)
				$html .= "<li data-rank='$rank'><a href='?page=".rawurlencode($suggested_pagename)."'>".htmlentities($suggested_pagename)."</a></li>\n";
			$html .= "</ul>\n\t\t<!-- Took {$env->perfdata->similar_suggest}ms to compute similar page suggestions -->\n\t\t</aside>\n";
			
			$parts["{extra}"] = $html . $parts["{extra}"];
		});
	}
]);

/**
 * Given a page name, returns a list fo similar pages.
 * @param	string	$pagename	The name of the page to return suggestions for.
 * @param	string	$content	The content of the given page.
 * @return	array	A list of suggested page names in the format pagename => rank.
 */
function similar_suggest(string $pagename, string $content, bool $limit_output = true) : array {
	global $settings;
	$content_search = search::$literator->transliterate($content);
	$index = search::index_generate($content_search);
	$title_tokens = search::tokenize($pagename);
	foreach($title_tokens as $token) {
		if(in_array($token, search::$stop_words)) continue;
		$index[$token] = [ "freq" => 10000, "fromtitle" => true ];
	}
	search::index_sort_freq($index, true);
	search::invindex_load();
	
	
	$our_pageid = ids::getid($pagename);
	$pages = [];
	$max_count = -1;
	$i = 0;
	foreach($index as $term => $data) {
		// Only search the top 20% most common words
		// Stop words are skipped automagically
		// if($i > $max_count * 0.2) break;
		// Skip words shorter than 3 characters
		if(strlen($term) < 3) continue;
		
		// if($i > 10) break;
		
		// If this one is less than 0.2x the max frequency count, break out
		if(!isset($data["fromtitle"]))
			$max_count = max($max_count, $data["freq"]);
		if($data["freq"] < $max_count * 0.2 || $data["freq"] <= 1) break;
		
		// Check is it's present just in case (todo figure out if it's necessary)
		if(!search::invindex_term_exists($term)) continue;
		
		$otherpages = search::invindex_term_getpageids($term);
		foreach($otherpages as $pageid) {
			if($pageid == $our_pageid) continue;
			if(!isset($pages[$pageid]))
				$pages[$pageid] = 0;
			
			$amount = search::invindex_term_getoffsets($term, $pageid)->freq;
			if(isset($data["fromtitle"]))
				$amount *= 5;
			$pages[$pageid] += $amount;
		}
		
		$i++;
	}
	
	arsort($pages, SORT_NUMERIC);
	
	$result = []; $i = 0;
	foreach($pages as $pageid => $count) {
		if($limit_output && $i >= $settings->similarpages_count) break;
		$result[ids::getpagename($pageid)] = $count;
		$i++;
	}
	return $result;
}

?>
