<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


/**
 * Get the actual absolute origin of the request sent by the user.
 * @package core
 * @param  array	$s						The $_SERVER variable contents. Defaults to $_SERVER.
 * @param  bool		$use_forwarded_host		Whether to utilise the X-Forwarded-Host header when calculating the actual origin.
 * @return string							The actual origin of the user's request.
 */
function url_origin( $s = false, $use_forwarded_host = false )
{
	global $env;
	if($s === false) $s = $_SERVER;
	$ssl      = $env->is_secure;
	$sp       = strtolower( $s['SERVER_PROTOCOL'] );
	$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
	$port     = $s['SERVER_PORT'];
	$port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
	$host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
	$host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
	return $protocol . '://' . $host;
}

/**
 * Get the full url, as requested by the client.
 * @package core
 * @see		http://stackoverflow.com/a/8891890/1460422	This Stackoverflow answer.
 * @param	array	$s                  The $_SERVER variable. Defaults to $_SERVER.
 * @param	bool		$use_forwarded_host Whether to take the X-Forwarded-Host header into account.
 * @return	string						The full url, as requested by the client.
 */
function full_url($s = false, $use_forwarded_host = false) {
	if($s == false) $s = $_SERVER;
	return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}

/**
 * Get the stem URL at which this Pepperminty Wiki instance is located
 * You can just append ?get_params_here to this and it will be a valid URL.
 * Uses full_url() under the hood.
 * Note that this is based on the URL of the current request.
 * @param	array	$s					The $_SERVER variable (defaults to $_SERVER)
 * @param	bool	$use_forwarded_host	Whether to use the x-forwarded-host header or ignore it.
 * @return	string	The stem url, as described above
 */
function url_stem( $s = false, bool $use_forwarded_host = false) : string {
	// Calculate the stem from the current full URL by stripping everything after the question mark ('?')
	$url_stem = full_url();
	if(mb_strpos($url_stem, "?") !== false) $url_stem = mb_substr($url_stem, 0, mb_strpos($url_stem, "?"));
	return $url_stem;
}

/**
 * Converts a filesize into a human-readable string.
 * @package core
 * @see	http://php.net/manual/en/function.filesize.php#106569	The original source
 * @author	rommel
 * @author	Edited by Starbeamrainbowlabs
 * @param	int		$bytes		The number of bytes to convert.
 * @param	int		$decimals	The number of decimal places to preserve.
 * @return 	string				A human-readable filesize.
 */
function human_filesize($bytes, $decimals = 2)
{
	$sz = ["b", "kib", "mib", "gib", "tib", "pib", "eib", "yib", "zib"];
	$factor = floor((strlen($bytes) - 1) / 3);
	$result = round($bytes / pow(1024, $factor), $decimals);
	return $result . @$sz[$factor];
}

/**
 * Calculates the time since a particular timestamp and returns a
 * human-readable result.
 * @package core
 * @see http://goo.gl/zpgLgq The original source. No longer exists, maybe the wayback machine caught it :-(
 * @param	int		$time	The timestamp to convert.
 * @return	string	The time since the given timestamp as a human-readable string.
 */
function human_time_since($time)
{
	return human_time(time() - $time);
}
/**
 * Renders a given number of seconds as something that humans can understand more easily.
 * @package core
 * @param 	int		$seconds	The number of seconds to render.
 * @return	string	The rendered time.
 */
function human_time($seconds)
{
	$tokens = array (
		31536000 => 'year',
		2592000 => 'month',
		604800 => 'week',
		86400 => 'day',
		3600 => 'hour',
		60 => 'minute',
		1 => 'second'
	);
	foreach ($tokens as $unit => $text) {
		if ($seconds < $unit) continue;
		$numberOfUnits = floor($seconds / $unit);
		return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').' ago';
	}
}

/**
 * A recursive glob() function.
 * @package core
 * @see http://in.php.net/manual/en/function.glob.php#106595	The original source
 * @author	Mike
 * @param	string	$pattern	The glob pattern to use to find filenames.
 * @param	int		$flags		The glob flags to use when finding filenames.
 * @return	array	An array of the filepaths that match the given glob.
 */
