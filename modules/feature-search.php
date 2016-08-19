<?php
register_module([
	"name" => "Search",
	"version" => "0.2.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki using an inverted index to provide a full text search engine. If pages don't show up, then you might have hit a stop word. If not, try requesting the `invindex-rebuild` action to rebuild the inverted index from scratch.",
	"id" => "feature-search",
	"code" => function() {
		/**
		 * @api {get} ?action=index&page={pageName} Get an index of words for a given page
		 * @apiName SearchIndex
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	page	The page to generate a word index page.
		 */
		
		/*
		 * ██ ███    ██ ██████  ███████ ██   ██ 
		 * ██ ████   ██ ██   ██ ██       ██ ██  
		 * ██ ██ ██  ██ ██   ██ █████     ███   
		 * ██ ██  ██ ██ ██   ██ ██       ██ ██  
		 * ██ ██   ████ ██████  ███████ ██   ██ 
		 */
		add_action("index", function() {
			global $settings, $env;
			
			$breakable_chars = "\r\n\t .,\\/!\"£$%^&*[]()+`_~#";
			
			header("content-type: text/plain");
			
			$source = file_get_contents("$env->storage_prefix$env->page.md");
			
			$index = search::index($source);
			
			var_dump($env->page);
			var_dump($source);
			
			var_dump($index);
		});
		
		/**
		 * @api {get} ?action=invindex-rebuild Rebuild the inverted search index from scratch
		 * @apiDescription	Causes the inverted search index to be completely rebuilt from scratch. Can take a while for large wikis!
		 * @apiName			SearchInvindexRebuild
		 * @apiGroup		Search
		 * @apiPermission Anonymous
		 */
		
		/*
		 * ██ ███    ██ ██    ██ ██ ███    ██ ██████  ███████ ██   ██          
		 * ██ ████   ██ ██    ██ ██ ████   ██ ██   ██ ██       ██ ██           
		 * ██ ██ ██  ██ ██    ██ ██ ██ ██  ██ ██   ██ █████     ███  █████     
		 * ██ ██  ██ ██  ██  ██  ██ ██  ██ ██ ██   ██ ██       ██ ██           
		 * ██ ██   ████   ████   ██ ██   ████ ██████  ███████ ██   ██          
		 * 
		 * ██████  ███████ ██████  ██    ██ ██ ██      ██████                  
		 * ██   ██ ██      ██   ██ ██    ██ ██ ██      ██   ██                 
		 * ██████  █████   ██████  ██    ██ ██ ██      ██   ██                 
		 * ██   ██ ██      ██   ██ ██    ██ ██ ██      ██   ██                 
		 * ██   ██ ███████ ██████   ██████  ██ ███████ ██████                  
		 */
		add_action("invindex-rebuild", function() {
			search::rebuild_invindex();
		});
		
		/**
		 * @api {get} ?action=search&query={text}	Search the wiki for a given query string
		 * @apiName Search
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	query	The query string to search for.
		 */
		
		/*
		 * ███████ ███████  █████  ██████   ██████ ██   ██ 
		 * ██      ██      ██   ██ ██   ██ ██      ██   ██ 
		 * ███████ █████   ███████ ██████  ██      ███████ 
		 *      ██ ██      ██   ██ ██   ██ ██      ██   ██ 
		 * ███████ ███████ ██   ██ ██   ██  ██████ ██   ██ 
		 */
		add_action("search", function() {
			global $settings, $env, $pageindex, $paths;
			
			// Create the inverted index if it doesn't exist.
			// todo In the future perhaps a CLI for this would be good?
			if(!file_exists($paths->searchindex))
				search::rebuild_invindex();
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			$invindex = search::load_invindex($paths->searchindex);
			$results = search::query_invindex($_GET["query"], $invindex);

			$search_end = microtime(true) - $search_start;

			$title = $_GET["query"] . " - Search results - $settings->sitename";
			
			$content = "<section>\n";
			$content .= "<h1>Search Results</h1>";
			
			/// Search Box ///
			$content .= "<form method='get' action=''>\n";
			$content .= "	<input type='search' id='search-box' name='query' placeholder='Type your query here and then press enter.' value='" . $_GET["query"] . "' />\n";
			$content .= "	<input type='hidden' name='action' value='search' />\n";
			$content .= "</form>";
			
			$query = $_GET["query"];
			if(isset($pageindex->$query))
			{
				$content .= "<p>There's a page on $settings->sitename called <a href='?page=" . rawurlencode($query) . "'>$query</a>.</p>";
			}
			else
			{
				$content .= "<p>There isn't a page called $query on $settings->sitename, but you ";
				if((!$settings->anonedits && !$env->is_logged_in) || !$settings->editing)
				{
					$content .= "do not have permission to create it.";
					if(!$env->is_logged_in)
					{
						$content .= " You could try <a href='?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.";
					}
				}
				else
				{
					$content .= "can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.</p>";
				}
			}
			
			$i = 0; // todo use $_GET["offset"] and $_GET["result-count"] or something
			foreach($results as $result)
			{
				$link = "?page=" . rawurlencode($result["pagename"]);
				$pagesource = file_get_contents($env->storage_prefix . $result["pagename"] . ".md");
				$context = search::extract_context($_GET["query"], $pagesource);
				//echo("Generated search context for " . $result["pagename"] . ": $context\n");
				$context = search::highlight_context($_GET["query"], $context);
				/*if(strlen($context) == 0)
				{
					$context = search::strip_markup(file_get_contents("$env->page.md", null, null, null, $settings->search_characters_context * 2));
					if($pageindex->{$env->page}->size > $settings->search_characters_context * 2)
						$context .= "...";
				}*/
				
				
				// We add 1 to $i here to convert it from an index to a result
				// number as people expect it to start from 1
				$content .= "<div class='search-result' data-result-number='" . ($i + 1) . "' data-rank='" . $result["rank"] . "'>\n";
				$content .= "	<h2><a href='$link'>" . $result["pagename"] . "</a></h2>\n";
				$content .= "	<p>$context</p>\n";
				$content .= "</div>\n";
				
				$i++;
			}
			
			$content .= "</section>\n";
			
			exit(page_renderer::render($title, $content));
			
			//header("content-type: text/plain");
			//var_dump($results);
		});
	}
]);

