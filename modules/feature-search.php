<?php
register_module([
	"name" => "Search",
	"version" => "0.8",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki using an inverted index to provide a full text search engine. If pages don't show up, then you might have hit a stop word. If not, try requesting the `invindex-rebuild` action to rebuild the inverted index from scratch.",
	"id" => "feature-search",
	"code" => function() {
		global $settings;
		
		/**
		 * @api {get} ?action=index&page={pageName} Get an index of words for a given page
		 * @apiName SearchIndex
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * @apiDescription For debugging purposes. Be warned - the format could change at any time!
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
			
			echo("Page name: $env->page\n");
			echo("--------------- Source ---------------\n");
			echo($source); echo("\n");
			echo("--------------------------------------\n\n");
			echo("---------------- Index ---------------\n");
			foreach($index as $term => $entry) {
				echo("$term: {$entry["freq"]} matches | " . implode(", ", $entry["offsets"]) . "\n");
			}
			echo("--------------------------------------\n");
		});
		
		/**
		 * @api {get} ?action=invindex-rebuild Rebuild the inverted search index from scratch
		 * @apiDescription	Causes the inverted search index to be completely rebuilt from scratch. Can take a while for large wikis!
		 * @apiName			SearchInvindexRebuild
		 * @apiGroup		Search
		 * @apiPermission	Admin
		 *
		 * @apiParam	{string}	secret		Optional. Specify the secret from peppermint.json here in order to rebuild the search index without logging in.
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
			global $env, $settings;
			if($env->is_admin ||
				(
					!empty($_POST["secret"]) &&
					$_POST["secret"] === $settings->secret
				)
			)
				search::rebuild_invindex();
			else
			{
				http_response_code(401);
				exit(page_renderer::render_main("Error - Search index regenerator - $settings->sitename", "<p>Error: You aren't allowed to regenerate the search index. Try logging in as an admin, or setting the <code>secret</code> POST parameter to $settings->sitename's secret - which can be found in $settings->sitename's <code>peppermint.json</code> file.</p>"));
			}
		});
		
		
		/**
		 * @api {get} ?action=idindex-show Show the id index
		 * @apiDescription	Outputs the id index. Useful if you need to verify that it's working as expected. Output is a json object.
		 * @apiName			SearchShowIdIndex
		 * @apiGroup		Search
		 * @apiPermission	Anonymous
		 */
		add_action("idindex-show", function() {
			global $idindex;
			header("content-type: application/json; charset=UTF-8");
			exit(json_encode($idindex, JSON_PRETTY_PRINT));
		});
		
		/**
		 * @api {get} ?action=search&query={text}[&format={format}]	Search the wiki for a given query string
		 * @apiName Search
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	query	The query string to search for.
		 * @apiParam {string}	format	Optional. Valid values: html, json. In json mode an object is returned with page names as keys, values as search result information - sorted in ranking order.
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
				search::rebuild_invindex(false);
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			
			$time_start = microtime(true);
			$invindex = search::load_invindex($paths->searchindex);
			$env->perfdata->invindex_decode_time = round((microtime(true) - $time_start)*1000, 3);
			
			$start = microtime(true);
			$results = search::query_invindex($_GET["query"], $invindex);
			$resultCount = count($results);
			$env->perfdata->invindex_query_time = round((microtime(true) - $time_start)*1000, 3);
			
			header("x-invindex-decode-time: {$env->perfdata->invindex_decode_time}ms");
			header("x-invindex-query-time: {$env->perfdata->invindex_query_time}ms");
			
			$start = microtime(true);
			foreach($results as &$result) {
				$result["context"] = search::extract_context(
					$invindex, $result["pagename"],
					$_GET["query"],
					file_get_contents($env->storage_prefix . $result["pagename"] . ".md")
				);
			}
			$env->perfdata->context_generation_time = round((microtime(true) - $start)*1000, 3);
			header("x-context-generation-time: {$env->perfdata->context_generation_time}ms");
			
			$env->perfdata->search_time = round((microtime(true) - $search_start)*1000, 3);
			
			header("x-search-time: {$env->perfdata->search_time}ms");
			
			if(!empty($_GET["format"]) && $_GET["format"] == "json") {
				header("content-type: application/json");
				$json_results = new stdClass();
				foreach($results as $result) $json_results->{$result["pagename"]} = $result;
				exit(json_encode($json_results));
			}

			$title = $_GET["query"] . " - Search results - $settings->sitename";
			
			$content = "<section>\n";
			$content .= "<h1>Search Results</h1>";
			
			/// Search Box ///
			$content .= "<form method='get' action=''>\n";
			$content .= "	<input type='search' id='search-box' name='query' placeholder='Type your query here and then press enter.' value='" . htmlentities($_GET["query"], ENT_HTML5 | ENT_QUOTES) . "' />\n";
			$content .= "	<input type='hidden' name='action' value='search' />\n";
			$content .= "</form>";
			
			$content .= "<p>Found $resultCount " . ($resultCount === 1 ? "result" : "results") . " in " . $env->perfdata->search_time . "ms. ";
			
			$query = $_GET["query"];
			if(isset($pageindex->$query))
			{
				$content .= "There's a page on $settings->sitename called <a href='?page=" . rawurlencode($query) . "'>$query</a>.";
			}
			else
			{
				$content .= "There isn't a page called $query on $settings->sitename, but you ";
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
					$content .= "can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.";
				}
			}
			$content .= "</p>";
			
			if(module_exists("page-list")) {
				$nterms = search::tokenize($query);
				$nterms_regex = implode("|", array_map(function($nterm) {
					return preg_quote(strtolower(trim($nterm)));
				}, $nterms));
				$all_tags = get_all_tags();
				$matching_tags = [];
				foreach($all_tags as $tag) {
					if(preg_match("/$nterms_regex/i", trim($tag)) > 0)
						$matching_tags[] = $tag;
				}
				
				if(count($matching_tags) > 0) {
					$content .= "<p class='matching-tags-display'><label>Matching tags</label><span class='tags'>";
					foreach($matching_tags as $tag) {
						$content .= "\t<a href='?action=list-tags&tag=" . rawurlencode($tag)  ."' class='mini-tag'>" . htmlentities($tag) . "</a> \n";
					}
					$content .= "</span></p>";
				}
			}
			
			$i = 0; // todo use $_GET["offset"] and $_GET["result-count"] or something
			foreach($results as $result)
			{
				$link = "?page=" . rawurlencode($result["pagename"]);
				$pagesource = file_get_contents($env->storage_prefix . $result["pagename"] . ".md");
				
				//echo("Extracting context for result " . $result["pagename"] . ".\n");
				$context = $result["context"];
				if(mb_strlen($context) === 0)
					$context = mb_substr($pagesource, 0, $settings->search_characters_context * 2);
				//echo("'Generated search context for " . $result["pagename"] . ": $context'\n");
				$context = search::highlight_context(
					$_GET["query"],
					preg_replace('/</u', '&lt;', $context)
				);
				/*if(strlen($context) == 0)
				{
					$context = search::strip_markup(file_get_contents("$env->page.md", null, null, null, $settings->search_characters_context * 2));
					if($pageindex->{$env->page}->size > $settings->search_characters_context * 2)
						$context .= "...";
				}*/
				
				$tag_list = "<span class='tags'>";
				foreach($pageindex->{$result["pagename"]}->tags ?? [] as $tag) $tag_list .= "<a href='?action=list-tags&tag=" . rawurlencode($tag) . "' class='mini-tag'>$tag</a>";
				$tag_list .= "</span>\n";
				
				// Make redirect pages italics
				if(!empty($pageindex->{$result["pagename"]}->redirect))
					$result["pagename"] = "<em>{$result["pagename"]}</em>";
				
				// We add 1 to $i here to convert it from an index to a result
				// number as people expect it to start from 1
				$content .= "<div class='search-result' data-result-number='" . ($i + 1) . "' data-rank='" . $result["rank"] . "'>\n";
				$content .= "	<h2><a href='$link'>" . $result["pagename"] . "</a> <span class='search-result-badges'>$tag_list</span></h2>\n";
				$content .= "	<p class='search-context'>$context</p>\n";
				$content .= "</div>\n";
				
				$i++;
			}
			
			$content .= "</section>\n";
			
			header("content-type: text/html; charset=UTF-8");
			exit(page_renderer::render($title, $content));
			
			//header("content-type: text/plain");
			//var_dump($results);
		});
		