function glob_recursive($pattern, $flags = 0)
{
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
	{
		$prefix = "$dir/";
		// Remove the "./" from the beginning if it exists
		if(substr($prefix, 0, 2) == "./") $prefix = substr($prefix, 2);
		$files = array_merge($files, glob_recursive($prefix . basename($pattern), $flags));
	}
	return $files;
}

/**
 * Normalize file name & path.
 * Used to convert filenames returned by glob_recursive() to a format used in pageindex.
 *
 * @package core
 * @author	Alx84
 * @param	string	$filename	A filename with storage prefix as retuned by glob_recursive()
 * @return	string	Normalized filename
 */
function normalize_filename($filename)
{
	global $env;
	// glob_recursive() returns values like "./storage_prefix/folder/filename.md"
	// in the pageindex we save them as "folder/filename.md"
	$result = mb_substr( // Store the filename, whilst trimming the storage prefix
		$filename,
		mb_strlen(preg_replace("/^\.\//iu", "", $env->storage_prefix)) // glob_recursive trim the ./ from returned filenames , so we need to as well
	);
	// Remove the `./` from the beginning if it's still hanging around
	if(mb_substr($result, 0, 2) == "./")
		$result = mb_substr($result, 2);

	return $result;
}

/**
 * Resolves a relative path against a given base directory.
 * @since 0.20.0
 * @source	https://stackoverflow.com/a/44312137/1460422
 * @param	string		$path		The relative path to resolve.
 * @param	string|null	$basePath	The base directory to resolve against.
 * @return	string		An absolute path.
 */
function path_resolve(string $path, string $basePath = null) {
	// Make absolute path
	if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR) {
		if ($basePath === null) {
			// Get PWD first to avoid getcwd() resolving symlinks if in symlinked folder
			$path=(getenv('PWD') ?: getcwd()).DIRECTORY_SEPARATOR.$path;
		} elseif (strlen($basePath)) {
			$path=$basePath.DIRECTORY_SEPARATOR.$path;
		}
	}

	// Resolve '.' and '..'
	$components=array();
	foreach(explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR)) as $name) {
		if ($name === '..') {
			array_pop($components);
		} elseif ($name !== '.' && !(count($components) && $name === '')) {
			// … && !(count($components) && $name === '') - we want to keep initial '/' for abs paths
			$components[]=$name;
		}
	}

	return implode(DIRECTORY_SEPARATOR, $components);
}

/**
 * Determines if a directory is empty or not.
 * @ref https://stackoverflow.com/a/7497848/1460422
 * @param	string	$dir	The path to the directory to check.
 * @return	boolean	True if the directory is empty, or false if it is not empty.
 */
function is_directory_empty(string $dir) : bool {
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			closedir($handle);
			return false;
		}
	}
	closedir($handle);
	return true;
}

/**
 * Converts a filepath to a page name.
 * @param  string $filepath The filepath to convert.
 * @return string           The extracted pagename.
 */
function filepath_to_pagename(string $filepath) : string {
	global $env;
	// Strip the storage prefix, but only if it isn't a dot
	if(starts_with($filepath, $env->storage_prefix) && $env->storage_prefix !== ".")
		$filepath = mb_substr($filepath, mb_strlen($env->storage_prefix));
	
	// If a revision number is detected, strip it
	if(preg_match("/\.r[0-9]+$/", $filepath) > 0)
		$filepath = mb_substr($filepath, 0, mb_strrpos($filepath, ".r"));
	
	// Strip the .md file extension
	if(ends_with($filepath, ".md"))
		$filepath = mb_substr($filepath, 0, -3);
	
	return $filepath;
}

/**
 * Gets the name of the parent page to the specified page.
 * @since 0.15.0
 * @package core
 * @param  string		$pagename	The child page to get the parent
 * 									page name for.
 * @return string|bool
 */
function get_page_parent($pagename) {
	if(mb_strpos($pagename, "/") === false)
		return false;
	return mb_substr($pagename, 0, mb_strrpos($pagename, "/"));
}

