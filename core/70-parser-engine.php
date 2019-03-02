<?php


$parsers = [
	"none" => [
		"parser" => function() {
			throw new Exception("No parser registered!");
		},
		"hash_generator" => function() {
			throw new Exception("No parser registered!");
		}
	]
];
/**
 * Registers a new parser.
 * @package	core
 * @param	string		$name			The name of the new parser to register.
 * @param	callable	$parser_code	The function to register as a new parser.
 * @param	callable	$hash_generator	A function that should take a single argument of the input source text, and return a unique hash for that content. The return value is used as the filename for cache entries, so should be safe to use as such.
 */
function add_parser($name, $parser_code, $hash_generator) {
	global $parsers;
	if(isset($parsers[$name]))
		throw new Exception("Can't register parser with name '$name' because a parser with that name already exists.");

	$parsers[$name] = [
		"parser" => $parser_code,
		"hash_generator" => $hash_generator
	];
}
/**
 * Parses the specified page source using the parser specified in the settings
 * into HTML.
 * The specified parser may (though it's unlikely) render it to other things.
 * @package core
 * @param	string	$source		The source to render.
 * @param	bool	$use_cache	Whether to use the on-disk cache. Has no effect if parser caching is disabled in peppermint.json, or the source string is too small.
 * @param	bool	$untrusted	Whether the source string is 'untrusted' - i.e. a user comment. Untrusted source disallows HTML and protects against XSS attacks.
 * @return	string	The source rendered to HTML.
 */
function parse_page_source($source, $untrusted = false, $use_cache = true) {
	global $settings, $paths, $parsers, $version;
	$start_time = microtime(true);
	
	if(!$settings->parser_cache || strlen($source) < $settings->parser_cache_min_size) $use_cache = false;
	
	if(!isset($parsers[$settings->parser]))
		exit(page_renderer::render_main("Parsing error - $settings->sitename", "<p>Parsing some page source data failed. This is most likely because $settings->sitename has the parser setting set incorrectly. Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>" . $settings->admindetails_name . "</a>, your $settings->sitename Administrator."));
	
/* Not needed atm because escaping happens when saving, not when rendering *
	if($settings->clean_raw_html)
		$source = htmlentities($source, ENT_QUOTES | ENT_HTML5);
*/
	
	$cache_id = $parsers[$settings->parser]["hash_generator"]($source);
	$cache_file = "{$paths->cache_directory}/{$cache_id}.html";
	
	$result = null;
	if($use_cache && file_exists($cache_file)) {
		$result = file_get_contents($cache_file);
		$result .= "\n<!-- cache: hit, id: $cache_id, took: " . round((microtime(true) - $start_time)*1000, 5) . "ms -->\n";
	}
	if($result == null) {
		$result = $parsers[$settings->parser]["parser"]($source, $untrusted);
		// If we should use the cache and we failed to write to it, warn the admin.
		// It's not terribible if we can't write to the cache directory (so we shouldn't stop dead & refuse service), but it's still of concern.
		if($use_cache && !file_put_contents($cache_file, $result))
			error_log("[Pepperminty Wiki] Warning: Failed to write to cache file $cache_file.");
		
		$result .= "\n<!-- cache: " . ($use_cache ? "miss" : "n/a") . ", id: $cache_id, took: " . round((microtime(true) - $start_time)*1000, 5) . "ms -->\n";
	}
	
	return $result;
}
