<?php
register_module([
	"name" => "Search",
	"version" => "0.11",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki using an inverted index to provide a full text search engine. If pages don't show up, then you might have hit a stop word. If not, try requesting the `invindex-rebuild` action to rebuild the inverted index from scratch.",
	"id" => "feature-search",
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
			// todo In the future perhaps a CLI for this would be good?
			if(!file_exists($paths->searchindex))
				search::invindex_rebuild(false);
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			
			$time_start = microtime(true);
			search::invindex_load($paths->searchindex);
			$env->perfdata->invindex_decode_time = round((microtime(true) - $time_start)*1000, 3);
			
			$time_start = microtime(true);
			$results = search::invindex_query($_GET["query"]);
			$resultCount = count($results);
			$env->perfdata->invindex_query_time = round((microtime(true) - $time_start)*1000, 3);
			
			header("x-invindex-load-time: {$env->perfdata->invindex_decode_time}ms");
			header("x-invindex-query-time: {$env->perfdata->invindex_query_time}ms");
			
			$start = microtime(true);
			foreach($results as &$result) {
				$result["context"] = search::extract_context(
					$result["pagename"],
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
				// TODO: Refactor ths to use STAS
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
			search::invindex_load($paths->searchindex);
			$env->perfdata->searchindex_decode_time = (microtime(true) - $env->perfdata->searchindex_decode_start) * 1000;
			$env->perfdata->searchindex_query_start = microtime(true);
			$searchResults = search::invindex_query($_GET["query"]);
			$env->perfdata->searchindex_query_time = (microtime(true) - $env->perfdata->searchindex_query_start) * 1000;
			
			header("content-type: application/json");
			$result = new stdClass();
			$result->time_format = "ms";
			$result->decode_time = $env->perfdata->searchindex_decode_time;
			$result->query_time = $env->perfdata->searchindex_query_time;
			$result->total_time = $result->decode_time + $result->query_time;
			$result->stas = search::stas_parse(search::stas_split($_GET["query"]));
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

/*
███████ ████████  ██████  ██████   █████   ██████  ███████ ██████   ██████  ██   ██
██         ██    ██    ██ ██   ██ ██   ██ ██       ██      ██   ██ ██    ██  ██ ██
███████    ██    ██    ██ ██████  ███████ ██   ███ █████   ██████  ██    ██   ███
     ██    ██    ██    ██ ██   ██ ██   ██ ██    ██ ██      ██   ██ ██    ██  ██ ██
███████    ██     ██████  ██   ██ ██   ██  ██████  ███████ ██████   ██████  ██   ██
*/

/**
 * Represents a key-value data store.
 */
class StorageBox {
	const MODE_JSON = 0;
	const MODE_ARR_SIMPLE = 1;
	
	/**
	 * The SQLite database connection.
	 * @var \PDO
	 */
	private $db;
	
	/**
	 * A cache of values.
	 * @var object[]
	 */
	private $cache = [];
	
	/**
	 * A cache of prepared SQL statements.
	 * @var \PDOStatement[]
	 */
	private $query_cache = [];
	
	/**
	 * Initialises a new store connection.
	 * @param	string	$filename	The filename that the store is located in.
	 */
	function __construct(string $filename) {
		$firstrun = !file_exists($filename);
		$this->db = new \PDO("sqlite:" . path_resolve($filename, __DIR__)); // HACK: This might not work on some systems, because it depends on the current working directory
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if($firstrun) {
			$this->query("CREATE TABLE store (key TEXT UNIQUE NOT NULL, value TEXT)");
		}
	}
	/**
	 * Makes a query against the database.
	 * @param	string	$sql		The (potentially parametised) query to make.
	 * @param	array	$variables	Optional. The variables to substitute into the SQL query.
	 * @return	\PDOStatement		The result of the query, as a PDOStatement.
	 */
	private function query(string $sql, array $variables = []) {
		// Add to the query cache if it doesn't exist
		if(!isset($this->query_cache[$sql]))
			$this->query_cache[$sql] = $this->db->prepare($sql);
		$this->query_cache[$sql]->execute($variables);
		return $this->query_cache[$sql]; // fetchColumn(), fetchAll(), etc. are defined on the statement, not the return value of execute()
	}
	
	/**
	 * Determines if the given key exists in the store or not.
	 * @param	string	$key	The key to test.
	 * @return	bool	Whether the key exists in the store or not.
	 */
	public function has(string $key) : bool {
		if(isset($this->cache[$key]))
			return true;
		return $this->query(
			"SELECT COUNT(key) FROM store WHERE key = :key;",
			[ "key" => $key ]
		)->fetchColumn() > 0;
	}
	
	/**
	 * Gets a value from the store.
	 * @param	string	$key	The key value is stored under.
	 * @return	mixed	The stored value.
	 */
	public function get(string $key) {
		// If it's not in the cache, insert it
		if(!isset($this->cache[$key])) {
			$this->cache[$key] = [ "modified" => false, "value" => json_decode($this->query(
				"SELECT value FROM store WHERE key = :key;",
				[ "key" => $key ]
			)->fetchColumn()) ];
		}
		return $this->cache[$key]["value"];
	}
	public function get_arr_simple(string $key, string $delimiter = "|") {
		// If it's not in the cache, insert it
		if(!isset($this->cache[$key])) {
			$this->cache[$key] = [
				"modified" => false,
				"value" => explode($delimiter, $this->query(
					"SELECT value FROM store WHERE key = :key;",
					[ "key" => $key ]
				)->fetchColumn())
			];
		}
		return $this->cache[$key]["value"];
	}
	
	/**
	 * Sets a value in the data store.
	 * Note that this does NOT save changes to disk until you close the connection!
	 * @param	string	$key	The key to set the value of.
	 * @param	mixed	$value	The value to store.
	 */
	public function set(string $key, $value) : void {
		if(!isset($this->cache[$key])) $this->cache[$key] = [];
		$this->cache[$key]["value"] = $value;
		$this->cache[$key]["modified"] = true;
		$this->cache[$key]["mode"] = self::MODE_JSON;
	}
	public function set_arr_simple(string $key, $value, string $delimiter = "|") : void {
		if(!isset($this->cache[$key])) $this->cache[$key] = [];
		$this->cache[$key]["value"] = $value;
		$this->cache[$key]["modified"] = true;
		$this->cache[$key]["delimiter"] = $delimiter;
		$this->cache[$key]["mode"] = self::MODE_ARR_SIMPLE;
	}
	
	/**
	 * Deletes an item from the data store.
	 * @param	string	$key	The key of the item to delete.
	 * @return	bool	Whether it was really deleted or not. Note that if it doesn't exist, then it can't be deleted.
	 */
	public function delete(string $key) : bool {
		// Remove it from the cache
		if(isset($this->cache[$key]))
			unset($this->cache[$key]);
		// Remove it from disk
		// TODO: Queue this action for the transaction later
		return $this->query(
			"DELETE FROM store WHERE key = :key;",
			[ "key" => $key ]
		)->rowCount() > 0;
	}
	
	/**
	 * Empties the store.
	 */
	public function clear() : void {
		// Empty the cache;
		$this->cache = [];
		// Empty the disk
		$this->query("DELETE FROM store;");
	}
	
	/**
	 * Syncs changes to disk and closes the PDO connection.
	 */
	public function close() : void {
		$this->db->beginTransaction();
		foreach($this->cache as $key => $value_data) {
			// If it wasn't modified, there's no point in saving it, is there?
			if(!$value_data["modified"])
				continue;
			
			$this->query(
				"INSERT OR REPLACE INTO store(key, value) VALUES(:key, :value)",
				[
					"key" => $key,
					"value" => $value_data["mode"] == self::MODE_ARR_SIMPLE ?
						implode($value_data["delimiter"], $value_data["value"]) :
						json_encode($value_data["value"])
				]
			);
		}
		$this->db->commit();
		$this->db = null;
	}
}


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
	 * The transliterator that can be used to transliterate strings.
	 * Transliterated strings are more suitable for use with the search index.
	 * Note that this is no longer wrapped in a function as of v0.21 for 
	 * performance reasons.
	 * @var Transliterator
	 */
	public static $literator = null;
	
	/**
	 * Initialises the search system.
	 * Do not call this function! It is called automatically.
	 */
	public static function init() {
		self::$literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
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
		
		// We don't need to normalise here because the transliterator handles 
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
		
		if($output) {
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
				echo("data: [" . ($i + 1) . " / $max] Error: Can't find $page_filename\n");
				flush();
				$i++; $missing_files++;
				continue;
			}
			// We do not transliterate or normalise here because the indexer will take care of this for us
			$index = self::index_generate(file_get_contents($page_filename));
			
			$pageid = ids::getid($pagename);
			self::invindex_merge($pageid, $index);
			
			if($output) {
				echo("data: [" . ($i + 1) . " / $max] Added $pagename (id #$pageid) to the new search index.\n\n");
				flush();
			}
			
			$i++;
		}
		
		echo("data: Syncing to disk....\n\n");
		self::invindex_close();
		
		if($output) {
			echo("data: Search index rebuilding complete.\n\n");
			echo("data: Couldn't find $missing_files pages on disk. If $settings->sitename couldn't find some pages on disk, then you might need to manually correct $settings->sitename's page index (stored in pageindex.json).\n\n");
			echo("data: Done! Saving new search index to '$paths->searchindex'.\n\n");
		}
		
		// No need to save, it's an SQLite DB backend
	}
	
	/**
	 * Sorts an index alphabetically.
	 * This allows us to do a binary search instead of a regular
	 * sequential search.
	 * @param	array	$index	The index to sort.
	 */
	public static function index_sort(&$index) {
		$sorter = new Collator("");
		uksort($index, function($a, $b) use($sorter) : int {
			return $sorter->compare($a, $b);
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
	 * @param	string	$invindex_filename	The path to the inverted index to load.
	 * @todo	Remove this function and make everything streamable
	 */
	public static function invindex_load(string $invindex_filename) {
		global $env, $paths;
		$start_time = microtime(true);
		self::$invindex = new StorageBox($invindex_filename);
		$env->perfdata->searchindex_load_time = round((microtime(true) - $start_time)*1000, 3);
	}
	
	/**
	 * Closes the currently open inverted index.
	 */
	public static function invindex_close() {
		global $env;
		
		$start_time = microtime(true);
		self::$invindex->close();
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
		error_log(var_export($removals, true));
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
		 */
		// var_dump($tokens);
		$result = [
			"terms" => [],
			"exclude" => [],
			"interwiki" => null
		];
		// foreach($operators as $op)
		// 	$result[$op] = [];
		
		
		$count = count($tokens);
		for($i = count($tokens) - 1; $i >= 0; $i--) {
			// Look for excludes
			if($tokens[$i][0] == "-") {
				if(in_array(substr($tokens[$i], 1), self::$stop_words)) {
					$result["tokens"][] = [
						"term" => substr($tokens[$i], 1),
						"weight" => -1,
						"location" => "all"
					];
				}
				else
					$result["exclude"][] = substr($tokens[$i], 1);
				
				continue;
			}

			// Look for weighted terms
			if($tokens[$i][0] == "+") {
				if(in_array(substr($tokens[$i], 1), self::$stop_words)) {
					$result["tokens"] = [ "term" => substr($tokens[$i], 1), "weight" => -1, "location" => "all" ];
				}
				else {
					$result["terms"][] = [
						"term" => substr($tokens[$i], 1),
						"weight" => 2,
						"location" => "all"
					];
				}
				continue;
			}

			// Look for interwiki searches
			if($tokens[$i][0] == "!" || substr($tokens[$i], -1) == "!") {
				// You can only go to 1 interwiki destination at once, so we replace any previous finding with this one
				$result["interwiki"] = trim($tokens[$i], "!");
			}

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
				if(!isset($result[$parts[0]]))
					$result[$parts[0]] = [];
				
				switch($parts[0]) {
					case "intitle": // BUG: What if a normal word is found in a title?
						$result["terms"][] = [
							"term" => $parts[1],
							"weight" => $settings->search_title_matches_weighting * mb_strlen($parts[1]),
							"location" => "title"
						];
						break;
					case "intags":
						$result["terms"][] = [
							"term" => $parts[1],
							"weight" => $settings->search_tags_matches_weighting * mb_strlen($parts[1]),
							"location" => "tags"
						];
						break;
					case "inbody":
						$result["terms"][] = [
							"term" => $parts[1],
							"weight" => 1,
							"location" => "body"
						];
						break;
					default:
						$result[$parts[0]][] = trim($parts[1], '"');
						break;
				}
				continue;
			}

			// Doesn't appear to be particularly special *shrugs*
			// Set the weight to -1 if it's a stop word
			$result["terms"][] = [
				"term" => $tokens[$i],
				"weight" => in_array($tokens[$i], self::$stop_words) ? -1 : 1,
				"location" => "all"
			];
		}

		return $result;
	}
	
	/**
	 * Searches the given inverted index for the specified search terms.
	 * @param	string	$query		The search query.
	 * @return	array	An array of matching pages.
	 */
	public static function invindex_query($query)
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
				
				// Make sure that the title & tags don't contain a term we should exclude
				$skip = false;
				foreach($query_stas["exclude"] as $excl_term) {
					if(mb_strpos($lit_title, $excl_term) !== false) {
						$skip = true;
						// Delete it from the candidate matches (it might be present in the tags / title but not the body)
						if(isset($matching_pages[$excl_term]))
							unset($matching_pages[$excl_term]);
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
						$pageid = ids::getid($pagename); // Fetch the page id
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
					if($pageid === null) // Fill out the page id if it hasn't been already
						$pageid = ids::getid($pagename);
					
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
	 * @param	string	$query		The search queary to generate the context for.
	 * @param	string	$source		The page source to extract the context from.
	 * @return	string				The generated context string.
	 */
	public static function extract_context($pagename, $query, $source)
	{
		global $settings;
		
		$pageid = ids::getid($pagename);
		$nterms = self::stas_parse(self::stas_split($query))["terms"];
		
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
	 * @param	string	$query		The query  to use when highlighting.
	 * @param	string	$context	The context string to highlight.
	 * @return	string				The highlighted (HTML) string.
	 */
	public static function highlight_context($query, $context)
	{
		$qterms = self::stas_parse(self::stas_split($query))["terms"];
		
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