class search
{
	// Words that we should exclude from the inverted index.
	public static $stop_words = [
		"a", "about", "above", "above", "across", "after", "afterwards", "again",
		"against", "all", "almost", "alone", "along", "already", "also",
		"although", "always", "am", "among", "amongst", "amoungst", "amount",
		"an", "and", "another", "any", "anyhow", "anyone", "anything", "anyway",
		"anywhere", "are", "around", "as", "at", "back", "be", "became",
		"because", "become", "becomes", "becoming", "been", "before",
		"beforehand", "behind", "being", "below", "beside", "besides",
		"between", "beyond", "bill", "both", "bottom", "but", "by", "call",
		"can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de",
		"describe", "detail", "do", "done", "down", "due", "during", "each",
		"eg", "eight", "either", "eleven", "else", "elsewhere", "empty",
		"enough", "etc", "even", "ever", "every", "everyone", "everything",
		"everywhere", "except", "few", "fifteen", "fify", "fill", "find",
		"fire", "first", "five", "for", "former", "formerly", "forty", "found",
		"four", "from", "front", "full", "further", "get", "give", "go", "had",
		"has", "hasnt", "have", "he", "hence", "her", "here", "hereafter",
		"hereby", "herein", "hereupon", "hers", "herself", "him", "himself",
		"his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed",
		"interest", "into", "is", "it", "its", "itself", "keep", "last",
		"latter", "latterly", "least", "less", "ltd", "made", "many", "may",
		"me", "meanwhile", "might", "mine", "more", "moreover", "most",
		"mostly", "move", "much", "must", "my", "myself", "name", "namely",
		"neither", "never", "nevertheless", "next", "nine", "no", "none",
		"nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on",
		"once", "one", "only", "onto", "or", "other", "others", "otherwise",
		"our", "ours", "ourselves", "out", "over", "own", "part", "per",
		"perhaps", "please", "put", "rather", "re", "same", "see", "seem",
		"seemed", "seeming", "seems", "serious", "several", "she", "should",
		"show", "side", "since", "sincere", "six", "sixty", "so", "some",
		"somehow", "someone", "something", "sometime", "sometimes",
		"somewhere", "still", "such", "system", "take", "ten", "than", "that",
		"the", "their", "them", "themselves", "then", "thence", "there",
		"thereafter", "thereby", "therefore", "therein", "thereupon", "these",
		"they", "thickv", "thin", "third", "this", "those", "though", "three",
		"through", "throughout", "thru", "thus", "to", "together", "too", "top",
		"toward", "towards", "twelve", "twenty", "two", "un", "under", "until",
		"up", "upon", "us", "very", "via", "was", "we", "well", "were", "what",
		"whatever", "when", "whence", "whenever", "where", "whereafter",
		"whereas", "whereby", "wherein", "whereupon", "wherever", "whether",
		"which", "while", "whither", "who", "whoever", "whole", "whom", "whose",
		"why", "will", "with", "within", "without", "would", "yet", "you",
		"your", "yours", "yourself", "yourselves"
	];
	
