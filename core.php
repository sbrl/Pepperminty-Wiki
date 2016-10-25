<?php
$start_time = microtime(true);
mb_internal_encoding("UTF-8");

{settings}

///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "{version}";
/// Environment ///
$env = new stdClass();
$env->action = $settings->defaultaction;
$env->page = "";
$env->page_filename = "";
$env->is_history_revision = false;
$env->history = new stdClass();
$env->history->revision_number = -1;
$env->history->revision_data = false;
$env->user = "Anonymous";
$env->is_logged_in = false;
$env->is_admin = false;
$env->storage_prefix = $settings->data_storage_dir . DIRECTORY_SEPARATOR;
$env->perfdata = new stdClass();
/// Paths ///
$paths = new stdClass();
$paths->pageindex = "pageindex.json"; // The pageindex
$paths->searchindex = "invindex.json"; // The inverted index used for searching
$paths->idindex = "idindex.json"; // The index that converts ids to page names

// Prepend the storage data directory to all the defined paths.
foreach ($paths as &$path) {
	$path = $env->storage_prefix . $path;
}

$paths->upload_file_prefix = "Files/"; // The prefix to add to uploaded files

session_start();
// Make sure that the login cookie lasts beyond the end of the user's session
setcookie(session_name(), session_id(), time() + $settings->sessionlifetime);
///////// Login System /////////
// Clear expired sessions
if(isset($_SESSION[$settings->sessionprefix . "-expiretime"]) and
   $_SESSION[$settings->sessionprefix . "-expiretime"] < time())
{
	// Clear the session variables
	$_SESSION = [];
	session_destroy();
	$env->is_logged_in = false;
	$env->user = "Anonymous";
}

if(!isset($_SESSION[$settings->sessionprefix . "-user"]) and
  !isset($_SESSION[$settings->sessionprefix . "-pass"]))
{
	// The user is not logged in
	$env->is_logged_in = false;
}
else
{
	$env->user = $_SESSION[$settings->sessionprefix . "-user"];
	$env->pass = $_SESSION[$settings->sessionprefix . "-pass"];
	if($settings->users->{$env->user} == $env->pass)
	{
		// The user is logged in
		$env->is_logged_in = true;
	}
	else
	{
		// The user's login details are invalid (what is going on here?)
		// Unset the session variables, treat them as an anonymous user,
		// and get out of here
		$env->is_logged_in = false;
		$env->user = "Anonymous";
		$env->pass = "";
		// Clear the session data
		$_SESSION = []; //delete all the variables
		session_destroy(); //destroy the session
	}
}
//check to see if the currently logged in user is an admin
$env->is_admin = false;
if($env->is_logged_in)
{
	foreach($settings->admins as $admin_username)
	{
		if($admin_username == $env->user)
		{
			$env->is_admin = true;
			break;
		}
	}
}
/////// Login System End ///////

////////////////////
// APIDoc strings //
////////////////////
/**
 * @apiDefine Moderator	Only users loggged with a moderator account may use this call.
 */
/**
* @apiDefine User		Only users loggged in may use this call.
*/
/**
* @apiDefine Anonymous	Anybody may use this call.
*/
/**
 * @apiDefine	UserNotLoggedInError
 * @apiError	UserNotLoggedInError	You didn't log in before sending this request.
 */
/**
* @apiDefine	UserNotModeratorError
* @apiError	UserNotModeratorError	You weren't loggged in as a moderator before sending this request.
*/
/**
* @apiDefine	PageParameter
* @apiParam	{string}	page	The page to operate on.
*/
////////////////////

///////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Functions //////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
/**
 * Get the actual absolute origin of the request sent by the user.
 * @param  array	$s						The $_SERVER variable contents. Defaults to $_SERVER.
 * @param  bool		$use_forwarded_host		Whether to utilise the X-Forwarded-Host header when calculating the actual origin.
 * @return string							The actual origin of the user's request.
 */
