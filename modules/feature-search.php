<?php
register_module([
	"name" => "Search",
	"version" => "0.13.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki using an inverted index to provide a full text search engine. If pages don't show up, then you might have hit a stop word. If not, try requesting the `invindex-rebuild` action to rebuild the inverted index from scratch.",
	"id" => "feature-search",
	// After refactoring, we'll need to specify dependencies like this
	"depends" => [ "lib-search-engine" ],
	"code" => function() {
		global $settings, $paths;
		
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
			
			$index = search::index_generate($source);
			
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
				search::invindex_rebuild();
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
			if(!file_exists($paths->searchindex))
				search::invindex_rebuild(false);
				
			// Create the didyoumean index if it doesn't exist.
			if(module_exists("feature-search-didyoumean") && !file_exists($paths->didyoumeanindex))
				search::didyoumean_rebuild(false);
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			
			$time_start = microtime(true);
			search::invindex_load($paths->searchindex);
			$env->perfdata->invindex_decode_time = round((microtime(true) - $time_start)*1000, 3);
			
			$time_start = microtime(true);
			$query_parsed = null;
			$results = search::invindex_query($_GET["query"], $query_parsed);
			$resultCount = count($results);
			$env->perfdata->invindex_query_time = round((microtime(true) - $time_start)*1000, 3);
			
			header("x-invindex-load-time: {$env->perfdata->invindex_decode_time}ms");
			header("x-invindex-query-time: {$env->perfdata->invindex_query_time}ms");
			
			$start = microtime(true);
			// FUTURE: When we implement $_GET["offset"] and $_GET["count"] or something we can optimise here
			foreach($results as $key => &$result) {
				$filepath = $env->storage_prefix . $result["pagename"] . ".md";
				if(!file_exists($filepath)) {
					error_log("[PeppermintyWiki/$settings->sitename/search] Search engine returned {$result["pagename"]} as a result (maps to $filepath), but it doesn't exist on disk (try rebuilding the search index).");
					continue; // Something strange is happening
				}
				$result["context"] = search::extract_context(
					$result["pagename"],
					$query_parsed,
					file_get_contents($filepath)
				);
			}
			// This is absolutely *essential*, because otherwise we hit a very strange bug whereby PHP duplicates the value of the last iterated search result. Ref https://bugs.php.net/bug.php?id=70387 - apparently "documented behaviour"
			unset($result);
			$env->perfdata->context_generation_time = round((microtime(true) - $start)*1000, 3);
			header("x-context-generation-time: {$env->perfdata->context_generation_time}ms");
			
			$env->perfdata->search_time = round((microtime(true) - $search_start)*1000, 3);
			header("x-search-time: {$env->perfdata->search_time}ms");
			
			if(!empty($_GET["format"]) && $_GET["format"] == "json") {
				header("content-type: application/json");
				$json_results = new stdClass();
				foreach($results as $key => $result)
					$json_results->{$result["pagename"]} = $result;
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
			if(isset($pageindex->$query)) {
				$content .= "There's a page on $settings->sitename called <a href='?page=" . rawurlencode($query) . "'>$query</a>.";
			}
			else
			{
				$content .= "There isn't a page called $query on $settings->sitename, but you ";
				if((!$settings->anonedits && !$env->is_logged_in) || !$settings->editing) {
					$content .= "do not have permission to create it.";
					if(!$env->is_logged_in) {
						$content .= " You could try <a href='?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.";
					}
				}
				else {
					$content .= "can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.";
				}
			}
			$content .= "<br /><small><em>Pssst! Power users can make use of $settings->sitename's advanced query syntax. Learn about it <a href='?action=help#27-search'>here</a>!</em></small></p>";
			
			if(module_exists("page-list")) {
				// TODO: Refactor this to use STAS
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
					$query_parsed,
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
			search::invindex_load($paths->searchindex);
			$env->perfdata->searchindex_decode_time = (microtime(true) - $env->perfdata->searchindex_decode_start) * 1000;
			$env->perfdata->searchindex_query_start = microtime(true);
			$query_stas = null;
			$searchResults = search::invindex_query($_GET["query"], $query_stas);
			$env->perfdata->searchindex_query_time = (microtime(true) - $env->perfdata->searchindex_query_start) * 1000;
			
			header("content-type: application/json");
			$result = new stdClass();
			$result->time_format = "ms";
			$result->decode_time = $env->perfdata->searchindex_decode_time;
			$result->query_time = $env->perfdata->searchindex_query_time;
			if(isset($env->perfdata->didyoumean_correction))
				$result->didyoumean_correction_time = $env->perfdata->didyoumean_correction;
			$result->total_time = $result->decode_time + $result->query_time;
			// $result->stas = search::stas_parse(search::stas_split($_GET["query"]));
			$result->stas = $query_stas;
			$result->search_results = $searchResults;
			exit(json_encode($result, JSON_PRETTY_PRINT));
		});
		
		/**
		 * @api {get} ?action=stas-parse&query={text}	Debug search queries
		 * @apiDescription Debug Pepperminty Wiki's understanding of search queries.
		 * If you want something machine-readable, check out the new stas property on the object returned by query-searchindex.
		 * @apiName SearchSTASParse
		 * @apiGroup Search
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	query	The query string to parse.
		 */
		add_action("stas-parse", function() {
			global $settings;
			
			if(!isset($_GET["query"])) {
				http_response_code(400);
				header("x-status: failed");
				header("x-problem: no-query-specified");
				exit(page_renderer::render_main("Error - STAS Query Analysis - $settings->sitename", "<p>No query was present in the <code>query</code> GET parameter.</p>"));
			}
			
			$tokens = search::stas_split($_GET["query"]);
			$stas_query = search::stas_parse($tokens);
			
			$result = "";
			foreach($tokens as $token) {
				if(in_array(substr($token, 1), $stas_query["exclude"])) {
					$result .= "<span title='explicit exclude' style='color: red; text-decoration: dotted line-through;'>" . substr($token, 1) . "</span> ";
					continue;
				}
				
				$term = null;
				$token_part = $token;
				if($token_part[0] == "+") $token_part = substr($token_part, 1);
				if(strpos($token_part, ":") !== false) $token_part = explode(":", $token_part, 2)[1];
				foreach($stas_query["terms"] as $c_term) {
					// echo(var_export($token_part, true) . " / {$c_term["term"]}\n");
					if($c_term["term"] == $token_part) {
						$term = $c_term;
						break;
					}
				}
				if($term == null) {
					$result .= "<span title='unknown' style='color: black; text-decoration: wavy underline;'>$token</span> ";
					continue;
				}
				
				$title = "?";
				$style = "";
				switch($term["weight"]) {
					case -1: $style .= "color: grey; text-decoration: wavy line-through;"; $title = "stop word"; break;
					case 1: $style .= "color: blue;"; $title = "normal word"; break;
				}
				if($term["weight"] > 1) {
					$style .= "color: darkblue; font-weight: bold;";
					$title = "weighted word";
				}
				if($term["weight"] !== -1) {
					switch($term["location"]) {
						case "body": $style = "color: cyan"; $title = "body only"; break;
						case "title": $style .= "font-weight: bolder; font-size: 1.2em; color: orange;"; $title = "searching title only"; $token = $token_part; break;
						case "tags": $style .= "font-weight: bolder; color: purple;"; $title = "searching tags only"; $token = $token_part; break;
						case "all": $title .= ", searching everywhere";
					}
				}
				$title .= ", weight: {$term["weight"]}";
				
				$result .= "<span title='$title' style='$style'>$token</span> ";
			}
			
			exit(page_renderer::render_main("STAS Query Analysis - $settings->sitename", "<p>$settings->sitename understood your query to mean the following:</p>
				<blockquote>$result</blockquote>"));
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
			
			if($settings->dynamic_page_suggestion_count === 0) {
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
			
			$query = search::$literator->transliterate($_GET["query"]);
			
			// Rank each page name
			$results = [];
			foreach($pageindex as $pageName => $entry) {
				$results[] = [
					"pagename" => $pageName,
					// Costs: Insert: 1, Replace: 8, Delete: 6
					"distance" => levenshtein($query, search::$literator->transliterate($pageName), 1, 8, 6)
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
		
		if(module_exists("feature-cli")) {
			cli_register("search", "Query and manipulate the search index", function(array $args) : int {
				if(count($args) < 1) {
					echo("search: query and manipulate the search index
Usage:
    search {subcommand} 

Subcommands:
    rebuild     Rebuilds the search index
");
					return 0;
				}
				
				switch($args[0]) {
					case "rebuild":
						search::invindex_rebuild();
						break;
				}
				
				return 0;
			});
		}
		
		add_help_section("27-search", "Searching", "<p>$settings->sitename has an integrated full-text search engine, allowing you to search all of the pages on $settings->sitename and their content. To use it, simply enter your query into the page name box and press enter. If a page isn't found with the exact name of your query terms, a search will be performed instead.</p>
		<p>Additionally, advanced users can take advantage of some extra query syntax that $settings->sitename supports, which is inspired by popular search engines:</p>
		<table>
		<tr><th style='width: 33%;'>Example</th><th style='width: 66%;'>Meaning</th></tr>
		<tr><td><code>cat -dog</code></td><td>Search for pages containing \"cat\", but not \"dog\". This syntax does not make sense on it's own - other words must be present for it to take effect.</td>
		<tr><td><code>+glass marble</code></td><td>Double the weighting of the word \"glass\".</td>
		<tr><td><code>intitle:rocket</code></td><td>Search only page titles for \"rocket\".</td>
		<tr><td><code>intags:bill</code></td><td>Search only tags for \"bill\".</td>
		<tr><td><code>inbody:satellite</code></td><td>Search only the page body for \"satellite\".</td>
		</table>
		<p>More query syntax will be added in the future, so keep an eye on <a href='https://github.com/sbrl/Pepperminty-Wiki/releases/'>the latest releases</a> of <em>Pepperminty Wiki</em> to stay up-to-date (<a href='https://github.com/sbrl/Pepperminty-Wiki/releases.atom'>Atom / RSS feed available here</a>).</p>");
	}
]);

?>
