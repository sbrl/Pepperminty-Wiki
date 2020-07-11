<?php
register_module([
	"name" => "Library: Search engine",
	"version" => "0.13.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "A library module that provides the backend to the search engine module.",
	"id" => "lib-search-engine",
	"depends" => [ "lib-storage-box" ],
	"code" => function() {
		
	}
]);


/*
███████ ███████  █████  ██████   ██████ ██   ██
██      ██      ██   ██ ██   ██ ██      ██   ██
███████ █████   ███████ ██████  ██      ███████
     ██ ██      ██   ██ ██   ██ ██      ██   ██
███████ ███████ ██   ██ ██   ██  ██████ ██   ██
*/

/**
 * Holds a collection to methods to manipulate various types of search index.
 * @package search
 */
class search
{
	/**
	 * Words that we should exclude from the inverted index.
	 * @source	http://xpo6.com/list-of-english-stop-words/
	 * @var string[]
	 */
	public static $stop_words = [
		"a", "about", "above", "above", "across", "after", "afterwards", "again",
		"against", "all", "almost", "alone", "along", "already", "also",
		"although", "always", "am", "among", "amongst", "amoungst", "amount",
		"an", "and", "another", "any", "anyhow", "anyone", "anything", "anyway",
		"anywhere", "are", "around", "as", "at", "back", "be", "became",
		"because", "become", "becomes", "becoming", "been", "before",
		"beforehand", "behind", "being", "below", "beside", "besides",
		"between", "beyond", "bill", "both", "bottom", "but", "by", "call",
		"can", "can't", "cannot", "co", "con", "could", "couldnt", "cry", "de",
		"describe", "detail", "do", "done", "down", "due", "during", "each",
		"eg", "eight", "either", "eleven", "else", "elsewhere", "empty",
		"enough", "etc", "even", "ever", "every", "everyone", "everything",
		"everywhere", "except", "few", "fill", "find",
		"fire", "first", "five", "for", "former", "formerly", "found",
		"four", "from", "front", "full", "further", "get", "give", "go", "had",
		"has", "hasnt", "have", "he", "hence", "her", "here", "hereafter",
		"hereby", "herein", "hereupon", "hers", "herself", "him", "himself",
		"his", "how", "however", "ie", "if", "in", "inc", "indeed",
		"interest", "into", "is", "it", "its", "it's", "itself", "keep", "last",
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
	
	/**
	 * The StorageBox that contains the inverted index.
	 * @var StorageBox
	 */
	private static $invindex = null;
	/**
	 * The 'did you mean?' index for typo correction.
	 * Only populated if the feature-search-didyoumean module is present.
	 * @var BkTree
	 */
	public static $didyoumeanindex = null;
	
	/**
	 * The transliterator that can be used to transliterate strings.
	 * Transliterated strings are more suitable for use with the search index.
	 * Note that this is no longer wrapped in a function as of v0.21 for 
	 * performance reasons.
	 * @var Transliterator
	 */
	public static $literator = null;
	
	/**
	 * Sorter for sorting lists of *transliterated* strings.
	 * Should work for non-transliterated strings too.
	 * @var Collator
	 */
	private static $sorter;
	
	/**
	 * Initialises the search system.
	 * Do not call this function! It is called automatically.
	 */
	public static function init() {
		self::$literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
		self::$sorter = new Collator("");
	}
	
	/**
	 * Logs a progress message in the right format depending on the current
	 * environment.
	 * @param string $message The message to log.
	 */
	private static function log_progress(string $message, bool $sameline = false) : void {
		if(is_cli()) {
			if($sameline) $message = "$message\r";
			else $message = "$message\n";
			echo($message);
		}
		else {
			echo("data: $message\n\n");
			flush();
		}
	}
	
	/**
	 * Loads the didyoumean index.
	 * Don't forget to call this before making any search queries if didyoumean
	 * typo correction is enabled.
	 * Note that calling it multiple times has no effect. Returns true if the
	 * didyoumean index is already loaded.
	 * @param	string	$filename	The filename of the didyoumean index.
	 * @param	string	$seed_word	The seed word. If this changes, the index must be rebuilt.
	 * @return	bool	Whether the index was loaded successfully or not. Returns false if the feature-search-didyoumean module is not present.
	 */
	public static function didyoumean_load() : bool {
		global $settings, $paths;
		if(!module_exists("feature-search-didyoumean"))
			return false;
		
		// Avoid loading twice
		if(is_a(self::$didyoumeanindex, BkTree::class))
			return true;
		
		self::$didyoumeanindex = new BkTree(
			$paths->didyoumeanindex,
			$settings->search_didyoumean_seed_word
		);
		self::$didyoumeanindex->set_costs(
			$settings->search_didyoumean_cost_insert,
			$settings->search_didyoumean_cost_delete,
			$settings->search_didyoumean_cost_replace
		);
		return true;
	}
	
	/**
	 * Returns a correction for a given word according to the didyoumean index.
	 * Note that this is quite an expensive call.
	 * Check that the word exists in the regular search index first, and that
	 * it's not a stop word before calling this function.
	 * @param	string	$term	The term to correct.
	 * @return	string|null		The closest correction found, or null if none could be located.
	 */
	public static function didyoumean_correct(string $term) : ?string {
		global $settings, $paths, $env;
		$start_time = microtime(true);
		
		// Load the didyoumean index, but only if it's enabled etc
		if(!module_exists("feature-search-didyoumean") || !$settings->search_didyoumean_enabled)
			return null;
		
		// If it's not loaded already, load the didyoumean index on-demand
		if(self::$didyoumeanindex == null)
			search::didyoumean_load($paths->searchindex);
		
		$results = self::$didyoumeanindex->lookup(
			$term,
			$settings->search_didyoumean_editdistance
		);
		if(!empty($results)) {
			usort($results, function($a, $b) : int {
				return self::$sorter->compare($a, $b);
			});
		}
		
		if(!isset($env->perfdata->didyoumean_correction))
			$env->perfdata->didyoumean_correction = 0; 
		$env->perfdata->didyoumean_correction += (microtime(true) - $start_time) * 1000;
		return $results[0] ?? null;
	}
	
	public static function didyoumean_rebuild(bool $output = true) : void {
		global $env;
		if($output && !is_cli()) {
			header("content-type: text/event-stream");
			ob_end_flush();
		}
		
		$env->perfdata->didyoumean_rebuild = microtime(true);
		
		if($output) self::log_progress("Beginning didyoumean index rebuild");
		if($output) self::log_progress("Loading indexes");
		
		self::invindex_load();
		self::didyoumean_load();
		
		if($output) self::log_progress("Populating index");
		
		self::$didyoumeanindex->clear();
		$i = 0;
		foreach(self::$invindex->get_keys("|") as $key) {
			$key = $key["key"];
			
			if(self::$didyoumeanindex->add($key) === null && $output)
				self::log_progress("[$i] Skipping '$key' as it's too long");
			elseif($output && $i % 1500 == 0) self::log_progress("[$i] Added '$key'", true);
			$i++;
		}
		self::log_progress(""); // Blank newline
		if($output) self::log_progress("Syncing to disk...");
		
		// Closing = saving, but we can't use it afterwards
		self::$didyoumeanindex->close();
		
		// Just in case it's loaded again later
		self::$didyoumeanindex = null;
		
		$env->perfdata->didyoumean_rebuild = round(microtime(true) - $env->perfdata->didyoumean_rebuild, 4);
		if($output) self::log_progress("didyoumean index rebuild complete in {$env->perfdata->didyoumean_rebuild}s");
	}
	
	/**
	 * Converts a source string into an index of search terms that can be
	 * merged into an inverted index.
	 * Automatically transliterates the source string.
	 * @param  string $source The source string to index.
	 * @return array         An index represents the specified string.
	 */
	public static function index_generate(string $source) : array {
		// We don't need to normalise or transliterate here because self::tokenize() does this for us
		$source = html_entity_decode($source, ENT_QUOTES);
		$source_length = mb_strlen($source);
		
		$index = [];
		
		$terms = self::tokenize($source, true);
		foreach($terms as $term) {
			// Skip over stop words (see https://en.wikipedia.org/wiki/Stop_words)
			if(in_array($term[0], self::$stop_words)) continue;
			
			if(!isset($index[$term[0]]))
				$index[$term[0]] = [ "freq" => 0, "offsets" => [] ];
			
			$index[$term[0]]["freq"]++;
			$index[$term[0]]["offsets"][] = $term[1];
		}
		
		return $index;
	}
	
	/**
	 * Converts a source string into a series of raw tokens.
	 * @param	string	$source				The source string to process.
	 * @param	bool	$capture_offsets	Whether to capture & return the character offsets of the tokens detected. If true, then each token returned will be an array in the form [ token, char_offset ].
	 * @return	array	An array of raw tokens extracted from the specified source string.
	 */
	public static function tokenize(string $source, bool $capture_offsets = false) : array {
		
		$flags = PREG_SPLIT_NO_EMPTY; // Don't return empty items
		if($capture_offsets)
			$flags |= PREG_SPLIT_OFFSET_CAPTURE;
		
		// We don't need to normalise here because the transliterator handles that
		$source = self::$literator->transliterate($source);
		$source = preg_replace('/[\[\]\|\{\}\/]/u', " ", $source);
		return preg_split("/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))|\|/u", $source, -1, $flags);
	}
	
	/**
	 * Removes (most) markdown markup from the specified string.
	 * Stripped strings are not suitable for indexing!
	 * @param	string	$source	The source string to process.
	 * @return	string			The stripped string.
	 */
	public static function strip_markup(string $source) : string {
		return preg_replace('/([\"*_\[\]]| - |`)/u', "", $source);
	}
	
	/**
	 * Rebuilds the master inverted index and clears the page id index.
	 * @param	bool	$output	Whether to send progress information to the user's browser.
	 */
	public static function invindex_rebuild(bool $output = true) : void {
		global $pageindex, $env, $paths, $settings;
		$env->perfdata->invindex_rebuild = microtime(true);
		
		if($output && !is_cli()) {
			header("content-type: text/event-stream");
			ob_end_flush();
		}
		
		
		// Clear the id index out
		ids::clear();
		
		// Clear the existing inverted index out
		if(self::$invindex == null)
			self::invindex_load($paths->searchindex);
		self::$invindex->clear();
		self::$invindex->set("|termlist|", []);
		
		// Reindex each page in turn
		$i = 0; $max = count(get_object_vars($pageindex));
		$missing_files = 0;
		foreach($pageindex as $pagename => $pagedetails)
		{
			$page_filename = $env->storage_prefix . $pagedetails->filename;
			if(!file_exists($page_filename)) {
				if(!is_cli()) echo("data: ");
				echo("[" . ($i + 1) . " / $max] Error: Can't find $page_filename\n");
				flush();
				$i++; $missing_files++;
				continue;
			}
			// We do not transliterate or normalise here because the indexer will take care of this for us
			$index = self::index_generate(file_get_contents($page_filename));
			
			$pageid = ids::getid($pagename);
			self::invindex_merge($pageid, $index);
			
			if($output) {
				$message = "[" . ($i + 1) . " / $max] Added $pagename (id #$pageid) to the new search index.";
				if(!is_cli()) $message = "data: $message\n\n";
				else $message = "$message\r";
				echo($message);
				flush();
			}
			
			$i++;
		}
		
		$msg = "Syncing to disk....";
		if(!is_cli()) $msg = "data: $msg\n\n";
		else $msg = "$msg\r";
		echo($msg);
		
		self::invindex_close();
		
		$env->perfdata->invindex_rebuild = round(microtime(true) - $env->perfdata->invindex_rebuild, 4);
		
		if($output && !is_cli()) {
			echo("data: Search index rebuilding complete in {$env->perfdata->invindex_rebuild}s.\n\n");
			echo("data: Couldn't find $missing_files pages on disk. If $settings->sitename couldn't find some pages on disk, then you might need to manually correct $settings->sitename's page index (stored in pageindex.json).\n\n");
			echo("data: Done! Saving new search index to '$paths->searchindex'.\n\n");
		}
		if(is_cli()) echo("\nSearch index rebuilding complete in {$env->perfdata->invindex_rebuild}s.\n");
	}
	
	/**
	 * Sorts an index alphabetically.
	 * This allows us to do a binary search instead of a regular
	 * sequential search.
	 * @param	array	$index	The index to sort.
	 */
	public static function index_sort(&$index) {
		$sorter = self::$sorter;
		uksort($index, function($a, $b) use($sorter) : int {
			return $sorter->compare($a, $b);
		});
	}
	/**
	 * Sorts an index by frequency.
	 * @param  array $index The index to sort.
	 */
	public static function index_sort_freq(&$index) {
		uasort($index, function($a, $b) : int {
			return $b["freq"] > $a["freq"];
		});
	}
	
	/**
	 * Compares two *regular* indexes to find the differences between them.
	 * @param	array	$oldindex	The old index.
	 * @param	array	$newindex	The new index.
	 * @param	array	$changed	An array to be filled with the nterms of all the changed entries.
	 * @param	array	$removed	An array to be filled with the nterms of all  the removed entries.
	 */
	public static function index_compare($oldindex, $newindex, &$changed, &$removed) {
		foreach($oldindex as $nterm => $entry) {
			if(!isset($newindex[$nterm]))
				$removed[] = $nterm;
		}
		foreach($newindex as $nterm => $entry) {
			if(!isset($oldindex[$nterm]) or // If this word is new
			   $newindex[$nterm] !== $oldindex[$nterm]) // If this word has changed
				$changed[$nterm] = $newindex[$nterm];
		}
	}
	
	/**
	 * Loads a connection to an inverted index.
	 */
	public static function invindex_load() {
		global $env, $paths;
		// If the inverted index is alreayd loaded, it doesn't need loading again
		if(self::$invindex !== null) return;
		$start_time = microtime(true);
		self::$invindex = new StorageBox($paths->searchindex);
		$env->perfdata->searchindex_load_time = round((microtime(true) - $start_time)*1000, 3);
	}
	
	/**
	 * Closes the currently open inverted index.
	 */
	public static function invindex_close() {
		global $env;
		
		$start_time = microtime(true);
		self::$invindex->close();
		self::$invindex = null;
		$env->perfdata->searchindex_close_time = round((microtime(true) - $start_time)*1000, 3);
	}
	
	/**
	 * Merge an index into an inverted index.
	 * @param	int		$pageid		The id of the page to assign to the index that's being merged.
	 * @param	array	$index		The regular index to merge.
	 * @param	array	$removals	An array of index entries to remove from the inverted index. Useful for applying changes to an inverted index instead of deleting and remerging an entire page's index.
	 */
	public static function invindex_merge($pageid, &$index, &$removals = []) : void {
		if(self::$invindex == null)
			throw new Exception("Error: Can't merge into an inverted index that isn't loaded.");
		
		if(!self::$invindex->has("|termlist|"))
			self::$invindex->set("|termlist|", []);
		$termlist = self::$invindex->get("|termlist|");
		
		// Remove all the subentries that were removed since last time
		foreach($removals as $nterm) {
			// Delete the offsets
			self::$invindex->delete("$nterm|$pageid");
			// Delete the item from the list of pageids containing this term
			$nterm_pageids = self::$invindex->get_arr_simple($nterm);
			array_splice($nterm_pageids, array_search($pageid, $nterm_pageids), 1);
			if(empty($nterm_pageids)) { // No need to keep the pageid list if there's nothing in it
				self::$invindex->delete($nterm);
				// Update the termlist if we're deleting the term completely
				$termlist_loc = array_search($nterm, $termlist);
				if($termlist_loc !== false) array_splice($termlist, $termlist_loc, 1);
			}
			else
				self::$invindex->set_arr_simple($nterm, $nterm_pageids);
		}
		
		// Merge all the new / changed index entries into the inverted index
		foreach($index as $nterm => $newentry) {
			// if(!is_string($nterm)) $nterm = strval($nterm);
			if(!self::$invindex->has($nterm)) {
				self::$invindex->set_arr_simple($nterm, []);
				$termlist[] = $nterm;
			}
			
			// Update the nterm pageid list
			$nterm_pageids = self::$invindex->get_arr_simple($nterm);
			if(array_search($pageid, $nterm_pageids) === false) {
				$nterm_pageids[] = $pageid;
				self::$invindex->set_arr_simple($nterm, $nterm_pageids);
			}
			
			// Store the offset list
			self::$invindex->set("$nterm|$pageid", $newentry);
		}
		
		self::$invindex->set("|termlist|", $termlist);
	}
	
	/**
	 * Deletes the given pageid from the given pageindex.
	 * @param  int		$pageid		The pageid to remove.
	 */
	public static function invindex_delete(int $pageid) {
		$termlist = self::$invindex->get("|termlist|");
		foreach($termlist as $nterm) {
			$nterm_pageids = self::$invindex->get_arr_simple($nterm);
			$nterm_loc = array_search($pageid, $nterm_pageids);
			// If this nterm doesn't appear in the list, we're not interested
			if($nterm_loc === false)
				continue;
			
			// Delete it from the ntemr list
			array_splice($nterm_pageids, $nterm_loc, 1);
			
			// Delete the offset list
			self::$invindex->delete("$nterm|$pageid");
			
			// If this term doesn't appear in any other documents, delete it
			if(count($nterm_pageids) === 0) {
				self::$invindex->delete($nterm);
				array_splice($termlist, array_search($nterm, $termlist), 1);
			}
			else // Save the document id list back, since it still contains other pageids
				self::$invindex->set_arr_simple($nterm, $nterm_pageids);
		}
		// Save the termlist back to the store
		self::$invindex->set("|termlist|", $termlist);
	}
	
	
	/*
	 * ███████ ████████  █████  ███████
	 * ██         ██    ██   ██ ██
	 * ███████    ██    ███████ ███████
	 *      ██    ██    ██   ██      ██
	 * ███████    ██    ██   ██ ███████
	 */
	
	/**
	 * Splits a query string into tokens. Does not require that the input string be transliterated.
	 * Was based on my earlier explode_adv: https://starbeamrainbowlabs.com/blog/article.php?article=posts/081-PHP-String-Splitting.html
	 * Now improved to be strtok-based, since it's much faster.
	 * Example I used when writing this: https://www.php.net/manual/en/function.strtok.php#94463
	 * @param	string	$query	The query string to split.
	 */
	public function stas_split($query) {
		$query = self::$literator->transliterate($query);
		
		$terms = [];
		$next_token = strtok($query, " \r\n\t");
		while(true) {
			if(strpos($next_token, '"') !== false)
				$next_token .= " " . strtok('"') . '"';
			if(strpos($next_token, "'") !== false)
				$next_token .= " " . strtok("'") . "'";

			$terms[] = $next_token;
			
			$next_token = strtok(" \r\n\t");
			if($next_token === false) break;
		}
		
		return $terms;
	}

	/**
	 * Parses an array of query tokens into an associative array of search directives.
	 * Supported syntax derived from these sources:
		 * https://help.duckduckgo.com/duckduckgo-help-pages/results/syntax/
		 * https://docs.microsoft.com/en-us/windows/win32/lwef/-search-2x-wds-aqsreference

	 * @param	string[]	$tokens	The array of query tokens to parse.
	 */
	public function stas_parse($tokens) {
		global $settings;
		
		/* Supported Syntax *
		 * 
		 * -term				exclude a term
		 * +term				double the weighting of a term
		 * terms !dest terms	redirect entire query (minus the !bang) to interwiki with registered shortcut dest
		 * prefix:term			apply prefix operator to term
		 * "term"				exactly this term (don't try and correct)
		 */
		$result = [
			"terms" => [],
			"exclude" => [],
			"interwiki" => null
		];
		
		
		$count = count($tokens);
		for($i = count($tokens) - 1; $i >= 0; $i--) {
			// Look for excludes
			if($tokens[$i][0] == "-") {
				if(in_array(substr($tokens[$i], 1), self::$stop_words)) {
					$result["tokens"][] = [
						"term" => substr($tokens[$i], 1),
						"weight" => -1,
						"location" => "all",
						"exact" => false
					];
				}
				else // FUTURE: Correct excludes too
					$result["exclude"][] = substr($tokens[$i], 1);
				
				continue;
			}

			// Look for weighted terms
			if($tokens[$i][0] == "+") {
				if(in_array(substr($tokens[$i], 1), self::$stop_words)) {
					$result["tokens"] = [ "term" => substr($tokens[$i], 1), "weight" => -1, "location" => "all" ];
				}
				else {
					$term = trim(substr($tokens[$i], 1), '"');
					$result["terms"][] = [
						"term" => $term,
						"weight" => 2,
						"location" => "all",
						// if it's different, then there were quotes
						"exact" => substr($tokens[$i], 1) != $term
					];
				}
				continue;
			}

			// Look for interwiki searches
			// You can only go to 1 interwiki destination at once, so we replace any previous finding with this one
			if($tokens[$i][0] == "!" || substr($tokens[$i], -1) == "!")
				$result["interwiki"] = trim($tokens[$i], "!");
			
			// Look for colon directives in the form directive:term
			// Also supports prefix:"quoted term with spaces", quotes stripped automatically
			/*** Example directives *** (. = implemented, * = not implemented)
			 . intitle		search only page titles for term
			 . intags		search only tags for term
			 . inbody		search page body only for term
			 * before		search only pages that were last modified before term
			 * after		search only pages that were last modified after term
			 * size			search only pages that match the size spec term (e.g. 1k+ -> more than 1k bytes, 2k- -> less than 2k bytes, >5k -> more than 5k bytes, <10k -> less than 10k bytes)
			 **************************/
			if(strpos($tokens[$i], ":") !== false) {
				$parts = explode(":", $tokens[$i], 2);
				
				$exact = false;
				$term = trim($parts[1], '"');
				// If we trim off quotes, then it must be because it should be exact
				if($parts[1] != $term) $exact = true;
				
				switch($parts[0]) {
					case "intitle": // BUG: What if a normal word is found in a title?
						$result["terms"][] = [
							"term" => $term,
							"weight" => $settings->search_title_matches_weighting * mb_strlen($parts[1]),
							"location" => "title",
							"exact" => $exact
						];
						break;
					case "intags":
						$result["terms"][] = [
							"term" => $term,
							"weight" => $settings->search_tags_matches_weighting * mb_strlen($parts[1]),
							"location" => "tags",
							"exact" => $exact
						];
						break;
					case "inbody":
						$result["terms"][] = [
							"term" => $term,
							"weight" => 1,
							"location" => "body",
							"exact" => $exact
						];
						break;
					default:
						if(!isset($result[$parts[0]]))
							$result[$parts[0]] = [];
						$result[$parts[0]][] = $term;
						break;
				}
				continue;
			}
			
			$exact = false;
			$term = trim($tokens[$i], '"');
			// If we trim off quotes, then it must be because it should be exact
			if($tokens[$i] != $term) $exact = true;
			
			// Doesn't appear to be particularly special *shrugs*
			// Set the weight to -1 if it's a stop word
			$result["terms"][] = [
				"term" => $term,
				"weight" => in_array($tokens[$i], self::$stop_words) ? -1 : 1,
				"location" => "all",
				"exact" => $exact // If true then we shouldn't try to autocorrect it
			];
		}
		
		
		// Correct typos, but only if that's enabled
		if(module_exists("feature-search-didyoumean") && $settings->search_didyoumean_enabled) {
			$terms_count = count($result["terms"]);
			for($i = 0; $i < $terms_count; $i++) {
				// error_log("[stas_parse/didyoumean] Now looking at #$i:  ".var_export($result["terms"][$i], true)."(total count: $terms_count)");
				if($result["terms"][$i]["exact"] || // Skip exact-only
					$result["terms"][$i]["weight"] < 1 || // Skip stop & irrelevant words
					self::invindex_term_exists($result["terms"][$i]["term"]))
						continue;
				
				// It's not a stop word or in the index, try and correct it
				// self::didyoumean_correct auto-loads the didyoumean index on-demand
				$correction = self::didyoumean_correct($result["terms"][$i]["term"]);
				// Make a note if we fail to correct a term
				if(!is_string($correction)) {
					$result["terms"][$i]["corrected"] = false;
					continue;
				}
				
				$result["terms"][$i]["term_before"] = $result["terms"][$i]["term"];
				$result["terms"][$i]["term"] = $correction;
				$result["terms"][$i]["corrected"] = true;
			}
		}
		
		return $result;
	}
	
	/**
	 * Determines whether a term exists in the currently loaded inverted search
	 * index.
	 * Note that this only checked for precisely $term. See
	 * search::didyoumean_correct() for typo correction.
	 * @param	string	$term	The term to search for.
	 * @return	bool	Whether term exists in the inverted index or not.
	 */
	public static function invindex_term_exists(string $term) {
		// In the inverted index $term should have a list of page names in it
		// if the temr exists in the index, and won't exists if not
		return self::$invindex->has($term);
	}
	
	/**
	 * Returns the page ids that contain the given (transliterated) search term.
	 * @param  string $term The search term to look for.
	 * @return string[]       The list of page ids that contain the given term.
	 */
	public static function invindex_term_getpageids(string $term) {
		return self::$invindex->get_arr_simple($term);
	}
	
	/**
	 * Gets the offsets object for a given term on a given page.
	 * The return object is in the form { freq: 4, offsets: [2,3,4] }
	 * @param	string	$term	The term to search for.
	 * @param	int		$pageid	The id of the page to retrieve the offsets list for.
	 * @return	object	The offsets object as described above.
	 */
	public static function invindex_term_getoffsets(string $term, int $pageid) {
		return self::$invindex->get("$term|$pageid");
	}
	
	/**
	 * Searches the given inverted index for the specified search terms.
	 * Note that this automatically pushes the query string through STAS which
	 * can be a fairly expensive operation, so use 2nd argument if you need
	 * to debug the STAS parsing result if possible.
	 * @param	string		$query		The search query. If an array is passed, it is assumed it has already been pre-parsed with search::stas_parse().
	 * @param	&stdClass	$query_stas	An object to fill with the result of the STAS parsing.
	 * @return	array	An array of matching pages.
	 */
	public static function invindex_query($query, &$query_stas = null)
	{
		global $settings, $pageindex;
		
		$query_stas = self::stas_parse(
			self::stas_split(self::$literator->transliterate($query))
		);
		
		/* Sub-array format:
		 * [
		 * 	nterms : [ nterm => frequency, nterm => frequency, .... ],
		 * 	offsets_body : int[],
		 * 	matches_title : int,
		 * 	matches_tags : int
		 * ]
		 */
		$matching_pages = [];
		$match_template = [
			"nterms" => [],
			"offsets_body" => [],
			"rank_title" => 0,
			"rank_tags" => 0
		];
		
		// Query the inverted index
		foreach($query_stas["terms"] as $term_def) {
			if($term_def["weight"] == -1)
				continue; // Skip stop words
			
			if(!in_array($term_def["location"], ["all", "inbody"]))
				continue; // Skip terms we shouldn't search the page body for
			
			if(!self::$invindex->has($term_def["term"]))
				continue; // Skip if it's not in the index
			
			// For each page that contains this term.....
			$term_pageids = self::$invindex->get_arr_simple($term_def["term"]);
			foreach($term_pageids as $pageid) {
				// Check to see if it contains any words we should exclude
				$skip = false;
				foreach($query_stas["exclude"] as $excl_term) {
					if(self::$invindex->has("$excl_term|$pageid")) {
						$skip = true;
						break;
					}
				}
				if($skip) continue;
				
				// Get the list of offsets
				$page_offsets = self::$invindex->get("{$term_def["term"]}|$pageid");
				
				if(!isset($matching_pages[$pageid]))
					$matching_pages[$pageid] = $match_template; // Arrays are assigned by copy in php
				
				// Add it to the appropriate $matching_pages entry, not forgetting to apply the weighting
				$matching_pages[$pageid]["offsets_body"] = array_merge(
					$matching_pages[$pageid]["offsets_body"],
					$page_offsets->offsets
				);
				$matching_pages[$pageid]["nterms"][$term_def["term"]] = $page_offsets->freq * $term_def["weight"];
			}
			
		}
		
		// Query page titles & tags
		foreach($query_stas["terms"] as $term_def) {
			// No need to skip stop words here, since we're doing a normal 
			// sequential search anyway
			if(!in_array($term_def["location"], ["all", "title", "tags"]))
				continue; // Skip terms we shouldn't search the page body for
			
			// Loop over the pageindex and search the titles / tags
			reset($pageindex); // Reset array/object pointer
			foreach($pageindex as $pagename => $pagedata) {
				// Setup a variable to hold the current page's id
				$pageid = null; // Cache the page id
				
				$lit_title = self::$literator->transliterate($pagename);
				$lit_tags = isset($pagedata->tags) ? self::$literator->transliterate(implode(" ", $pagedata->tags)) : null;
				$pageid = null; // populated on-demand
				
				// Make sure that the title & tags don't contain a term we should exclude
				$skip = false;
				foreach($query_stas["exclude"] as $excl_term) {
					if(mb_strpos($lit_title, $excl_term) !== false) {
						$skip = true;
						if($pageid === null) $pageid = ids::getid($pagename);
						// Delete it from the candidate matches (it might be present in the tags / title but not the body)
						if(isset($matching_pages[$pageid]))
							unset($matching_pages[$pageid]);
						break;
					}
				}
				if($skip) continue;
				
				// Consider matches in the page title
				if(in_array($term_def["location"], ["all", "title"])) {
					// FUTURE: We may be able to optimise this further by using preg_match_all + preg_quote instead of mb_stripos_all. Experimentation / benchmarking is required to figure out which one is faster
					$title_matches = mb_stripos_all($lit_title, $term_def["term"]);
					$title_matches_count = $title_matches !== false ? count($title_matches) : 0;
					if($title_matches_count > 0) {
						if($pageid === null) $pageid = ids::getid($pagename);
						// We found the qterm in the title
						if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = $match_template; // Assign by copy
						
						$matching_pages[$pageid]["rank_title"] += $title_matches_count * $term_def["weight"];
					}
				}
				
				// If this page doesn't have any tags, skip it
				if($lit_tags == null)
					continue;
				
				if(!in_array($term_def["location"], ["all", "tags"]))
					continue; // If we shouldn't search the tags, no point in continuing
				
				// Consider matches in the page's tags
				$tag_matches = isset($pagedata->tags) ? mb_stripos_all($lit_tags, $term_def["term"]) : false;
				$tag_matches_count = $tag_matches !== false ? count($tag_matches) : 0;
				
				if($tag_matches_count > 0) {// And we found the qterm in the tags
					if($pageid === null) $pageid = ids::getid($pagename);
					
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = $match_template; // Assign by copy
					
					$matching_pages[$pageid]["rank_tags"] += $tag_matches_count * $term_def["weight"];
				}
			}
		}
		
		// TODO: Implement the rest of STAS here
		
		reset($matching_pages);
		foreach($matching_pages as $pageid => &$pagedata) {
			$pagedata["pagename"] = ids::getpagename($pageid);
			$pagedata["rank"] = 0;
			
			$pageOffsets = [];
			
			// Loop over each search term found on this page
			reset($pagedata["nterms"]);
			foreach($pagedata["nterms"] as $pterm => $frequency) {
				// Add the number of occurrences of this search term to the ranking
				// Multiply it by the length of the word
				$pagedata["rank"] += $frequency * strlen($pterm);
			}
			
			// Consider matches in the title / tags
			$pagedata["rank"] += $pagedata["rank_title"] + $pagedata["rank_tags"];
			
			// TODO: Consider implementing kernel density estimation here.
			// https://en.wikipedia.org/wiki/Kernel_density_estimation
			// We want it to have more of an effect the more words that are present in the query. Maybe a logarithmic function would be worth investigating here?
			
			// TODO: Remove items if the computed rank is below a threshold
		}
		unset($pagedata); // Ref https://bugs.php.net/bug.php?id=70387
		
		uasort($matching_pages, function($a, $b) {
			if($a["rank"] == $b["rank"]) return 0;
			return ($a["rank"] < $b["rank"]) ? +1 : -1;
		});
		
		return $matching_pages;
	}
	
	/**
	 * Extracts a context string (in HTML) given a search query that could be displayed
	 * in a list of search results.
	 * @param	string	$pagename	The name of the paget that this source belongs to. Used when consulting the inverted index.
	 * @param	string	$query_parsed	The *parsed* search query to generate the context for (basically the output of search::stas_parse()).
	 * @param	string	$source		The page source to extract the context from.
	 * @return	string				The generated context string.
	 */
	public static function extract_context($pagename, $query_parsed, $source)
	{
		global $settings;
		
		$pageid = ids::getid($pagename);
		$nterms = $query_parsed["terms"];
		
		// Query the inverted index for offsets
		$matches = [];
		foreach($nterms as $nterm) {
			// Skip if the page isn't found in the inverted index for this word
			if(!self::$invindex->has("{$nterm["term"]}|$pageid"))
				continue;
			
			$nterm_offsets = self::$invindex->get("{$nterm["term"]}|$pageid")->offsets;
			
			foreach($nterm_offsets as $next_offset)
				$matches[] = [ $nterm["term"], $next_offset ];
		}
		
		// Sort the matches by offset
		usort($matches, function($a, $b) {
			if($a[1] == $b[1]) return 0;
			return ($a[1] > $b[1]) ? +1 : -1;
		});
		
		$sourceLength = mb_strlen($source);
		
		$contexts = [];
		
		$matches_count = count($matches);
		$total_context_length = 0;
		for($i = 0; $i < $matches_count; $i++) {
			$next_context = [
				"from" => max(0, $matches[$i][1] - $settings->search_characters_context),
				"to" => min($sourceLength, $matches[$i][1] + mb_strlen($matches[$i][0]) + $settings->search_characters_context)
			];
			
			if(end($contexts) !== false && end($contexts)["to"] > $next_context["from"]) {
				// This next context overlaps with the previous one
				// Extend the last one instead of adding a new one
				
				// The array pointer is pointing at the last element now because we called end() above
				
				// Update the total context length counter appropriately
				$total_context_length += $next_context["to"] - $contexts[key($contexts)]["to"];
				$contexts[key($contexts)]["to"] = $next_context["to"];
			}
			else { // No overlap here! Business as usual.
				$contexts[] = $next_context;
				// Update the total context length counter as normal
				$total_context_length += $next_context["to"] - $next_context["from"];
			}
			
			
			end($contexts);
			$last_context = &$contexts[key($contexts)];
			if($total_context_length > $settings->search_characters_context_total) {
				// We've reached the limit on the number of characters this context should contain. Trim off the context to fit and break out
				$last_context["to"] -= $total_context_length - $settings->search_characters_context_total;
				break;
			}
		}
		
		$contexts_text = [];
		foreach($contexts as $context) {
			$contexts_text[] = substr($source, $context["from"], $context["to"] - $context["from"]);
		}
		
		// BUG: Make sure that a snippet is centred on the word in question if we have to cut it short
		
		$result = implode(" … ", $contexts_text);
		end($contexts); // If there's at least one item in the list and were not at the very end of the page, add an extra ellipsis
		if(isset($contexts[0]) && $contexts[key($contexts)]["to"] < $sourceLength) $result .= "… ";
		// Prepend an ellipsis if the context doesn't start at the beginning of a page
		if(isset($contexts[0]) && $contexts[0]["from"] > 0) $result = " …$result";
		
		return $result;
	}
	
	/**
	 * Highlights the keywords of a context string.
	 * @param	array	$query_parsed	The *parsed* query to use when highlighting (the output of search::stas_parse())
	 * @param	string	$context	The context string to highlight.
	 * @return	string				The highlighted (HTML) string.
	 */
	public static function highlight_context($query_parsed, $context)
	{
		$qterms = $query_parsed["terms"];
		
		foreach($qterms as $qterm) {
			// Stop words are marked by STAS
			if($qterm["weight"] == -1)
				continue;
			
			// From http://stackoverflow.com/a/2483859/1460422
			$context = preg_replace("/" . preg_replace('/\\//u', "\/", preg_quote($qterm["term"])) . "/iu", "<strong class='search-term-highlight'>$0</strong>", $context);
		}
		
		return $context;
	}
}
// Run the init function
search::init();

?>