/*
 *  ██████  ██    ██ ███████ ██████  ██    ██
 * ██    ██ ██    ██ ██      ██   ██  ██  ██
 * ██    ██ ██    ██ █████   ██████    ████  █████
 * ██ ▄▄ ██ ██    ██ ██      ██   ██    ██
 *  ██████   ██████  ███████ ██   ██    ██
 *     ▀▀
 * ███████ ███████  █████  ██████   ██████ ██   ██ ██ ███    ██ ██████  ███████ ██   ██
 * ██      ██      ██   ██ ██   ██ ██      ██   ██ ██ ████   ██ ██   ██ ██       ██ ██
 * ███████ █████   ███████ ██████  ██      ███████ ██ ██ ██  ██ ██   ██ █████     ███
 *      ██ ██      ██   ██ ██   ██ ██      ██   ██ ██ ██  ██ ██ ██   ██ ██       ██ ██
 * ███████ ███████ ██   ██ ██   ██  ██████ ██   ██ ██ ██   ████ ██████  ███████ ██   ██
 */

		/**
		 * @api {get} ?action=query-searchindex&query={text}	Inspect the internals of the search results for a query
		 * @apiName Search
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	query	The query string to search for.
		 */
		add_action("query-searchindex", function() {
			global $env, $paths;
			
			if(empty($_GET["query"])) {
				http_response_code(400);
				header("content-type: text/plain");
				exit("Error: No query specified. Specify it with the 'query' GET parameter.");
			}
			
			$env->perfdata->searchindex_decode_start = microtime(true);
			$searchIndex = search::load_invindex($paths->searchindex);
			$env->perfdata->searchindex_decode_time = (microtime(true) - $env->perfdata->searchindex_decode_start) * 1000;
			$env->perfdata->searchindex_query_start = microtime(true);
			$searchResults = search::query_invindex($_GET["query"], $searchIndex);
			$env->perfdata->searchindex_query_time = (microtime(true) - $env->perfdata->searchindex_query_start) * 1000;
			
			header("content-type: application/json");
			$result = new stdClass();
			$result->time_format = "ms";
			$result->decode_time = $env->perfdata->searchindex_decode_time;
			$result->query_time = $env->perfdata->searchindex_query_time;
			$result->total_time = $result->decode_time + $result->query_time;
			$result->search_results = $searchResults;
			exit(json_encode($result, JSON_PRETTY_PRINT));
		});
	