	public static function index($source)
	{
		$source = html_entity_decode($source, ENT_QUOTES);
		$source_length = strlen($source);
		
		$index = [];
		
		$terms = self::tokenize($source);
		$i = 0;
		foreach($terms as $term)
		{
			$nterm = $term;
			
			// Skip over stop words (see https://en.wikipedia.org/wiki/Stop_words)
			if(in_array($nterm, self::$stop_words)) continue;
			
			if(!isset($index[$nterm]))
			{
				$index[$nterm] = [ "freq" => 0, "offsets" => [] ];
			}
			
			$index[$nterm]["freq"]++;
			$index[$nterm]["offsets"][] = $i;
			
			$i++;
		}
		
		return $index;
	}
	
	public static function tokenize($source)
	{
		$source = strtolower($source);
		return preg_split("/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))|\|/", $source, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	public static function strip_markup($source)
	{
		return str_replace([ "[", "]", "\"", "*", "_", " - ", "`" ], "", $source);
	}
	
	public static function rebuild_invindex()
	{
		global $pageindex, $env, $paths;
		
		$invindex = [];
		foreach($pageindex as $pagename => $pagedetails)
		{
			$pagesource = file_get_contents("$env->storage_prefix$pagename.md");
			$index = self::index($pagesource);
			
			self::merge_into_invindex($invindex, ids::getid($pagename), $index);
		}
		
		self::save_invindex($paths->searchindex, $invindex);
	}
	
	/*
	 * @summary Sorts an index alphabetically. Will also sort an inverted index.
	 * 			This allows us to do a binary search instead of a regular
	 * 			sequential search.
	 */
	public static function sort_index(&$index)
	{
		ksort($index, SORT_NATURAL);
	}
	
	/*
	 * @summary Compares two *regular* indexes to find the differences between them.
	 * 
	 * @param {array} $indexa - The old index.
	 * @param {array} $indexb - The new index.
	 * @param {array} $changed - An array to be filled with the nterms of all
	 * 							 the changed entries.
	 * @param {array} $removed - An array to be filled with the nterms of all
	 * 							 the removed entries.
	 */
	public static function compare_indexes($oldindex, $newindex, &$changed, &$removed)
	{
		foreach($oldindex as $nterm => $entry)
		{
			if(!isset($newindex[$nterm]))
				$removed[] = $nterm;
		}
		foreach($newindex as $nterm => $entry)
		{
			if(!isset($oldindex[$nterm]) or // If this world is new
			   $newindex[$nterm] !== $oldindex[$nterm]) // If this word has changed
				$changed[$nterm] = $newindex[$nterm];
		}
	}
	
	/*
	 * @summary Reads in and parses an inverted index.
	 */
	// Todo remove this function and make everything streamable
	public static function load_invindex($invindex_filename) {
		$invindex = json_decode(file_get_contents($invindex_filename), true);
		return $invindex;
	}
	
	/*
	 * @summary Merge an index into an inverted index.
	 */
	public static function merge_into_invindex(&$invindex, $pageid, &$index, &$removals = [])
	{
		// Remove all the subentries that were removed since last time
		foreach($removals as $nterm)
		{
			unset($invindex[$nterm][$pageid]);
		}
		
		// Merge all the new / changed index entries into the inverted index
		foreach($index as $nterm => $newentry)
		{
			// If the nterm isn't in the inverted index, then create a space for it
			if(!isset($invindex[$nterm])) $invindex[$nterm] = [];
			$invindex[$nterm][$pageid] = $newentry;
			
			// Sort the page entries for this word by frequency
			uasort($invindex[$nterm], function($a, $b) {
				if($a["freq"] == $b["freq"]) return 0;
				return ($a["freq"] < $b["freq"]) ? +1 : -1;
			});
		}
		
		// Sort the inverted index by rank
		uasort($invindex, function($a, $b) {
			$ac = count($a); $bc = count($b);
			if($ac == $bc) return 0;
			return ($ac < $bc) ? +1 : -1;
		});
	}
	
	/**
	 * Deletes the given pageid from the given pageindex.
	 * @param  inverted_index	&$invindex	The inverted index.
	 * @param  number			$pageid		The pageid to remove.
	 */
	public static function delete_entry(&$invindex, $pageid)
	{
		$str_pageid = (string)$pageid;
		foreach($invindex as $nterm => &$entry)
		{
			if(isset($entry[$pageid]))
				unset($entry[$pageid]);
			if(isset($entry[$str_pageid]))
				unset($entry[$str_pageid]);
			if(count($entry) === 0)
				unset($invindex[$nterm]);
		}
	}
	
	public static function save_invindex($filename, &$invindex)
	{
		file_put_contents($filename, json_encode($invindex));
	}
	
	public static function query_invindex($query, &$invindex)
	{
		global $settings, $pageindex;
		
		$query_terms = self::tokenize($query);
		$matching_pages = [];
		
		
		// Loop over each term in the query and find the matching page entries
		$count = count($query_terms);
		for($i = 0; $i < $count; $i++)
		{
			$qterm = $query_terms[$i];
			
			// Only search the inverted index if it actually exists there
			if(isset($invindex[$qterm]))
			{
				// Loop over each page in the inverted index entry
				foreach($invindex[$qterm] as $pageid => $page_entry)
				{
					// Create an entry in the matching pages array if it doesn't exist
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					$matching_pages[$pageid]["nterms"][$qterm] = $page_entry;
				}
			}
			
			
			// Loop over the pageindex and search the titles / tags
			foreach ($pageindex as $pagename => $pagedata)
			{
				// Get the current page's id
				$pageid = ids::getid($pagename);
				// Consider matches in the page title
				if(stripos($pagename, $qterm) !== false)
				{
					// We found the qterm in the title
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for page title matches if it doesn't exist already
					if(!isset($matching_pages[$pageid]["title-matches"]))
						$matching_pages[$pageid]["title-matches"] = 0;
					
					$matching_pages[$pageid]["title-matches"] += count(mb_stripos_all($pagename, $qterm));
				}
				
				// Consider matches in the page's tags
				if(isset($pagedata->tags) and // If this page has tags
				   stripos(implode(" ", $pagedata->tags), $qterm) !== false) // And we found the qterm in the tags
				{
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for tag match if there isn't one already
					if(!isset($matching_pages[$pageid]["tag-matches"]))
						$matching_pages[$pageid]["tag-matches"] = 0;
					$matching_pages[$pageid]["tag-matches"] += count(mb_stripos_all(implode(" ", $pagedata->tags), $qterm));
				}
			}
		}
		
		
		foreach($matching_pages as $pageid => &$pagedata)
		{
			$pagedata["pagename"] = ids::getpagename($pageid);
			$pagedata["rank"] = 0;
			
			foreach($pagedata["nterms"] as $pterm => $entry)
			{
				$pagedata["rank"] += $entry["freq"];
				
				// todo rank by context here
			}
			
			// Consider matches in the title / tags
			if(isset($pagedata["title-matches"]))
				$pagedata["rank"] += $pagedata["title-matches"] * $settings->search_title_matches_weighting;
			if(isset($pagedata["tag-matches"]))
				$pagedata["rank"] += $pagedata["tag-matches"] * $settings->search_tags_matches_weighting;
			
			// todo remove items if the rank is below a threshold
		}
		
		// todo sort by rank here
		uasort($matching_pages, function($a, $b) {
			if($a["rank"] == $b["rank"]) return 0;
			return ($a["rank"] < $b["rank"]) ? +1 : -1;
		});
		
		return $matching_pages;
	}
	
	public static function extract_context($query, $source)
	{
		global $settings;
		
		$nterms = self::tokenize($query);
		$matches = [];
		// Loop over each nterm and find it in the source
		foreach($nterms as $nterm)
		{
			$all_offsets = mb_stripos_all($source, $nterm);
			// Skip over adding matches if there aren't any
			if($all_offsets === false)
				continue;
			foreach($all_offsets as $offset)
			{
				$matches[] = [ $nterm, $offset ];
			}
		}
		
		// Sort the matches by offset
		usort($matches, function($a, $b) {
			if($a[1] == $b[1]) return 0;
			return ($a[1] > $b[1]) ? +1 : -1;
		});
		
		$contexts = [];
		$basepos = 0;
		$matches_count = count($matches);
		while($basepos < $matches_count)
		{
			// Store the next match along - all others will be relative to that one
			$group = [$matches[$basepos]];
			
			// Start scanning at the next one along - we always store the first match
			$scanpos = $basepos + 1;
			$distance = 0;
			
			while(true)
			{
				// Break out if we reach the end
				if($scanpos >= $matches_count) break;
				
				// Find the distance between the current one and the last one
				$distance = $matches[$scanpos][1] - $matches[$scanpos - 1][1];
				
				// Store it if the distance is below the threshold
				if($distance < $settings->search_characters_context)
					$group[] = $matches[$scanpos];
				else
					break;
				
				$scanpos++;
			}
			
			$context_start = $group[0][1] - $settings->search_characters_context;
			$context_end = $group[count($group) - 1][1] + $settings->search_characters_context;
			
			//echo("Got context. Start: $context_start, End: $context_end\n");
			//echo("Group:"); var_dump($group);
			
			$context = substr($source, $context_start, $context_end - $context_start);
			
			// Strip the markdown from the context - it's most likely going to
			// be broken anyway.
			$context = self::strip_markup($context);
			
			$contexts[] = $context;
			
			$basepos = $scanpos + 1;
		}
		
		return implode(" ... ", $contexts);
	}
	
	public static function highlight_context($query, $context)
	{
		$qterms = self::tokenize($query);
		
		foreach($qterms as $qterm)
		{
			// From http://stackoverflow.com/a/2483859/1460422
			$context = preg_replace("/" . str_replace("/", "\/", preg_quote($qterm)) . "/i", "<strong class='search-term-highlight'>$0</strong>", $context);
		}
		
		return $context;
	}
}

?>