/**
 * Gets a list of all the sub pages of the current page.
 * @package core
 * @param	object	$pageindex	The pageindex to use to search.
 * @param	string	$pagename	The name of the page to list the sub pages of.
 * @return	object				An object containing all the subpages and their
 *     respective distances from the given page name in the pageindex tree.
 */
function get_subpages($pageindex, $pagename)
{
	$pagenames = get_object_vars($pageindex);
	$result = new stdClass();

	$stem = "$pagename/";
	$stem_length = strlen($stem);
	foreach($pagenames as $entry => $value)
	{
		if(substr($entry, 0, $stem_length) == $stem)
		{
			// We found a subpage

			// Extract the subpage's key relative to the page that we are searching for
			$subpage_relative_key = substr($entry, $stem_length, -3);
			// Calculate how many times removed the current subpage is from the current page. 0 = direct descendant.
			$times_removed = substr_count($subpage_relative_key, "/");
			// Store the name of the subpage we found
			$result->$entry = $times_removed;
		}
	}

	unset($pagenames);
	return $result;
}

/**
 * Makes sure that a subpage's parents exist.
 * Note this doesn't check the pagename itself.
 * @package	core
 * @param	string	$pagename	The pagename to check.
 * @param	bool	$create_dir	Whether to create an associated directory for subpages or not.
 */
function check_subpage_parents(string $pagename, bool $create_dir = true)
{
	global $pageindex, $paths, $env;
	// Save the new pageindex and return if there aren't any more parent pages to check
	if(strpos($pagename, "/") === false)
		return save_pageindex();
	
	$pagename = makepathsafe($pagename); // Just in case
	
	$parent_pagename = substr($pagename, 0, strrpos($pagename, "/"));
	$parent_page_filename = "$parent_pagename.md";
	if(!file_exists($env->storage_prefix . $parent_page_filename))
	{
		// This parent page doesn't exist! Create it and add it to the page index.
		touch($env->storage_prefix . $parent_page_filename, 0);
		
		$newentry = new stdClass();
		$newentry->filename = $parent_page_filename;
		$newentry->size = 0;
		$newentry->lastmodified = 0;
		$newentry->lasteditor = "none";
		$pageindex->$parent_pagename = $newentry;
	}
	if($create_dir) {
		$dirname = $env->storage_prefix . $parent_pagename;
		if(!file_exists($dirname))
			mkdir($dirname, 0755, true);
	}

	check_subpage_parents($parent_pagename, $create_dir);
}

/**
 * Makes a path (or page name) safe.
 * A safe path / page name may not contain:
	* Forward-slashes at the beginning
	* Multiple dots in a row
	* Odd characters (e.g. ?%*:|"<>() etc.)
 * A safe path may, however, contain unicode characters such as éôà etc.
 * @package core
 * @param	string	$string	The string to make safe.
 * @return	string			A safe version of the given string.
 */
function makepathsafe($string)
{
	// Old restrictive system
	//$string = preg_replace("/[^0-9a-zA-Z\_\-\ \/\.]/i", "", $string);
	// Remove reserved characters
	$string = preg_replace("/[?%*:|\"><()\\[\\]]/i", "", $string);
	// Collapse multiple dots into a single dot
	$string = preg_replace("/\.+/", ".", $string);
	// Don't allow slashes at the beginning
	$string = ltrim($string, "\\/");
	// Don't allow dots on their own
	$string = preg_replace(["/^\.\\/|\\/\.$/", "/\\/\.\\//"], ["", "/"], $string);
	return $string;
}

/**
 * Slugifies a given string such that it can only contain a-z0-9-_.
 * Also automatically makes it lowercase.
 * @param	string	$text	The text to operate on.
 * @return	string	The slugified string.
 */
function slugify(string $text) : string {
	return preg_replace("/[^a-zA-Z0-9\-_]/", "", $text);
}