function url_origin( $s = false, $use_forwarded_host = false )
{
	if($s === false) $s = $_SERVER;
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
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
 * @see		http://stackoverflow.com/a/8891890/1460422	This Stackoverflow answer.
 * @param	array	$s                  The $_SERVER variable. Defaults to $_SERVER.
 * @param	bool		$use_forwarded_host Whether to take the X-Forwarded-Host header into account.
 * @return	string						The full url, as requested by the client.
 */
function full_url( $s = false, $use_forwarded_host = false )
{
    return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}

/**
 * Converts a filesize into a human-readable string.
 * From http://php.net/manual/en/function.filesize.php#106569
 * Edited by Starbeamrainbowlabs.
 * @param	number	$bytes		The number of bytes to convert.
 * @param	number	$decimals	The number of decimal places to preserve.
 * @return 	string				A human-readable filesize.
 */
function human_filesize($bytes, $decimals = 2)
{
	$sz = ["b", "kb", "mb", "gb", "tb", "pb", "eb", "yb", "zb"];
	$factor = floor((strlen($bytes) - 1) / 3);
	$result = round($bytes / pow(1024, $factor), $decimals);
	return $result . @$sz[$factor];
}

/**
 * Calculates the time since a particular timestamp and returns a
 * human-readable result.
 * From http://goo.gl/zpgLgq.
 * @param	integer	$time	The timestamp to convert.
 * @return	string			The time since the given timestamp as
 *                      	a human-readable string.
 */
function human_time_since($time)
{
	$timediff = time() - $time;
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
		if ($timediff < $unit) continue;
		$numberOfUnits = floor($timediff / $unit);
		return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').' ago';
	}
}

/**
 * A recursive glob() function.
 * From http://in.php.net/manual/en/function.glob.php#106595
 * @param  string  $pattern The glob pattern to use to find filenames.
 * @param  integer $flags   The glob flags to use when finding filenames.
 * @return array			An array of the filepaths that match the given glob.
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
 * Gets a list of all the sub pages of the current page.
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
 * @param The pagename to check.
 */
function check_subpage_parents($pagename)
{
	global $pageindex, $paths, $env;
	// Save the new pageindex and return if there aren't any more parent pages to check
	if(strpos($pagename, "/") === false)
	{
		file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
		return;
	}

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

	check_subpage_parents($parent_pagename);
}

/**
 * Makes a path safe.
 * Paths may only contain alphanumeric characters, spaces, underscores, and
 * dashes.
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
	return $string;
}

/**
 * Hides an email address from bots by adding random html entities.
 * @param	string	$str	The original email address
 * @return	string			The mangled email address.
 */
function hide_email($str)
{
	$hidden_email = "";
	for($i = 0; $i < strlen($str); $i++)
	{
		if($str[$i] == "@")
		{
			$hidden_email .= "&#" . ord("@") . ";";
			continue;
		}
		if(rand(0, 1) == 0)
			$hidden_email .= $str[$i];
		else
			$hidden_email .= "&#" . ord($str[$i]) . ";";
	}

	return $hidden_email;
}
/**
 * Checks to see if $haystack starts with $needle.
 * @param	string	$haystack	The string to search.
 * @param	string	$needle		The string to search for at the beginning
 *                        		of $haystack.
 * @return	boolean				Whether $needle can be found at the beginning
 *                            	of $haystack.
 */
function starts_with($haystack, $needle)
{
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
}

/**
 * Case-insensitively finds all occurrences of $needle in $haystack. Handles
 * UTF-8 characters correctly.
 * From http://www.pontikis.net/tip/?id=16, and
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 * @param	string			$haystack	The string to search.
 * @param	string			$needle		The string to find.
 * @return	array || false				An array of match indices, or false if
 *                  					nothing was found.
 */