/*
 *  ██████  ██████  ███████ ███    ██ ███████ ███████  █████  ██████   ██████ ██   ██
 * ██    ██ ██   ██ ██      ████   ██ ██      ██      ██   ██ ██   ██ ██      ██   ██
 * ██    ██ ██████  █████   ██ ██  ██ ███████ █████   ███████ ██████  ██      ███████
 * ██    ██ ██      ██      ██  ██ ██      ██ ██      ██   ██ ██   ██ ██      ██   ██
 *  ██████  ██      ███████ ██   ████ ███████ ███████ ██   ██ ██   ██  ██████ ██   ██
 */
		/**
		 * @api {get} ?action=opensearch-description	Get the opensearch description file
		 * @apiName OpenSearchDescription
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 */
		add_action("opensearch-description", function () {
			global $settings;
			$siteRoot = full_url() . "/index.php";
			if(!isset($_GET["debug"]))
				header("content-type: application/opensearchdescription+xml");
			else
				header("content-type: text/plain");
			
			exit('<?xml version="1.0" encoding="UTF-8"?' . '>' . // hack The build system strips it otherwise O.o I should really fix that.
"\n<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\">
	<ShortName>Search $settings->sitename</ShortName>
	<Description>Search $settings->sitename, which is powered by Pepperminty Wiki.</Description>
	<Tags>$settings->sitename Wiki</Tags>
	<Image type=\"image/png\">$settings->favicon</Image>
	<Attribution>Search content available under the license linked to at the bottom of the search results page.</Attribution>
	<Developer>Starbeamrainbowlabs (https://github.com/sbrl/Pepperminty-Wiki/graphs/contributors)</Developer>
	<InputEncoding>UTF-8</InputEncoding>
	<OutputEncoding>UTF-8</OutputEncoding>
	
	<Url type=\"text/html\" method=\"get\" template=\"$siteRoot?action=view&amp;search-redirect=yes&amp;page={searchTerms}&amp;offset={startIndex?}&amp;count={count}\" />
	<Url type=\"application/x-suggestions+json\" template=\"$siteRoot?action=suggest-pages&amp;query={searchTerms}&amp;type=opensearch\" />
</OpenSearchDescription>");
		});
		
		
		/**
		 * @api {get} ?action=suggest-pages[&type={type}]	Get page name suggestions for a query
		 * @apiName OpenSearchDescription
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 *
		 * @apiParam	{string}	text	The search query string to get search suggestions for.
		 * @apiParam	{string}	type	The type of result to return. Default value: json. Available values: json, opensearch
		 */
		add_action("suggest-pages", function() {
			global $settings, $pageindex;
			
			if($settings->dynamic_page_suggestion_count === 0)
			{
				header("content-type: application/json");
				header("content-length: 3");
				exit("[]\n");
			}
			
			if(empty($_GET["query"])) {
				http_response_code(400);
				header("content-type: text/plain");
				exit("Error: You didn't specify the 'query' GET parameter.");
			}
			
			$type = $_GET["type"] ?? "json";
			
			if(!in_array($type, ["json", "opensearch"])) {
				http_response_code(406);
				exit("Error: The type '$type' is not one of the supported output types. Available values: json, opensearch. Default: json");
			}
			
			$literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
			
			$query = $literator->transliterate($_GET["query"]);
			
			
			// Rank each page name
			$results = [];
			foreach($pageindex as $pageName => $entry) {
				$results[] = [
					"pagename" => $pageName,
					// Costs: Insert: 1, Replace: 8, Delete: 6
					"distance" => levenshtein($query, $literator->transliterate($pageName), 1, 8, 6)
				];
			}
			
			// Sort the page names by distance from the original query
			usort($results, function($a, $b) {
				if($a["distance"] == $b["distance"])
					return strcmp($a["pagename"], $b["pagename"]);
				return $a["distance"] < $b["distance"] ? -1 : 1;
			});
			
			// Send the results to the user
			$suggestions = array_slice($results, 0, $settings->dynamic_page_suggestion_count);
			switch($type)
			{
				case "json":
					header("content-type: application/json");
					exit(json_encode($suggestions));
				case "opensearch":
					$opensearch_output = [
						$_GET["query"],
						array_map(function($suggestion) { return $suggestion["pagename"]; }, $suggestions)
					];
					header("content-type: application/x-suggestions+json");
					exit(json_encode($opensearch_output));
			}
		});
		
		if($settings->dynamic_page_suggestion_count > 0)
		{
			page_renderer::add_js_snippet('/// Dynamic page suggestion system
// Micro snippet 8 - Promisified GET (fetched 20th Nov 2016)
function get(u){return new Promise(function(r,t,a){a=new XMLHttpRequest();a.onload=function(b,c){b=a.status;c=a.response;if(b>199&&b<300){r(c)}else{t(c)}};a.open("GET",u,true);a.send(null)})}

window.addEventListener("load", function(event) {
	var searchBox = document.querySelector("input[type=search]");
	searchBox.dataset.lastValue = "";
	searchBox.addEventListener("keyup", function(event) {
		// Make sure that we don\'t keep sending requests to the server if nothing has changed
		if(searchBox.dataset.lastValue == event.target.value)
			return;
		searchBox.dataset.lastValue = event.target.value;
		// Fetch the suggestions from the server
		get("?action=suggest-pages&query=" + encodeURIComponent(event.target.value)).then(function(response) {
			var suggestions = JSON.parse(response),
				dataList = document.getElementById("allpages");
			
			// If the server sent no suggestions, then we shouldn\'t replace the contents of the datalist
			if(suggestions.length == 0)
				return;
			
			console.info(`Fetched suggestions for ${event.target.value}:`, suggestions.map(s => s.pagename));
			
			// Remove all the existing suggestions
			while(dataList.firstChild) {
				dataList.removeChild(dataList.firstChild);
			}
			
			// Add the new suggestions to the datalist
			var optionsFrag = document.createDocumentFragment();
			suggestions.forEach(function(suggestion) {
				var suggestionElement = document.createElement("option");
				suggestionElement.value = suggestion.pagename;
				suggestionElement.dataset.distance = suggestion.distance;
				optionsFrag.appendChild(suggestionElement);
			});
			dataList.appendChild(optionsFrag);
		});
	});
});
');
		}
	}
]);

