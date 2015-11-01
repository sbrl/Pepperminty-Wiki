<?php
register_module([
	"name" => "Search",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki. Note that this module, at the moment, just contains test code while I figure out how best to write a search engine.",
	"id" => "feature-search",
	"code" => function() {
		add_action("index", function() {
			global $settings, $env;
			
			$breakable_chars = "\r\n\t .,\\/!\"£$%^&*[]()+`_~#";
			
			header("content-type: text/plain");
			
			$source = file_get_contents("$env->page.md");
			
			$index = search::index($source);
			
			var_dump($env->page);
			var_dump($source);
			
			var_dump($index);
		});
		
		add_action("invindex-rebuild", function() {
			search::rebuild_invindex();
		});
		
		add_action("search", function() {
			global $settings, $env, $pageindex;
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			$invindex = search::load_invindex("invindex.json");
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
				$content .= "<p>There isn't a page called $query on $settings->sitename, but you can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.</p>";
			}
			
			$i = 0; // todo use $_GET["offset"] and $_GET["result-count"] or something
			foreach($results as $result)
			{
				$link = "?page=" . rawurlencode($result["pagename"]);
				$pagesource = file_get_contents($result["pagename"] . ".md");
				$context = search::extract_context($_GET["query"], $pagesource);
				$context = search::highlight_context($_GET["query"], $context);
				
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
		global $pageindex;
		
		$invindex = [];
		foreach($pageindex as $pagename => $pagedetails)
		{
			$pagesource = file_get_contents("$pagename.md");
			$index = self::index($pagesource);
			
			self::merge_into_invindex($invindex, ids::getid($pagename), $index);
		}
		
		self::save_invindex("invindex.json", $invindex);
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
	
	public static function save_invindex($filename, &$invindex)
	{
		file_put_contents($filename, json_encode($invindex));
	}
	
	public static function query_invindex($query, &$invindex)
	{
		$query_terms = self::tokenize($query);
		$matching_pages = [];
		
		// Loop over each term in the query and find the matching page entries
		for($i = 0; $i < count($query_terms); $i++)
		{
			$qterm = $query_terms[$i];
			
			// Skip over this term if it isn't in the inverted index
			if(!isset($invindex[$qterm]))
				continue;
			
			// Loop over each page
			foreach($invindex[$qterm] as $pageid => $page_entry)
			{
				// Create an entry in the matching pages array if it doesn't exist
				if(!isset($matching_pages[$pageid]))
					$matching_pages[$pageid] = [ "nterms" => [] ];
				$matching_pages[$pageid]["nterms"][$qterm] = $page_entry;
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
		
		usort($matches, function($a, $b) {
			if($a[1] == $b[1]) return 0;
			return ($a[1] < $b[1]) ? +1 : -1;
		});
		
		$contexts = [];
		$basepos = 0;
		$matches_count = count($matches);
		while($basepos < $matches_count)
		{
			// Store the next match along - all others will be relative to that
			// one
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
			$context = preg_replace("/" . preg_quote($qterm) . "/i", "<strong>$0</strong>", $context);
		}
		
		return $context;
	}
}

/**
 * mb_stripos all occurences
 * from http://www.pontikis.net/tip/?id=16
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack (case-insensitive, UTF8)
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
 */
function mb_stripos_all($haystack, $needle) {
	$s = 0; $i = 0;
	while(is_integer($i)) {
		$i = mb_stripos($haystack, $needle, $s);
		if(is_integer($i)) {
			$aStrPos[] = $i;
			$s = $i + mb_strlen($needle);
		}
	}

	if(isset($aStrPos))
		return $aStrPos;
	else
		return false;
}

?>