function mb_stripos_all($haystack, $needle) {
	$s = 0; $i = 0;
	while(is_integer($i)) {
		$i = function_exists("mb_stripos") ? mb_stripos($haystack, $needle, $s) : stripos($haystack, $needle, $s);
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
 * Tests whether a string ends with a given substring.
 * @param  string $whole The string to test against.
 * @param  string $end   The substring test for.
 * @return bool          Whether $whole ends in $end.
 */
function endsWith($whole, $end)
{
    return (strpos($whole, $end, strlen($whole) - strlen($end)) !== false);
}

/**
 * Returns the system's mime type mappings, considering the first extension
 * listed to be cacnonical.
 * From http://stackoverflow.com/a/1147952/1460422 by chaos.
 * Edited by Starbeamrainbowlabs.
 * @return array	An array of mime type mappings.
 */
function system_mime_type_extensions()
{
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
 * From http://stackoverflow.com/a/1147952/1460422 by chaos.
 * Edited by Starbeamrainbowlabs.
 * @param  string $type The mime type to convert.
 * @return string       The extension for the given mime type.
 */
function system_mime_type_extension($type)
{
	static $exts;
	if(!isset($exts))
		$exts = system_mime_type_extensions();
	return isset($exts[$type]) ? $exts[$type] : null;
}

/**
 * Returns the system MIME type mapping of extensions to MIME types.
 * From http://stackoverflow.com/a/1147952/1460422 by chaos.
 * Edited by Starbeamrainbowlabs.
 * @return array An array mapping file extensions to their associated mime types.
 */
function system_extension_mime_types()
{
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
 * From http://stackoverflow.com/a/1147952/1460422 by chaos.
 * Edited by Starbeamrainbowlabs.
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
 * Generates a stack trace.
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

/**
 * Polyfill getallheaders()
 */
if (!function_exists('getallheaders'))  {
    function getallheaders()
    {
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
 * @param  int $timestamp The timestamp to render.
 * @return string         HTML representing the given timestamp.
 */
function render_timestamp($timestamp)
{
	return "<time class='cursor-query' title='" . date("l jS \of F Y \a\\t h:ia T", $timestamp) . "'>" . human_time_since($timestamp) . "</time>";
}
/**
 * Renders a page name in HTML.
 * @param  object $rchange The recent change to render as a page name
 * @return string          HTML representing the name of the given page.
 */
function render_pagename($rchange)
{
	global $pageindex;
	$pageDisplayName = $rchange->page;
	if(isset($pageindex->$pageDisplayName) and !empty($pageindex->$pageDisplayName->redirect))
		$pageDisplayName = "<em>$pageDisplayName</em>";
	$pageDisplayLink = "<a href='?page=" . rawurlencode($rchange->page) . "'>$pageDisplayName</a>";
	return $pageDisplayName;
}
/**
 * Renders an editor's name in HTML.
 * @param  string $editorName The name of the editor to render.
 * @return string             HTML representing the given editor's name.
 */
function render_editor($editorName)
{
	return "<span class='editor'>&#9998; $editorName</span>";
}

///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////////////////////// Security and Consistency Measures //////////////////////
///////////////////////////////////////////////////////////////////////////////

/*
 * Sort out the pageindex. Create it if it doesn't exist, and load + parse it
 * if it does.
 */
if(!file_exists($paths->pageindex))
{
	$glob_str = $env->storage_prefix . "*.md";
	$existingpages = glob_recursive($glob_str);
	// Debug statements. Uncomment when debugging the pageindex regenerator.
	// var_dump($env->storage_prefix);
	// var_dump($glob_str);
	// var_dump($existingpages);
	$pageindex = new stdClass();
	// We use a for loop here because foreach doesn't loop over new values inserted
	// while we were looping
	for($i = 0; $i < count($existingpages); $i++)
	{
		$pagefilename = $existingpages[$i];
		
		// Create a new entry
		$newentry = new stdClass();
		$newentry->filename = utf8_encode(substr( // Store the filename, whilst trimming the storage prefix
			$pagefilename,
			strlen(preg_replace("/^\.\//i", "", $env->storage_prefix)) // glob_recursive trim the ./ from returned filenames , so we need to as well
		));
		// Remove the `./` from the beginning if it's still hanging around
		if(substr($newentry->filename, 0, 2) == "./")
			$newentry->filename = substr($newentry->filename, 2);
		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified
		// Todo find a way to keep the last editor independent of the page index
		$newentry->lasteditor = utf8_encode("unknown"); // Set the editor to "unknown"
		// Extract the name of the (sub)page without the ".md"
		$pagekey = utf8_encode(substr($newentry->filename, 0, -3));
		
		if(file_exists($env->storage_prefix . $pagekey) && // If it exists...
			!is_dir($env->storage_prefix . $pagekey)) // ...and isn't a directory
		{
			// This page (potentially) has an associated file!
			// Let's investigate.
			
			// Blindly add the file to the pageindex for now.
			// Future We might want to do a security check on the file later on.
			// File a bug if you think we should do this.
			$newentry->uploadedfile = true; // Yes this page does have an uploaded file associated with it
			$newentry->uploadedfilepath = $pagekey; // It's stored here
			
			// Work out what kind of file it really is
			$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
			$newentry->uploadedfilemime = finfo_file($mimechecker, $env->storage_prefix . $pagekey);
		}
		
		// Debug statements. Uncomment when debugging the pageindex regenerator.
		// echo("pagekey: ");
		// var_dump($pagekey);
		// echo("newentry: ");
		// var_dump($newentry);
		
		// Subpage parent checker
		if(strpos($pagekey, "/") !== false)
		{
			// We have a sub page people
			// Work out what our direct parent's key must be in order to check to
			// make sure that it actually exists. If it doesn't, then we need to
			// create it.
			$subpage_parent_key = substr($pagekey, 0, strrpos($pagekey, "/"));
			$subpage_parent_filename = "$env->storage_prefix$subpage_parent_key.md";
			if(array_search($subpage_parent_filename, $existingpages) === false)
			{
				// Our parent page doesn't actually exist - create it
				touch($subpage_parent_filename, 0);
				// Furthermore, we should add this page to the list of existing pages
				// in order for it to be indexed
				$existingpages[] = $subpage_parent_filename;
			}
		}

		// Store the new entry in the new page index
		$pageindex->$pagekey = $newentry;
	}
	file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
	unset($existingpages);
}
else
{
	$pageindex_read_start = microtime(true);
	$pageindex = json_decode(file_get_contents($paths->pageindex));
	$env->perfdata->pageindex_decode_time = round((microtime(true) - $pageindex_read_start)*1000, 3);
	header("x-pageindex-decode-time: " . $env->perfdata->pageindex_decode_time . "ms");
}

//////////////////////////
///// Page id system /////
//////////////////////////
if(!file_exists($paths->idindex))
	file_put_contents($paths->idindex, "{}");
$idindex_decode_start = microtime(true);
$idindex = json_decode(file_get_contents($paths->idindex));
$env->perfdata->idindex_decode_time = round((microtime(true) - $idindex_decode_start)*1000, 3);
class ids
{
	/*
	 * @summary Gets the page id associated with the given pagename.
	 */
	public static function getid($pagename)
	{
		global $idindex;

		foreach ($idindex as $id => $entry)
		{
			if($entry == $pagename)
				return $id;
		}

		// This pagename doesn't have an id - assign it one quick!
		return self::assign($pagename);
	}

	/*
	 * @summary Gets the page name associated with the given page id.
	 */
	public static function getpagename($id)
	{
		global $idindex;

		if(!isset($idindex->$id))
			return false;
		else
			return $idindex->$id;
	}
	
	/*
	 * @summary Moves a page in the id index from $oldpagename to $newpagename.
	 *		  Note that this function doesn't perform any special checks to
	 *		  make sure that the destination name doesn't already exist.
	 */
	public static function movepagename($oldpagename, $newpagename)
	{
		global $idindex, $paths;
		
		$pageid = self::getid($oldpagename);
		$idindex->$pageid = $newpagename;
		
		file_put_contents($paths->idindex, json_encode($idindex));
	}
	
	/*
	 * @summary Removes the given page name from the id index. Note that this
	 *		  function doesn't handle multiple entries with the same name.
	 */
	public static function deletepagename($pagename)
	{
		global $idindex, $paths;
		
		// Get the id of the specified page
		$pageid = self::getid($pagename);
		// Remove it from the pageindex
		unset($idindex->$pageid);
		// Save the id index
		file_put_contents($paths->idindex, json_encode($idindex));
	}

	/*
	 * @summary Assigns an id to a pagename. Doesn't check to make sure that
	 * 			pagename doesn't exist in the pageindex.
	 */
	protected static function assign($pagename)
	{
		global $idindex, $paths;

		$nextid = count(array_keys(get_object_vars($idindex)));
		// Increment the generated id until it's unique
		while(isset($idindex->nextid))
			$nextid++;
		
		// Update the id index
		$idindex->$nextid = utf8_encode($pagename);

		// Save the id index
		file_put_contents($paths->idindex, json_encode($idindex));

		return $nextid;
	}
}
//////////////////////////
//////////////////////////

// Work around an Opera + Syntaxtic bug where there is no margin at the left
// hand side if there isn't a query string when accessing a .php file.
if(!isset($_GET["action"]) and !isset($_GET["page"]))
{
	http_response_code(302);
	header("location: index.php?action=$settings->defaultaction&page=$settings->defaultpage");
	exit();
}

// Make sure that the action is set
if(!isset($_GET["action"]))
	$_GET["action"] = $settings->defaultaction;
// Make sure that the page is set
if(!isset($_GET["page"]) or strlen($_GET["page"]) === 0)
	$_GET["page"] = $settings->defaultpage;

// Redirect the user to the safe version of the path if they entered an unsafe character
if(makepathsafe($_GET["page"]) !== $_GET["page"])
{
	http_response_code(301);
	header("location: index.php?action=" . rawurlencode($_GET["action"]) . "&page=" . makepathsafe($_GET["page"]));
	header("x-requested-page: " . $_GET["page"]);
	header("x-actual-page: " . makepathsafe($_GET["page"]));
	exit();
}


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////// HTML fragments ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
class page_renderer
{
	public static $html_template = "<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8' />
		<title>{title}</title>
		<meta name='viewport' content='width=device-width, initial-scale=1' />
		<link rel='shortcut-icon' href='{favicon-url}' />
		<link rel='icon' href='{favicon-url}' />
		{header-html}
	</head>
	<body>
		{body}
		<!-- Took {generation-time-taken}ms to generate -->
	</body>
</html>
";

	public static $main_content_template = "{navigation-bar}
		<h1 class='sitename'>{sitename}</h1>
		<main>
		{content}
		</main>

		<footer>
			<p>{footer-message}</p>
			<p>Powered by Pepperminty Wiki {version}, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or <a href='//github.com/sbrl/Pepperminty-Wiki' title='Github Issue Tracker'>open an issue</a>.</p>
			<p>Your local friendly administrators are {admins-name-list}.</p>
			<p>This wiki is managed by <a href='mailto:{admin-details-email}'>{admin-details-name}</a>.</p>
		</footer>
		{navigation-bar-bottom}
		{all-pages-datalist}";
	public static $minimal_content_template = "<main class='printable'>{content}</main>
		<footer class='printable'>
			<hr class='footerdivider' />
			<p><em>From {sitename}, which is managed by {admin-details-name}.</em></p>
			<p>{footer-message}</p>
			<p><em>Timed at {generation-date}</em></p>
			<p><em>Powered by Pepperminty Wiki {version}.</em></p>
		</footer>";

	// An array of functions that have been registered to process the
	// find / replace array before the page is rendered. Note that the function
	// should take a *reference* to an array as its only argument.
	protected static $part_processors = [];

	// Registers a function as a part post processor.
	public static function register_part_preprocessor($function)
	{
		global $settings;

		// Make sure that the function we are about to register is valid
		if(!is_callable($function))
		{
			http_response_code(500);
			$admin_name = $settings->admindetails_name;
			$admin_email = hide_email($settings->admindetails_email);
			exit(page_renderer::render("$settings->sitename - Module Error", "<p>$settings->sitename has got a misbehaving module installed that tried to register an invalid HTML handler with the page renderer. Please contact $settings->sitename's administrator $admin_name at <a href='mailto:$admin_email'>$admin_email</a>."));
		}

		self::$part_processors[] = $function;

		return true;
	}

	public static function render($title, $content, $body_template = false)
	{
		global $settings, $start_time, $version;

		if($body_template === false)
			$body_template = self::$main_content_template;

		if(strlen($settings->logo_url) > 0)
		{
			// A logo url has been specified
			$logo_html = "<img class='logo" . (isset($_GET["printable"]) ? " small" : "") . "' src='$settings->logo_url' />";
			switch($settings->logo_position)
			{
				case "left":
					$logo_html = "$logo_html $settings->sitename";
					break;
				case "right":
					$logo_html .= " $settings->sitename";
					break;
				default:
					throw new Exception("Invalid logo_position '$settings->logo_position'. Valid values are either \"left\" or \"right\" and are case sensitive.");
			}
		}

		$parts = [
			"{body}" => $body_template,

			"{sitename}" => $logo_html,
			"{version}" => $version,
			"{favicon-url}" => $settings->favicon,
			"{header-html}" => self::get_header_html(),

			"{navigation-bar}" => self::render_navigation_bar($settings->nav_links, $settings->nav_links_extra, "top"),
			"{navigation-bar-bottom}" => self::render_navigation_bar($settings->nav_links_bottom, [], "bottom"),

			"{admin-details-name}" => $settings->admindetails_name,
			"{admin-details-email}" => $settings->admindetails_email,

			"{admins-name-list}" => implode(", ", $settings->admins),

			"{generation-date}" => date("l jS \of F Y \a\\t h:ia T"),

			"{all-pages-datalist}" => self::generate_all_pages_datalist(),

			"{footer-message}" => $settings->footer_message,

			/// Secondary Parts ///

			"{content}" => $content,
			"{title}" => $title,
		];

		// Pass the parts through the part processors
		foreach(self::$part_processors as $function)
		{
			$function($parts);
		}

		$result = self::$html_template;

		$result = str_replace(array_keys($parts), array_values($parts), $result);

		$result = str_replace("{generation-time-taken}", round((microtime(true) - $start_time)*1000, 2), $result);
		return $result;
	}
	public static function render_main($title, $content)
	{
		return self::render($title, $content, self::$main_content_template);
	}
	public static function render_minimal($title, $content)
	{
		return self::render($title, $content, self::$minimal_content_template);
	}
	
	public static function get_header_html()
	{
		global $settings;
		$result = self::get_css_as_html();
		
		if(module_exists("feature-search"))
			$result .= "\t\t<link type='application/opensearchdescription+xml' rel='search' href='?action=opensearch-description' />";
		
		if(!empty($settings->enable_math_rendering))
			$result .= "<script type='text/x-mathjax-config'>
  MathJax.Hub.Config({
    tex2jax: {
      inlineMath: [ ['$','$'], ['\\\\(','\\\\)'] ],
      processEscapes: true,
      skipTags: ['script','noscript','style','textarea','pre','code']
    }
  });
</script>
<script async src='https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML'></script>";
		
		return $result;
	}
	public static function get_css_as_html()
	{
		global $settings;

		if(preg_match("/^[^\/]*\/\/|^\//", $settings->css))
			return "<link rel='stylesheet' href='$settings->css' />\n";
		else
		{
			$css = $settings->css;
			if(!empty($settings->optimize_pages))
			{
				// CSS Minification ideas by Jean from catswhocode.com
				// Link: http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php
				// Remove comments
				$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', "", $css);
				// Cut down whitespace
				$css = preg_replace('/\s+/', " ", $css);
				// Remove whitespace after colons and semicolons
				$css = str_replace([
					" :",
					": ",
					"; ",
					" { ",
					" } "
				], [
					":",
					":",
					";",
					"{",
					"}"
				], $css);
				
			}
			return "<style>$css</style>\n";
		}
	}

	public static $nav_divider = "<span class='nav-divider inflexible'> | </span>";

	/**
	 * Renders a navigation bar from an array of links. See
	 * $settings->nav_links for format information.
	 * @param array	$nav_links			The links to add to the navigation bar.
	 * @param array	$nav_links_extra	The extra nav links to add to
	 *                               	the "More..." menu.
	 */
	public static function render_navigation_bar($nav_links, $nav_links_extra, $class = "")
	{
		global $settings, $env;
		$result = "<nav class='$class'>\n";

		// Loop over all the navigation links
		foreach($nav_links as $item)
		{
			if(is_string($item))
			{
				// The item is a string
				switch($item)
				{
					//keywords
					case "user-status": // Renders the user status box
						if($env->is_logged_in)
						{
							$result .= "<span class='inflexible logged-in" . ($env->is_logged_in ? " moderator" : " normal-user") . "'>" . self::render_username($env->user) . " <small>(<a href='index.php?action=logout'>Logout</a>)</small></span>";
							//$result .= page_renderer::$nav_divider;
						}
						else
							$result .= "<span class='not-logged-in'><a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>Login</a></span>";
						break;

					case "search": // Renders the search bar
						$result .= "<span class='inflexible'><form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /><input type='hidden' name='search-redirect' value='true' /></form></span>";
						break;

					case "divider": // Renders a divider
						$result .= page_renderer::$nav_divider;
						break;

					case "menu": // Renders the "More..." menu
						$result .= "<span class='inflexible nav-more'><label for='more-menu-toggler'>More...</label>
<input type='checkbox' class='off-screen' id='more-menu-toggler' />";
						$result .= page_renderer::render_navigation_bar($nav_links_extra, [], "nav-more-menu");
						$result .= "</span>";
						break;

					// It isn't a keyword, so just output it directly
					default:
						$result .= "<span>$item</span>";
				}
			}
			else
			{
				// Output the item as a link to a url
				$result .= "<span><a href='" . str_replace("{page}", rawurlencode($env->page), $item[1]) . "'>$item[0]</a></span>";
			}
		}

		$result .= "</nav>";
		return $result;
	}
	public static function render_username($name)
	{
		global $settings;
		$result = "";
		if(in_array($name, $settings->admins))
			$result .= $settings->admindisplaychar;
		$result .= $name;

		return $result;
	}

	public static function generate_all_pages_datalist()
	{
		global $pageindex;
		$arrayPageIndex = get_object_vars($pageindex);
		ksort($arrayPageIndex);
		$result = "<datalist id='allpages'>\n";
		foreach($arrayPageIndex as $pagename => $pagedetails)
		{
			$escapedPageName = str_replace('"', '&quot;', $pagename);
			$result .= "\t\t\t<option value=\"$escapedPageName\" />\n";
		}
		$result .= "\t\t</datalist>";

		return $result;
	}
}

/// Finish setting up the environment object ///
$env->page = $_GET["page"];
if(isset($_GET["revision"]) and is_numeric($_GET["revision"]))
{
	// We have a revision number!
	$env->is_history_revision = true;
	$env->history->revision_number = intval($_GET["revision"]);
	
	// Make sure that the revision exists for later on
	if(!isset($pageindex->{$env->page}->history[$env->history->revision_number]))
	{
		http_response_code(404);
		exit(page_renderer::render_main("404: Revision Not Found - $env->page - $settings->sitename", "<p>Revision #{$env->history->revision_number} of $env->page doesn't appear to exist. Try viewing the <a href='?action=history&page=" . rawurlencode($env->page) . "'>list of revisions for $env->page</a>, or viewing <a href='?page=" . rawurlencode($env->page) . "'>the latest revision</a> instead.</p>"));
	}
	
	$env->history->revision_data = $pageindex->{$env->page}->history[$env->history->revision_number];
}
// Construct the page's filename
$env->page_filename = $env->storage_prefix;
if($env->is_history_revision)
	$env->page_filename .= $pageindex->{$env->page}->history[$env->history->revision_number]->filename;
else if(isset($pageindex->{$env->page}))
	$env->page_filename .= $pageindex->{$env->page}->filename;

$env->action = strtolower($_GET["action"]);

////////////////////////////////////////////////

//////////////////////////////////////
///// Extra consistency measures /////
//////////////////////////////////////
// Redirect to the search page if there isn't a page with the requested name
if(!isset($pageindex->{$env->page}) and isset($_GET["search-redirect"]))
{
	http_response_code(307);
	$url = "?action=search&query=" . rawurlencode($env->page);
	header("location: $url");
	exit(page_renderer::render("Non existent page - $settings->sitename", "<p>There isn't a page on $settings->sitename with that name. However, you could <a href='$url'>search for this page name</a> in other pages.</p>
		<p>Alternatively, you could <a href='?action=edit&page=" . rawurlencode($env->page) . "&create=true'>create this page</a>.</p>"));
}

// Redirect the user to the login page if:
//  - A login is required to view this wiki
//  - The user isn't already requesting the login page
// Note we use $_GET here because $env->action isn't populated at this point
if($settings->require_login_view === true && // If this site requires a login in order to view pages
   !$env->is_logged_in && // And the user isn't logged in
   !in_array($_GET["action"], [ "login", "checklogin" ])) // And the user isn't trying to login
{
	// Redirect the user to the login page
	http_response_code(307);
	$url = "?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "&required=true";
	header("location: $url");
	exit(page_renderer::render("Login required - $settings->sitename", "<p>$settings->sitename requires that you login before you are able to access it.</p>
		<p><a href='$url'>Login</a>.</p>"));
}
//////////////////////////////////////
//////////////////////////////////////

//////////////////////////
///  Module functions  ///
//////////////////////////
// These functions are	//
// used by modules to	//
// register themselves	//
// or new pages.		//
//////////////////////////
$modules = []; // List that contains all the loaded modules
// Function to register a module
function register_module($moduledata)
{
	global $modules;
	//echo("registering module\n");
	//var_dump($moduledata);
	$modules[] = $moduledata;
}
/**
 * Checks to see whether a module with the given id exists.
 * @param  string   $id	 The id to search for.
 * @return bool     Whether a module is currently loaded with the given id.
 */
function module_exists($id)
{
	global $modules;
	foreach($modules as $module)
	{
		if($module["id"] == $id)
			return true;
	}
	return false;
}

// Function to register an action handler
$actions = new stdClass();
function add_action($action_name, $func)
{
	global $actions;
	$actions->$action_name = $func;
}

$parsers = [
	"none" => function() {
		throw new Exception("No parser registered!");
	}
];
/**
 * Registers a new parser.
 * @param string	$name       	The name of the new parser to register.
 * @param function	$parser_code	The function to register as a new parser.
 */
function add_parser($name, $parser_code)
{
	global $parsers;
	if(isset($parsers[$name]))
		throw new Exception("Can't register parser with name '$name' because a parser with that name already exists.");

	$parsers[$name] = $parser_code;
}
function parse_page_source($source)
{
	global $settings, $parsers;
	if(!isset($parsers[$settings->parser]))
		exit(page_renderer::render_main("Parsing error - $settings->sitename", "<p>Parsing some page source data failed. This is most likely because $settings->sitename has the parser setting set incorrectly. Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>" . $settings->admindetails_name . "</a>, your $settings->sitename Administrator."));

/* Not needed atm because escaping happens when saving, not when rendering *
	if($settings->clean_raw_html)
		$source = htmlentities($source, ENT_QUOTES | ENT_HTML5);
*/
	return $parsers[$settings->parser]($source);
}

// Function to 
$save_preprocessors = [];
/**
 * Register a new proprocessor that will be executed just before
 * an edit is saved.
 * @param	function	$func	The function to register.
 */
function register_save_preprocessor($func)
{
	global $save_preprocessors;
	$save_preprocessors[] = $func;
}

$help_sections = [];
/**
 * Adds a new help section to the help page.
 * @param string $index   The string to index the new section under.
 * @param string $title   The title to display above the section.
 * @param string $content The content to display.
 */
function add_help_section($index, $title, $content)
{
	global $help_sections;
	
	$help_sections[$index] = [
		"title" => $title,
		"content" => $content
	];
}

if(!empty($settings->enable_math_rendering))
	add_help_section("22-Mathematical-Expressions", "Methematical Expressions", "<p>$settings->sitename supports rendering of mathematical expressions. Mathematical expressions can be included practically anywhere in your page. Expressions should be written in LaTeX and enclosed in dollar signs like this: <code>&#36;x^2&#36;</code>.</p>
	<p>Note that expression parsing is done on the viewer's computer with javascript (specifically MathJax) and not by $settings->sitename directly (also called client side rendering).</p>");

//////////////////////////////////////////////////////////////////

// %next_module% //

//////////////////////////////////////////////////////////////////

// Execute each module's code
foreach($modules as $moduledata)
{
	$moduledata["code"]();
}
// Make sure that the credits page exists
if(!isset($actions->credits))
{
	exit(page_renderer::render_main("Error - $settings->$sitename", "<p>No credits page detected. The credits page is a required module!</p>"));
}

// Perform the appropriate action
$action_name = $env->action;
if(isset($actions->$action_name))
{
	$req_action_data = $actions->$action_name;
	$req_action_data();
}
else
{
	exit(page_renderer::render_main("Error - $settings->sitename", "<p>No action called " . strtolower($_GET["action"]) ." has been registered. Perhaps you are missing a module?</p>"));
}

?>