/**
 * Holds a collection to methods to manipulate various types of search index.
 * @package search
 */
class search
{
	/**
	 * Words that we should exclude from the inverted index
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
		"can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de",
		"describe", "detail", "do", "done", "down", "due", "during", "each",
		"eg", "eight", "either", "eleven", "else", "elsewhere", "empty",
		"enough", "etc", "even", "ever", "every", "everyone", "everything",
		"everywhere", "except", "few", "fill", "find",
		"fire", "first", "five", "for", "former", "formerly", "found",
		"four", "from", "front", "full", "further", "get", "give", "go", "had",
		"has", "hasnt", "have", "he", "hence", "her", "here", "hereafter",
		"hereby", "herein", "hereupon", "hers", "herself", "him", "himself",
		"his", "how", "however", "ie", "if", "in", "inc", "indeed",
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
	
	/**
	 * Converts a source string into an index of search terms that can be
	 * merged into an inverted index.
	 * @param  string $source The source string to index.
	 * @return array         An index represents the specified string.
	 */
	public static function index($source)
	{
		// We don't need to normalise or transliterate here because self::tokenize() does this for us
		$source = html_entity_decode($source, ENT_QUOTES);
		$source_length = mb_strlen($source);
		
		$index = [];
		
		$terms = self::tokenize($source, true);
		foreach($terms as $term)
		{
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
	public static function tokenize($source, $capture_offsets = false)
	{
		/** Normalises input characters for searching & indexing */
		static $literator; if($literator == null) $literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
		
		$flags = PREG_SPLIT_NO_EMPTY; // Don't return empty items
		if($capture_offsets)
			$flags |= PREG_SPLIT_OFFSET_CAPTURE;
		
		// We don't need to normalise here because the transliterator handles 
		// this for us. Also, we can't move the literator to a static variable 
		// because PHP doesn't like it very much
		$source = $literator->transliterate($source);
		$source = preg_replace('/[\[\]\|\{\}\/]/u', " ", $source);
		return preg_split("/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))|\|/u", $source, -1, $flags);
	}
	
	/**
	 * Removes (most) markdown markup from the specified string.
	 * Stripped strings are not suitable for indexing!
	 * @param	string	$source	The source string to process.
	 * @return	string			The stripped string.
	 */
	public static function strip_markup($source)
	{
		return preg_replace('/([\"*_\[\]]| - |`)/u', "", $source);
	}
	
	/**
	 * Rebuilds the master inverted index and clears the page id index.
	 * @param	bool	$output	Whether to send progress information to the user's browser.
	 */
	public static function rebuild_invindex($output = true)
	{
		global $pageindex, $env, $paths, $settings;
		
		if($output) {
			header("content-type: text/event-stream");
			ob_end_flush();
		}
		
		// Clear the id index out
		ids::clear();
		
		// Reindex each page in turn
		$invindex = [];
		$i = 0; $max = count(get_object_vars($pageindex));
		$missing_files = 0;
		foreach($pageindex as $pagename => $pagedetails)
		{
			$page_filename = $env->storage_prefix . $pagedetails->filename;
			if(!file_exists($page_filename)) {
				echo("data: [" . ($i + 1) . " / $max] Error: Can't find $page_filename\n");
				flush();
				$i++; $missing_files++;
				continue;
			}
			// We do not transliterate or normalise here because the indexer will take care of this for us
			$index = self::index(file_get_contents($page_filename));
			
			$pageid = ids::getid($pagename);
			self::merge_into_invindex($invindex, $pageid, $index);
			
			if($output) {
				echo("data: [" . ($i + 1) . " / $max] Added $pagename (id #$pageid) to the new search index.\n\n");
				flush();
			}
			
			$i++;
		}
		
		if($output) {
			echo("data: Search index rebuilding complete.\n\n");
			echo("data: Couldn't find $missing_files pages on disk. If $settings->sitename couldn't find some pages on disk, then you might need to manually correct $settings->sitename's page index (stored in pageindex.json).\n\n");
			echo("data: Done! Saving new search index to '$paths->searchindex'.\n\n");
		}
		
		self::save_invindex($paths->searchindex, $invindex);
	}
	
	/**
	 * Sorts an index alphabetically. Will also sort an inverted index.
	 * This allows us to do a binary search instead of a regular
	 * sequential search.
	 * @param	array	$index	The index to sort.
	 */
	public static function sort_index(&$index)
	{
		ksort($index, SORT_NATURAL);
	}
	
	/**
	 * Compares two *regular* indexes to find the differences between them.
	 * @param	array	$oldindex	The old index.
	 * @param	array	$newindex	The new index.
	 * @param	array	$changed	An array to be filled with the nterms of all the changed entries.
	 * @param	array	$removed	An array to be filled with the nterms of all  the removed entries.
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
			if(!isset($oldindex[$nterm]) or // If this word is new
			   $newindex[$nterm] !== $oldindex[$nterm]) // If this word has changed
				$changed[$nterm] = $newindex[$nterm];
		}
	}
	
	/**
	 * Reads in and parses an inverted index.
	 * @param	string	$invindex_filename	The path tp the inverted index to parse.
	 * @todo	Remove this function and make everything streamable
	 */
	public static function load_invindex($invindex_filename) {
		$invindex = json_decode(file_get_contents($invindex_filename), true);
		return $invindex;
	}
	/**
	 * Reads in and parses an inverted index, measuring the time it takes to do so.
	 * @param  string $invindex_filename The path to the file inverted index to parse.
	 * @return boolean Whether the measurement was actually able to take place. Usually this will be true, but it will return false if it can't find the specified index.
	 */
	public static function measure_invindex_load_time($invindex_filename) {
		global $env;
		if(!file_exists($invindex_filename))
			return false;
		$searchindex_decode_start = microtime(true);
		search::load_invindex($invindex_filename);
		$env->perfdata->searchindex_decode_time = round((microtime(true) - $searchindex_decode_start)*1000, 3);
		
		return true;
	}
	
	/**
	 * Merge an index into an inverted index.
	 * @param	array	$invindex	The inverted index to merge into.
	 * @param	int		$pageid		The id of the page to assign to the index that's being merged.
	 * @param	array	$index		The regular index to merge.
	 * @param	array	$removals	An array of index entries to remove from the inverted index. Useful for applying changes to an inverted index instead of deleting and remerging an entire page's index.
	 */
	public static function merge_into_invindex(&$invindex, $pageid, &$index, &$removals = [])
	{
		// Remove all the subentries that were removed since last time
		foreach($removals as $nterm)
			unset($invindex[$nterm][$pageid]);
		
		// Merge all the new / changed index entries into the inverted index
		foreach($index as $nterm => $newentry) {
			// If the nterm isn't in the inverted index, then create a space for it
			if(!isset($invindex[$nterm])) $invindex[$nterm] = [];
			$invindex[$nterm][$pageid] = $newentry;
			
			// Sort the page entries for this word by frequency
			/*
			uasort($invindex[$nterm], function($a, $b) {
				if($a["freq"] == $b["freq"]) return 0;
				return ($a["freq"] < $b["freq"]) ? +1 : -1;
			});
			*/
		}
		
		/*
		// Sort the inverted index by rank
		uasort($invindex, function($a, $b) {
			$ac = count($a); $bc = count($b);
			if($ac == $bc) return 0;
			return ($ac < $bc) ? +1 : -1;
		});
		*/
	}
	
	/**
	 * Deletes the given pageid from the given pageindex.
	 * @param  array	&$invindex	The inverted index.
	 * @param  int		$pageid		The pageid to remove.
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
	
	/**
	 * Saves the given inverted index back to disk.
	 * @param	string	$filename	The path to the file to save the inverted index to.
	 * @param	array	$invindex	The inverted index to save.
	 */
	public static function save_invindex($filename, &$invindex)
	{
		file_put_contents($filename, json_encode($invindex));
	}
	
	/**
	 * Searches the given inverted index for the specified search terms.
	 * @param	string	$query		The search query.
	 * @param	array	$invindex	The inverted index to search.
	 * @return	array	An array of matching pages.
	 */
	public static function query_invindex($query, &$invindex)
	{
		global $settings, $pageindex;
		
		/** Normalises input characters for searching & indexing */
		static $literator; if($literator == null) $literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
		
		$query_terms = self::tokenize($query);
		$matching_pages = [];
		
		
		// Loop over each term in the query and find the matching page entries
		$count = count($query_terms);
		for($i = 0; $i < $count; $i++)
		{
			$qterm = $query_terms[$i];
			
			// Stop words aren't worth the bother - make sure we don't search
			// the title or the tags for them
			if(in_array($qterm, self::$stop_words))
				continue;
			
			// Only search the inverted index if it actually exists there
			if(isset($invindex[$qterm]))
			{
				// Loop over each page in the inverted index entry
				reset($invindex[$qterm]); // Reset array/object pointer
				foreach($invindex[$qterm] as $pageid => $page_entry)
				{
					// Create an entry in the matching pages array if it doesn't exist
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					$matching_pages[$pageid]["nterms"][$qterm] = $page_entry;
				}
			}
			
			
			// Loop over the pageindex and search the titles / tags
			reset($pageindex); // Reset array/object pointer
			foreach ($pageindex as $pagename => $pagedata)
			{
				// Setup a variable to hold the current page's id
				$pageid = false; // Only fill this out if we find a match
				// Consider matches in the page title
				// FUTURE: We may be able to optimise this further by using preg_match_all + preg_quote instead of mb_stripos_all. Experimentation / benchmarking is required to figure out which one is faster
				$title_matches = mb_stripos_all($literator->transliterate($pagename), $qterm);
				$title_matches_count = $title_matches !== false ? count($title_matches) : 0;
				if($title_matches_count > 0)
				{
					$pageid = ids::getid($pagename); // Fill out the page id
					// We found the qterm in the title
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for page title matches if it doesn't exist already
					if(!isset($matching_pages[$pageid]["title-matches"]))
						$matching_pages[$pageid]["title-matches"] = 0;
					
					$matching_pages[$pageid]["title-matches"] += $title_matches_count * strlen($qterm);
				}
				
				// Consider matches in the page's tags
				$tag_matches = isset($pagedata->tags) ? mb_stripos_all($literator->transliterate(implode(" ", $pagedata->tags)), $qterm) : false;
				$tag_matches_count = $tag_matches !== false ? count($tag_matches) : 0;
				
				if($tag_matches_count > 0) // And we found the qterm in the tags
				{
					if($pageid == false) // Fill out the page id if it hasn't been already
						$pageid = ids::getid($pagename);
					
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for tag match if there isn't one already
					if(!isset($matching_pages[$pageid]["tag-matches"]))
						$matching_pages[$pageid]["tag-matches"] = 0;
					$matching_pages[$pageid]["tag-matches"] += $tag_matches_count * strlen($qterm);
				}
			}
		}
		
		reset($matching_pages);
		foreach($matching_pages as $pageid => &$pagedata)
		{
			$pagedata["pagename"] = ids::getpagename($pageid);
			$pagedata["rank"] = 0;
			
			$pageOffsets = [];
			
			// Loop over each search term found on this page
			reset($pagedata["nterms"]);
			foreach($pagedata["nterms"] as $pterm => $entry)
			{
				// Add the number of occurrences of this search term to the ranking
				// Multiply it by the length of the word
				$pagedata["rank"] += $entry["freq"] * strlen($pterm);
				
				// Add the offsets to a listof all offsets on this page
				foreach($entry["offsets"] as $offset)
					$pageOffsets[] = $offset;
			}
			/*
			// Sort the list of offsets
			$pageOffsets = array_unique($pageOffsets);
			sort($pageOffsets);
			var_dump($pageOffsets);
			
			// Calcualate the clump distances via a variable moving window size
			$pageOffsetsCount = count($pageOffsets);
			$clumpDistanceWindow = min($count, $pageOffsetsCount); // a.k.a. count($query_terms) - see above
			$clumpDistances = [];
			for($i = 0; $i < $pageOffsetsCount - $clumpDistanceWindow; $i++)
				$clumpDistances[] = $pageOffsets[$i] - $pageOffsets[$i + $clumpDistanceWindow];
			
			// Sort the new list of clump distances
			sort($clumpDistances);
			// Calcualate a measure of how clumped the offsets are
			$tightClumpLimit = floor((count($clumpDistances) - 1) / 0.25);
			$tightClumpsMeasure = $clumpDistances[$tightClumpLimit] - $clumpDistances[0];
			$clumpsRange = $clumpDistances[count($clumpDistances) - 1] - $clumpDistances[0];
			
			$clumpiness = $tightClumpsMeasure / $clumpsRange;
			echo("{$pagedata["pagename"]} - $clumpiness");
			*/
			
			// Consider matches in the title / tags
			if(isset($pagedata["title-matches"]))
				$pagedata["rank"] += $pagedata["title-matches"] * $settings->search_title_matches_weighting;
			if(isset($pagedata["tag-matches"]))
				$pagedata["rank"] += $pagedata["tag-matches"] * $settings->search_tags_matches_weighting;
			
			// todo remove items if the rank is below a threshold
		}
		
		uasort($matching_pages, function($a, $b) {
			if($a["rank"] == $b["rank"]) return 0;
			return ($a["rank"] < $b["rank"]) ? +1 : -1;
		});
		
		return $matching_pages;
	}
	
	/**
	 * Extracts a context string (in HTML) given a search query that could be displayed
	 * in a list of search results.
	 * @param	string	$invindex	The inverted index to consult.
	 * @param	string	$pagename	The name of the paget that this source belongs to. Used when consulting the inverted index.
	 * @param	string	$query		The search queary to generate the context for.
	 * @param	string	$source		The page source to extract the context from.
	 * @return	string				The generated context string.
	 */
	public static function extract_context($invindex, $pagename, $query, $source)
	{
		global $settings;
		
		$pageid = ids::getid($pagename);
		$nterms = self::tokenize($query);
		$matches = [];
		
		foreach($nterms as $nterm) {
			// Skip over words that don't appear in the inverted index (e.g. stop words)
			if(!isset($invindex[$nterm]))
				continue;
			// Skip if the page isn't found in the inverted index for this word
			if(!isset($invindex[$nterm][$pageid]))
				continue;
			
			foreach($invindex[$nterm][$pageid]["offsets"] as $next_offset)
				$matches[] = [ $nterm, $next_offset ];
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
		
		$result = implode(" … ", $contexts_text);
		end($contexts); // If there's at least one item in the list and were not at the very end of the page, add an extra ellipsis
		if(isset($contexts[0]) && $contexts[key($contexts)]["to"] < $sourceLength) $result .= "… ";
		// Prepend an ellipsis if the context doesn't start at the beginning of a page
		if(isset($contexts[0]) && $contexts[0]["from"] > 0) $result = " …$result";
		
		return $result;
	}
	
	/**
	 * Highlights the keywords of a context string.
	 * @param	string	$query		The query  to use when highlighting.
	 * @param	string	$context	The context string to highlight.
	 * @return	string				The highlighted (HTML) string.
	 */
	public static function highlight_context($query, $context)
	{
		$qterms = self::tokenize($query);
		
		foreach($qterms as $qterm)
		{
			if(in_array($qterm, static::$stop_words))
				continue;
			
			// From http://stackoverflow.com/a/2483859/1460422
			$context = preg_replace("/" . preg_replace('/\\//u', "\/", preg_quote($qterm)) . "/iu", "<strong class='search-term-highlight'>$0</strong>", $context);
		}
		
		return $context;
	}
}

?>