/**
 * Hides an email address from bots. Returns a fragment of HTML that contains the mangled email address.
 * @package core
 * @param	string	$str			The original email address
 * @param	string	$display_text	The display text for the resulting HTML - if null then the original email address is used. Note that because it's base64 encoded and then textContent is used, one does not need to run either htmlentities() or rawurlencode() over this value as it's completely safe.
 * @return	string	The mangled email address as a fragment of HTML.
 */
function hide_email(string $email, string $display_text = null) : string
{
	$enc = json_encode([ $email, $display_text ]);
	$len = strlen($enc);
	$pool = []; for($i = 0; $i < $len; $i++) $pool[] = $i;
	$a = []; $b = [];
	for($i = 0; $i < $len; $i++) {
		$n = random_int(0, $len - $i - 1);
		$j = array_splice($pool, $n, 1)[0]; $b[] = $j;
		// echo("chose ".$enc[$j].", index $j, n $n\n");
		$a[] = $enc[$j];
	}
	$a = base64_encode(implode("|", $a));
	$b = base64_encode(implode("|", $b));
	$span_id = "he-".crypto_id(16);
	return "<a href='#protected-with-javascript' id='$span_id'>[protected with javascript]</a><script>(() => {let c=\"$a|$b\".split('|').map(atob).map(s=>s.split('|'));let d=[],e=document.getElementById('$span_id');c[1].map((n,i)=>d[parseInt(n)]=c[0][i]);d=JSON.parse(d.join(''));e.textContent=d[1]==null?d[0]:d[1];e.setAttribute('href', 'mailto:'+d[0])})();</script>";
}

/**
 * Checks to see if $haystack starts with $needle.
 * @package	core
 * @param	string	$haystack	The string to search.
 * @param	string	$needle		The string to search for at the beginning
 *                        		of $haystack.
 * @return	bool	Whether $needle can be found at the beginning of $haystack.
 */
function starts_with(string $haystack, string $needle) : bool {
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}
/**
 * Checks to see if $hackstack ends with $needle.
 * The matching bookend to starts_with.
 * @package	core
 * @param	string	$haystack	The haystack to search..
 * @param	string	$needle		The needle to look for.
 * @return	bool
 */
function ends_with(string $haystack, string $needle) : bool {
	$length = strlen($needle);
	return (substr($haystack, -$length) === $needle);
}

/**
 * Case-insensitively finds all occurrences of $needle in $haystack. Handles
 * UTF-8 characters correctly.
 * @package core
 * @see	http://www.pontikis.net/tip/?id=16 the source
 * @see	http://www.php.net/manual/en/function.strpos.php#87061	the source that the above was based on
 * @param	string			$haystack	The string to search.
 * @param	string			$needle		The string to find.
 * @return	array|false					An array of match indices, or false if
 *                  					nothing was found.
 */
function mb_stripos_all($haystack, $needle) {
	$s = 0; $i = 0;
	while(is_integer($i)) {
		$i = mb_stripos($haystack, $needle, $s);
		if(is_integer($i)) {
			$aStrPos[] = $i;
			$s = $i + (function_exists("mb_strlen") ? mb_strlen($needle) : strlen($needle));
		}
	}
	if(isset($aStrPos))
		return $aStrPos;
	else
		return false;
}

/**
 * Tests whether a string starts with a specified substring.
 * @package core
 * @param 	string	$haystack	The string to check against.
 * @param 	string	$needle		The substring to look for.
 * @return	bool				Whether the string starts with the specified substring.
 */
function startsWith($haystack, $needle) {
	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
/**
 * Tests whether a string ends with a given substring.
 * @package core
 * @param  string $whole The string to test against.
 * @param  string $end   The substring test for.
 * @return bool          Whether $whole ends in $end.
 */
function endsWith($whole, $end) {
	return (strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
}
/**
 * Replaces the first occurrence of $find with $replace.
 * @package core
 * @param  string $find    The string to search for.
 * @param  string $replace The string to replace the search string with.
 * @param  string $subject The string ot perform the search and replace on.
 * @return string		   The source string after the find and replace has been performed.
 */
function str_replace_once($find, $replace, $subject) {
	$index = strpos($subject, $find);
	if($index !== false)
		return substr_replace($subject, $replace, $index, strlen($find));
	return $subject;
}

/**
 * Returns the system's mime type mappings, considering the first extension
 * listed to be cacnonical.
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
 * @return array	An array of mime type mappings.
 */
function system_mime_type_extensions() {
	global $settings;
	$out = array();
	$file = fopen($settings->mime_extension_mappings_location, 'r');
	while(($line = fgets($file)) !== false) {
		$line = trim(preg_replace('/#.*/', '', $line));
		if(!$line)
			continue;
		$parts = preg_split('/\s+/', $line);
		if(count($parts) == 1)
			continue;
		$type = array_shift($parts);
		if(!isset($out[$type]))
			$out[$type] = array_shift($parts);
	}
	fclose($file);
	return $out;
}

/**
 * Converts a given mime type to it's associated file extension.
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
 * @param  string $type The mime type to convert.
 * @return string       The extension for the given mime type.
 */
function system_mime_type_extension($type) {
	static $exts;
	if(!isset($exts))
		$exts = system_mime_type_extensions();
	return isset($exts[$type]) ? $exts[$type] : null;
}

/**
 * Returns the system MIME type mapping of extensions to MIME types.
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
 * @return array An array mapping file extensions to their associated mime types.
 */
function system_extension_mime_types() {
	global $settings;
	$out = array();
	$file = fopen($settings->mime_extension_mappings_location, 'r');
	while(($line = fgets($file)) !== false) {
		$line = trim(preg_replace('/#.*/', '', $line));
		if(!$line)
			continue;
		$parts = preg_split('/\s+/', $line);
		if(count($parts) == 1)
			continue;
		$type = array_shift($parts);
		foreach($parts as $part)
			$out[$part] = $type;
	}
	fclose($file);
	return $out;
}
/**
 * Converts a given file extension to it's associated mime type.
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
 * @param  string $ext The extension to convert.
 * @return string      The mime type associated with the given extension.
 */
function system_extension_mime_type($ext) {
	static $types;
	if(!isset($types))
		$types = system_extension_mime_types();
	$ext = strtolower($ext);
	return isset($types[$ext]) ? $types[$ext] : null;
}

/**
 * Creates an images containing the specified text.
 * Useful for sending errors back to the client.
 * @package core
 * @param	string	$text			The text to include in the image.
 * @param	int		$target_size	The target width to aim for when creating 
 * 									the image. Not not specified, a value is 
 * 									determined automatically.
 * @return	resource				The handle to the generated GD image.
 */
function errorimage($text, $target_size = null)
{
	$width = 0;
	$height = 0;
	$border_size = 10; // in px, if $target_size isn't null has no effect
	$line_spacing = 2; // in px
	$font_size = 5; // 1 - 5
	
	$font_width = imagefontwidth($font_size);	// in px
	$font_height = imagefontheight($font_size);	// in px
	$text_lines = array_map("trim", explode("\n", $text));
	
	if(!empty($target_size)) {
		$width = $target_size;
		$height = $target_size * (2 / 3);
	}
	else {
		$height = count($text_lines) * $font_height + 
			(count($text_lines) - 1) * $line_spacing +
			$border_size * 2;
		foreach($text_lines as $line)
			$width = max($width, $font_width * mb_strlen($line));
		$width += $border_size * 2;
	}
	
	$image = imagecreatetruecolor($width, $height);
	imagefill($image, 0, 0, imagecolorallocate($image, 250, 249, 251)); // Set the background to #faf8fb
	
	$i = 0;
	foreach($text_lines as $line) {
		imagestring($image, $font_size,
			($width / 2) - (($font_width * mb_strlen($line)) / 2),
			$border_size + $i * ($font_height + $line_spacing),
			$line,
			imagecolorallocate($image, 68, 39, 113) // #442772
		);
		$i++;	
	}
	
	return $image;
}

/**
 * Generates a stack trace.
 * @package core
 * @param	bool	$log_trace	Whether to send the stack trace to the error log.
 * @param	bool	$full		Whether to output a full description of all the variables involved.
 * @return	string				A string prepresentation of a stack trace.
 */
function stack_trace($log_trace = true, $full = false)
{
	$result = "";
	$stackTrace = debug_backtrace();
	$stackHeight = count($stackTrace);
	foreach ($stackTrace as $i => $stackEntry)
	{
		$result .= "#" . ($stackHeight - $i) . ": ";
		$result .= (isset($stackEntry["file"]) ? $stackEntry["file"] : "(unknown file)") . ":" . (isset($stackEntry["line"]) ? $stackEntry["line"] : "(unknown line)") . " - ";
		if(isset($stackEntry["function"]))
		{
			$result .= "(calling " . $stackEntry["function"];
			if(isset($stackEntry["args"]) && count($stackEntry["args"]))
			{
				$result .= ": ";
				$result .= implode(", ", array_map($full ? "var_dump_ret" : "var_dump_short", $stackEntry["args"]));
			}
		}
		$result .= ")\n";
	}
	if($log_trace)
		error_log($result);
	return $result;
}
/**
 * Calls var_dump() and returns the output.
 * @package core
 * @param	mixed	$var	The thing to pass to var_dump().
 * @return	string			The output captured from var_dump().
 */
function var_dump_ret($var)
{
	ob_start();
	var_dump($var);
	return ob_get_clean();
}

/**
 * Calls var_dump(), shortening the output for various types.
 * @package core
 * @param	mixed 	$var	The thing to pass to var_dump().
 * @return	string			A shortened version of the var_dump() output.
 */
function var_dump_short($var)
{
	$result = trim(var_dump_ret($var));
	if(substr($result, 0, 6) === "object" || substr($result, 0, 5) === "array")
	{
		$result = substr($result, 0, strpos($result, " ")) . " { ... }";
	}
	return $result;
}

if (!function_exists('getallheaders'))  {
	/**
	 * Polyfill for PHP's native getallheaders() function on platforms that
	 * don't have it.
	 * @package core
	 * @todo	Identify which platforms don't have it and whether we still need this
	 */
	function getallheaders() {
		if (!is_array($_SERVER))
			return [];

		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}
}
/**
 * Renders a timestamp in HTML.
 * @package core
 * @param	int		$timestamp	The timestamp to render.
 * @param	boolean	$absolute	Whether the time should be displayed absolutely, or relative to the current time.
 * @param	boolean	$html		Whether the result should formatted as HTML (true) or plain text (false).
 * @return string         HTML representing the given timestamp.
 */
function render_timestamp($timestamp, $absolute = false, $html = true) {
	$time_rendered = $absolute ? date("Y-m-d g:ia e", $timestamp) : human_time_since($timestamp);
	if($html)
		return "<time class='cursor-query' datetime='".date("c",  $timestamp)."' title='" . date("l jS \of F Y \a\\t h:ia T", $timestamp) . "'>$time_rendered</time>";
	else
		return $time_rendered;
}
/**
 * Renders a page name in HTML.
 * @package core
 * @param  object $rchange The recent change to render as a page name
 * @return string          HTML representing the name of the given page.
 */
function render_pagename($rchange) {
	global $pageindex;
	$pageDisplayName = htmlentities($rchange->page);
	if(isset($pageindex->$pageDisplayName) and !empty($pageindex->$pageDisplayName->redirect))
		$pageDisplayName = "<em>$pageDisplayName</em>";
	$pageDisplayLink = "<a href='?page=" . rawurlencode($rchange->page) . "'>$pageDisplayName</a>";
	return $pageDisplayName;
}
/**
 * Renders an editor's or a group of editors name(s) in HTML.
 * @package core
 * @param  string $editorName The name of the editor to render. Note that this may contain ARBITRARY HTML! In other words, make sure that the editor name(s) are sanitized (e.g. htmlentities()'d) before padding to this function.
 * @return string             HTML representing the given editor's name.
 */
function render_editor($editorName) {
	return "<span class='editor'>&#9998; $editorName</span>";
}

/**
 * Minifies CSS. Uses simple computationally-cheap optimisations to reduce size.
 * CSS Minification ideas by Jean from catswhocode.com
 * @source	http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php
 * @since	0.20.0
 * @param	string	$css_str	The string of CSS to minify.
 * @return	string	The minified CSS string.
 */
function minify_css(string $css_str) : string {
	// Remove comments
	$result = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', "", $css_str);
	// Cut down whitespace
	$result = preg_replace('/\s+/', " ", $result);
	// Remove whitespace after colons and semicolons
	$result = str_replace([
		" :", ": ", "; ",
		" { ", " } ", "{ ", " {", "} ", " }",
		", ", "0."
	], [
		":", ":", ";",
		"{", "}", "{", "{", "}", "}",
		",", "."
	], $result);
	return $result;
}

/**
 * Saves the settings file back to peppermint.json.
 * @package	core
 * @return	bool	Whether the settings were saved successfully.
 */
function save_settings() {
	global $paths, $settings;
	return file_put_contents($paths->settings_file, json_encode($settings, JSON_PRETTY_PRINT)) !== false;
}
/**
 * Save the page index back to disk, respecting $settings->minify_pageindex
 * @package	core
 * @return	bool	Whether the page index was saved successfully or not.
 */
function save_pageindex() {
	global $paths, $settings, $pageindex;
	return file_put_contents(
		$paths->pageindex,
		json_encode($pageindex, $settings->minify_pageindex ? 0 : JSON_PRETTY_PRINT)
	);
}

/**
 * Saves the currently logged in user's data back to peppermint.json.
 * @package	core
 * @return	bool	Whether the user's data was saved successfully. Returns false if the user isn't logged in.
 */
function save_userdata() {
	global $env, $settings, $paths;
	
	if(!$env->is_logged_in)
		return false;
	
	$settings->users->{$env->user} = $env->user_data;
	
	return save_settings();
}

/**
 * Figures out the path to the user page for a given username.
 * Does not check to make sure the user acutally exists. 
 * @package core
 * @param  string $username The username to get the path to their user page for.
 * @return string           The path to the given user's page.
 */
function get_user_pagename($username) {
	global $settings;
	return "$settings->user_page_prefix/$username";
}
/**
 * Extracts a username from a user page path.
 * @package core
 * @param  string $userPagename The suer page path to extract from.
 * @return string               The name of the user that the user page belongs to.
 */
function extract_user_from_userpage($userPagename) {
	global $settings;
	$matches = [];
	preg_match("/$settings->user_page_prefix\\/([^\\/]+)\\/?/", $userPagename, $matches);
	
	return $matches[1];
}

/**
 * Sends a plain text email to a user, replacing {username} with the specified username.
 * @package core
 * @param	string	$username	The username to send the email to.
 * @param	string	$subject	The subject of the email.
 * @param	string	$body		The body of the email.
 * @param	bool	$ignore_verification	Whether to ignore user email verification status and send the email anyway. Defaults to false.
 * @return	bool	Whether the email was sent successfully or not. Currently, this may fail if the user doesn't have a registered email address.
 */
function email_user(string $username, string $subject, string $body, bool $ignore_verification = false) : bool
{
	global $version, $env, $settings;
	
	static $literator = null;
	if($literator == null) $literator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
	
	// If the user doesn't have an email address, then we can't email them :P
	if(empty($settings->users->{$username}->emailAddress))
		return false;
	
	// If email address verification is required but hasn't been done for this user, skip them
	if(empty($env->user_data->emailAddressVerified) && !$ignore_verification)
		return false;
	
	
	$headers = [
		"x-mailer" => ini_get("user_agent"),
		"reply-to" => "$settings->admindetails_name <$settings->admindetails_email>"
	];
	
	// Correctly encode the subject
	if($settings->email_subject_utf8)
		$subject = "=?utf-8?B?" . base64_encode($username) . "?=";
	else
		$subject = $literator->transliterate($subject);
	
	// Correctly encode the message body
	if($settings->email_body_utf8)
		$headers["content-type"] = "text/plain; charset=utf-8";
	else {
		$headers["content-type"] = "text/plain";
		$body = $literator->transliterate($body);
	}
	
	$subject = str_replace("{username}", $username, $subject);
	$body = str_replace("{username}", $username, $body);
	
	$compiled_headers = "";
	foreach($headers as $header => $value)
		$compiled_headers .= "$header: $value\r\n";
	
	if($settings->email_debug_dontsend) {
		error_log("[PeppermintyWiki/$settings->sitename/email] Username: $username ({$settings->users->{$username}->emailAddress})
Subject: $subject
----- Headers -----
$compiled_headers
-------------------
----- Body -----
$body
----------------");
		return true;
	}
	else
		return mail($settings->users->{$username}->emailAddress, $subject, $body, $compiled_headers, "-t");
}
/**
 * Sends a plain text email to a list of users, replacing {username} with each user's name.
 * @package core
 * @param  string[]	$usernames	A list of usernames to email.
 * @param  string	$subject	The subject of the email.
 * @param  string	$body		The body of the email.
 * @return int					The number of emails sent successfully.
 */
function email_users($usernames, string $subject, string $body) : int
{
	$emailsSent = 0;
	foreach($usernames as $username)
	{
		$emailsSent += email_user($username, $subject, $body) ? 1 : 0;
	}
	return $emailsSent;
}

/**
 * Recursively deletes a directory and it's contents.
 * Adapted by Starbeamrainbowlabs
 * @param	string	$path			The path to the directory to delete.
 * @param	bool	$delete_self	Whether to delete the top-level directory. Set this to false to delete only a directory's contents
 * @source https://stackoverflow.com/questions/4490637/recursive-delete
 */
function delete_recursive($path, $delete_self = true) {
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($path),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $file) {
		if (in_array($file->getBasename(), [".", ".."]))
			continue;
		if($file->isDir())
			rmdir($file->getPathname());
		else
			unlink($file->getPathname());
	}
	if($delete_self) rmdir($path);
}

/**
 * Generates a crytographically-safe random id of the given length.
 * @param	int		$length		The length of id to generate.
 * @return	string	The random id.
 */
function crypto_id(int $length) : string {
	// It *should* be the right length already, but it doesn't hurt to be safe
	return substr(strtr(
		base64_encode(random_bytes($length * 0.75)),
		[ "=" => "", "+" => "-", "/" => "_"]
	), 0, $length);
}

/**
 * Returns whether we are both on the cli AND the cli is enabled.
 * @return boolean
 */
function is_cli() : bool {
	global $settings;
	return php_sapi_name() == "cli" &&
		$settings->cli_enabled;
}

function metrics2servertiming(stdClass $perfdata) : string {
	$result = [];
	foreach($perfdata as $key => $value) {
		$result[] = str_replace("_", "", $key).";dur=$value";
	}
	return "foo, ".implode(", ", $result);
}

/**
 * Sets a cookie on the client via the set-cookie header.
 * Uses setcookie() under-the-hood.
 * @param  string $key     The cookie name to set.
 * @param  string $value   The cookie value to set.
 * @param  int    $expires The expiry time to set on the cookie.
 * @return void
 */
function send_cookie(string $key, $value, int $expires) : void {
	global $env, $settings;
	
	$cookie_secure = true;
	switch ($settings->cookie_secure) {
		case "false":
			$cookie_secure = false;
			break;
		case "auto":
		default:
			$cookie_secure = $env->is_secure;
			break;
	}
	
	if(version_compare(PHP_VERSION, "7.3.0") >= 0) {
		// Phew! We're running PHP 7.3+, so we're ok to use the array syntax
		setcookie($key, $value, [
			"expires" => $expires,
			"secure" => $cookie_secure,
			"httponly" => true,
			"samesite" => "Strict"
		]);
	}
	else {
		if(!$env->is_secure) error_log("[pepperminty_wiki/$settings->sitename] Warning: You are using a version of PHP that is less than 7.3. This is not recommended - as the samesite cookie flag can't be set in PHP 7.3-, and this is insecure - as it opens you to session stealing attacks. In addition, browsers have deprecated non-samesite cookies in insecure contexts. Please upgrade today!");
		setcookie($key, $value, $expires, "", "", $cookie_secure, true);
	}
}
