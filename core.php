<?php
/** The Pepperminty Wiki core */
$start_time = microtime(true);
mb_internal_encoding("UTF-8");

//{settings}

/////////////////////////////////////////////////////////////////////////////
////// Do not edit below this line unless you know what you are doing! //////
/////////////////////////////////////////////////////////////////////////////
/** The version of Pepperminty Wiki currently running. */
$version = "{version}";
$commit = "{commit}";
/// Environment ///
/** Holds information about the current request environment. */
$env = new stdClass();
/** The action requested by the user. */
$env->action = $settings->defaultaction;
/** The page name requested by the remote client. */
$env->page = "";
/** The filename that the page is stored in. */
$env->page_filename = "";
/** Whether we are looking at a history revision. */
$env->is_history_revision = false;
/** An object holding history revision information for the current request */
$env->history = new stdClass();
/** The revision number requested of the current page */
$env->history->revision_number = -1;
/** The revision data object from the page index for the requested revision */
$env->history->revision_data = false;
/** The user's name if they are logged in. Defaults to `$settings->anonymous_user_name` if the user isn't currently logged in. @var string */
$env->user = $settings->anonymous_user_name;
/** Whether the user is logged in */
$env->is_logged_in = false;
/** Whether the user is an admin (moderator) @todo Refactor this to is_moderator, so that is_admin can be for the server owner. */
$env->is_admin = false;
/** The currently logged in user's data. Please see $settings->users->username if you need to edit this - this is here for convenience :-) */
$env->user_data = new stdClass();
/** The data storage directory. Page filenames should be prefixed with this if you want their content. */
$env->storage_prefix = $settings->data_storage_dir . DIRECTORY_SEPARATOR;
/** Contains performance data statistics for the current request. */
$env->perfdata = new stdClass();
/// Paths ///
/**
 * Contains a bunch of useful paths to various important files.
 * None of these need to be prefixed with `$env->storage_prefix`.
 */
$paths = new stdClass();
/** The pageindex. Contains extensive information about all pages currently in this wiki. Individual entries for pages may be extended with arbitrary properties. */
$paths->pageindex = "pageindex.json";
/** The inverted index used for searching. Use the `search` class to interact with this - otherwise your brain might explode :P */
$paths->searchindex = "invindex.json";
/** The index that maps ids to page names. Use the `ids` class to interact with it :-) */
$paths->idindex = "idindex.json";
/** The cache of the most recently calculated statistics. */
$paths->statsindex = "statsindex.json";
/** The interwiki index cache */
$paths->interwiki_index = "interwiki_index.json";
/** The cache directory, minus the trailing slash. Contains cached rendered versions of pages. If things don't update, try deleting this folder.  */
$paths->cache_directory = "._cache";

// Prepend the storage data directory to all the defined paths.
foreach ($paths as &$path) {
	$path = $env->storage_prefix . $path;
}

/** The master settings file @var string */
$paths->settings_file = $settingsFilename;
/** The prefix to add to uploaded files */
$paths->upload_file_prefix = "Files/";

// Create the cache directory if it doesn't exist
if(!is_dir($paths->cache_directory))
	mkdir($paths->cache_directory, 0700);

session_start();
// Make sure that the login cookie lasts beyond the end of the user's session
setcookie(session_name(), session_id(), time() + $settings->sessionlifetime, "", "", false, true);
///////// Login System /////////
// Clear expired sessions
if(isset($_SESSION[$settings->sessionprefix . "-expiretime"]) and
   $_SESSION[$settings->sessionprefix . "-expiretime"] < time())
{
	// Clear the session variables
	$_SESSION = [];
	session_destroy();
}

if(isset($_SESSION[$settings->sessionprefix . "-user"]) and
  isset($_SESSION[$settings->sessionprefix . "-pass"]))
{
	// Grab the session variables
	$env->user = $_SESSION[$settings->sessionprefix . "-user"];
	
	// The user is logged in
	$env->is_logged_in = true;
	$env->user_data = $settings->users->{$env->user};
	
}

// Check to see if the currently logged in user is an admin
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
 * @apiDefine Admin	Only the wiki administrator may use this call.
 */
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
 * @package core
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
 * @package core
 * @see		http://stackoverflow.com/a/8891890/1460422	This Stackoverflow answer.
 * @param	array	$s                  The $_SERVER variable. Defaults to $_SERVER.
 * @param	bool		$use_forwarded_host Whether to take the X-Forwarded-Host header into account.
 * @return	string						The full url, as requested by the client.
 */
function full_url( $s = false, $use_forwarded_host = false )
{
	if($s == false) $s = $_SERVER;
    return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}

/**
 * Converts a filesize into a human-readable string.
 * @package core
 * @see	http://php.net/manual/en/function.filesize.php#106569	The original source
 * @author	rommel
 * @author	Edited by Starbeamrainbowlabs
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
 * @package core
 * @see http://goo.gl/zpgLgq The original source. No longer exists, maybe the wayback machine caught it :-(
 * @param	integer	$time	The timestamp to convert.
 * @return	string			The time since the given timestamp as
 *                      	a human-readable string.
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
 * Gets the name of the parent page to the specified page.
 * @since 0.15
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
 * @package core
 * @param $pagename	The pagename to check.
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
	return $string;
}

/**
 * Hides an email address from bots by adding random html entities.
 * @todo			Make this more clevererer :D
 * @package core
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
 * @package	core
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
function endsWith($whole, $end)
{
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
function str_replace_once($find, $replace, $subject)
{
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
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
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
 * @package core
 * @see http://stackoverflow.com/a/1147952/1460422 From this stackoverflow answer
 * @author	chaos
 * @author	Edited by Starbeamrainbowlabs
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
 * @package core
 * @param  int $timestamp The timestamp to render.
 * @return string         HTML representing the given timestamp.
 */
function render_timestamp($timestamp)
{
	return "<time class='cursor-query' title='" . date("l jS \of F Y \a\\t h:ia T", $timestamp) . "'>" . human_time_since($timestamp) . "</time>";
}
/**
 * Renders a page name in HTML.
 * @package core
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
 * Renders an editor's or a group of editors name(s) in HTML.
 * @package core
 * @param  string $editorName The name of the editor to render.
 * @return string             HTML representing the given editor's name.
 */
function render_editor($editorName)
{
	return "<span class='editor'>&#9998; $editorName</span>";
}

/**
 * Saves the settings file back to peppermint.json.
 * @return bool Whether the settings were saved successfully.
 */
function save_settings() {
	global $paths, $settings;
	return file_put_contents($paths->settings_file, json_encode($settings, JSON_PRETTY_PRINT)) !== false;
}

/**
 * Saves the currently logged in user's data back to peppermint.json.
 * @package core
 * @return bool Whether the user's data was saved successfully. Returns false if the user isn't logged in.
 */
function save_userdata()
{
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
 * @param  string $username The username to send the email to.
 * @param  string $subject  The subject of the email.
 * @param  string $body     The body of the email.
 * @return boolean          Whether the email was sent successfully or not. Currently, this may fail if the user doesn't have a registered email address.
 */
function email_user($username, $subject, $body)
{
	global $version, $settings;
	
	// If the user doesn't have an email address, then we can't email them :P
	if(empty($settings->users->{$username}->emailAddress))
		return false;
	
	$subject = str_replace("{username}", $username, $subject);
	$body = str_replace("{username}", $username, $body);
	
	$headers = [
		"content-type" => "text/plain",
		"x-mailer" => "$settings->sitename Pepperminty-Wiki/$version PHP/" . phpversion(),
		"reply-to" => "$settings->admindetails_name <$settings->admindetails_email>"
	];
	$compiled_headers = "";
	foreach($headers as $header => $value)
		$compiled_headers .= "$header: $value\r\n";
	
	return mail($settings->users->{$username}->emailAddress, $subject, $body, $compiled_headers, "-t");
}
/**
 * Sends a plain text email to a list of users, replacing {username} with each user's name.
 * @package core
 * @param  string[]	$usernames	A list of usernames to email.
 * @param  string	$subject	The subject of the email.
 * @param  string	$body		The body of the email.
 * @return integer				The number of emails sent successfully.
 */
function email_users($usernames, $subject, $body)
{
	$emailsSent = 0;
	foreach($usernames as $username)
	{
		$emailsSent += email_user($username, $subject, $body) ? 1 : 0;
	}
	return $emailsSent;
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
		$newentry->filename = substr( // Store the filename, whilst trimming the storage prefix
			$pagefilename,
			mb_strlen(preg_replace("/^\.\//iu", "", $env->storage_prefix)) // glob_recursive trim the ./ from returned filenames , so we need to as well
		);
		// Remove the `./` from the beginning if it's still hanging around
		if(substr($newentry->filename, 0, 2) == "./")
			$newentry->filename = substr($newentry->filename, 2);
		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified
		// Todo find a way to keep the last editor independent of the page index
		$newentry->lasteditor = "unknown"; // Set the editor to "unknown"
		// Extract the name of the (sub)page without the ".md"
		$pagekey = mb_substr($newentry->filename, 0, -3);
		
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
/**
 * Provides an interface to interact with page ids.
 * @package core
 */
class ids
{
	/**
	 * Gets the page id associated with the given page name.
	 * If it doesn't exist in the id index, it will be added.
	 * @package core
	 * @param	string	$pagename	The name of the page to fetch the id for.
	 * @return	integer	The id for the specified page name.
	 */
	public static function getid($pagename)
	{
		global $idindex;
		
		$pagename_norm = Normalizer::normalize($pagename, Normalizer::FORM_C);
		foreach ($idindex as $id => $entry)
		{
			// We don't need to normalise here because we normralise when assigning ids
			if($entry == $pagename_norm)
				return $id;
		}
		
		// This pagename doesn't have an id - assign it one quick!
		return self::assign($pagename);
	}

	/**
	 * Gets the page name associated with the given page id.
	 * Be warned that if the id index is cleared (e.g. when the search index is
	 * rebuilt from scratch), the id associated with a page name may change!
	 * @package core
	 * @param	int		$id		The id to fetch the page name for.
	 * @return	string	The page name currently associated with the specified id.
	 */
	public static function getpagename($id)
	{
		global $idindex;

		if(!isset($idindex->$id))
			return false;
		else
			return $idindex->$id;
	}
	
	/**
	 * Moves a page in the id index from $oldpagename to $newpagename.
	 * Note that this function doesn't perform any special checks to make sure
	 * that the destination name doesn't already exist.
	 * @package core
	 * @param	string	$oldpagename	The old page name to move.
	 * @param	string	$newpagename	The new page name to move the old page name to.
	 */
	public static function movepagename($oldpagename, $newpagename)
	{
		global $idindex, $paths;
		
		$pageid = self::getid(Normalizer::normalize($oldpagename, Normalizer::FORM_C));
		$idindex->$pageid = Normalizer::normalize($newpagename, Normalizer::FORM_C);
		
		file_put_contents($paths->idindex, json_encode($idindex));
	}
	
	/**
	 * Removes the given page name from the id index.
	 * Note that this function doesn't handle multiple entries with the same
	 * name. Also note that it may get re-added during a search reindex if the
	 * page still exists.
	 * @package core
	 * @param	string	$pagename	The page name to delete from the id index.
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
	
	/**
	 * Clears the id index completely.
	 * Will break the inverted search index! Make sure you rebuild the search
	 * index (if the search module is installed, of course) if you want search
	 * to still work. Of course, note that will re-add all the pages to the id
	 * index.
	 * @package core
	 */
	public static function clear()
	{
		global $paths, $idindex;
		// Delete the old id index
		unlink($paths->idindex);
		// Create the new id index
		file_put_contents($paths->idindex, "{}");
		// Reset the in-memory id index
		$idindex = new stdClass();
	}

	/**
	 * Assigns an id to a pagename. Doesn't check to make sure that
	 * pagename doesn't already exist in the id index.
	 * @package core
	 * @param	string	$pagename	The page name to assign an id to.
	 * @return	integer				The id assigned to the specified page name.
	 */
	protected static function assign($pagename)
	{
		global $idindex, $paths;
		
		$pagename = Normalizer::normalize($pagename, Normalizer::FORM_C);

		$nextid = count(array_keys(get_object_vars($idindex)));
		// Increment the generated id until it's unique
		while(isset($idindex->nextid))
			$nextid++;
		
		// Update the id index
		$idindex->$nextid = $pagename;

		// Save the id index
		file_put_contents($paths->idindex, json_encode($idindex));

		return $nextid;
	}
}
//////////////////////////
//////////////////////////

// Work around an Opera + Syntaxtic bug where there is no margin at the left
// hand side if there isn't a query string when accessing a .php file.
if(!isset($_GET["action"]) and !isset($_GET["page"]) and basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) == "index.php")
{
	http_response_code(302);
	header("location: " . dirname(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
	exit();
}

// Make sure that the action is set
if(empty($_GET["action"]))
	$_GET["action"] = $settings->defaultaction;
// Make sure that the page is set
if(empty($_GET["page"]) or strlen($_GET["page"]) === 0)
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
/**
 * Renders the HTML page that is sent to the client.
 * @package core
 */
class page_renderer
{
	/**
	 * The root HTML template that all pages are built from.
	 * @var string
	 * @package core
	 */
	public static $html_template = "<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8' />
		<title>{title}</title>
		<meta name='viewport' content='width=device-width, initial-scale=1' />
		<meta name='generator' content='Pepperminty Wiki {version}' />
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
	/**
	 * The main content template that is used to render normal wiki pages.
	 * @var string
	 * @package core
	 */
	public static $main_content_template = "{navigation-bar}
		<h1 class='sitename'>{sitename}</h1>
		<main>
		{content}
		</main>
		{extra}
		<footer>
			<p>{footer-message}</p>
			<p>Powered by Pepperminty Wiki {version}, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or <a href='//github.com/sbrl/Pepperminty-Wiki' title='Github Issue Tracker'>open an issue</a>.</p>
			<p>Your local friendly moderators are {admins-name-list}.</p>
			<p>This wiki is managed by <a href='mailto:{admin-details-email}'>{admin-details-name}</a>.</p>
		</footer>
		{navigation-bar-bottom}
		{all-pages-datalist}";
	/**
	 * A specially minified content template that doesn't include the navbar and
	 * other elements not suitable for printing.
	 * @var string
	 * @package core
	 */
	public static $minimal_content_template = "<main class='printable'>{content}</main>
		<footer class='printable'>
			<hr class='footerdivider' />
			<p><em>From {sitename}, which is managed by {admin-details-name}.</em></p>
			<p>{footer-message}</p>
			<p><em>Timed at {generation-date}</em></p>
			<p><em>Powered by Pepperminty Wiki {version}.</em></p>
		</footer>";
	
	/**
	 * An array of items indicating the resources to ask the web server to push
	 * down to the client with HTTP/2.0 server push.
	 * Format: [ [type, path], [type, path], .... ]
	 * @var array[]
	 */
	protected static $http2_push_items = [];
	
	
	/**
	 * A string of extrar HTML that should be included at the bottom of the page <head>.
	 * @var string
	 */
	private static $extraHeaderHTML = "";
	
	/**
	 * The javascript snippets that will be included in the page.
	 * @var string[]
	 * @package core
	 */
	private static $jsSnippets = [];
	/**
	 * The urls of the external javascript files that should be referenced
	 * by the page.
	 * @var string[]
	 * @package core
	 */
	private static $jsLinks = [];
	
	/**
	 * The navigation bar divider.
	 * @package core
	 * @var string
	 */
	public static $nav_divider = "<span class='nav-divider inflexible'> | </span>";
	
	
	/**
	 * An array of functions that have been registered to process the
	 * find / replace array before the page is rendered. Note that the function
	 * should take a *reference* to an array as its only argument.
	 * @var array
	 * @package core
	 */
	protected static $part_processors = [];

	/**
	 * Registers a function as a part post processor.
	 * This function's use is more complicated to explain. Pepperminty Wiki
	 * renders pages with a very simple templating system. For example, in the
	 * template a page's content is denoted by `{content}`. A function
	 * registered here will be passed all the components of a page _just_
	 * before they are dropped into the template. Note that the function you
	 * pass in here should take a *reference* to the components, as the return
	 * value of the function passed is discarded.
	 * @package core
	 * @param  function $function The part preprocessor to register.
	 */
	public static function register_part_preprocessor($function) {
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
	
	/**
	 * Renders a HTML page with the content specified.
	 * @package core
	 * @param  string  $title         The title of the page.
	 * @param  string  $content       The (HTML) content of the page.
	 * @param  boolean $body_template The HTML content template to use.
	 * @return string                 The rendered HTML, ready to send to the client :-)
	 */
	public static function render($title, $content, $body_template = false)
	{
		global $settings, $start_time, $version;

		if($body_template === false)
			$body_template = self::$main_content_template;

		if(strlen($settings->logo_url) > 0) {
			// A logo url has been specified
			$logo_html = "<img class='logo" . (isset($_GET["printable"]) ? " small" : "") . "' src='$settings->logo_url' />";
			switch($settings->logo_position) {
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
		
		// Push the logo via HTTP/2.0 if possible
		if($settings->favicon[0] === "/") self::$http2_push_items[] = ["image", $settings->favicon];

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

			"{admins-name-list}" => implode(", ", array_map(function($username) { return page_renderer::render_username($username); }, $settings->admins)),

			"{generation-date}" => date("l jS \of F Y \a\\t h:ia T"),

			"{all-pages-datalist}" => self::generate_all_pages_datalist(),

			"{footer-message}" => $settings->footer_message,

			/// Secondary Parts ///

			"{content}" => $content,
			"{extra}" => "",
			"{title}" => $title,
		];

		// Pass the parts through the part processors
		foreach(self::$part_processors as $function) {
			$function($parts);
		}

		$result = self::$html_template;

		$result = str_replace(array_keys($parts), array_values($parts), $result);

		$result = str_replace("{generation-time-taken}", round((microtime(true) - $start_time)*1000, 2), $result);
		// Send the HTTP/2.0 server push indicators if possible - but not if we're sending a redirect page
		if(!headers_sent() && (http_response_code() < 300 || http_response_code() >= 400)) self::send_server_push_indicators();
		return $result;
	}
	/**
	 * Renders a normal HTML page.
	 * @package core
	 * @param  string $title   The title of the page.
	 * @param  string $content The content of the page.
	 * @return string          The rendered page.
	 */
	public static function render_main($title, $content) {
		return self::render($title, $content, self::$main_content_template);
	}
	/**
	 * Renders a minimal HTML page. Useful for printable pages.
	 * @package core
	 * @param  string $title   The title of the page.
	 * @param  string $content The content of the page.
	 * @return string          The rendered page.
	 */
	public static function render_minimal($title, $content) {
		return self::render($title, $content, self::$minimal_content_template);
	}
	
	/**
	 * Sends the currently registered HTTP2 server push items to the client.
	 * @return integer|FALSE	The number of resource hints included in the link: header, or false if server pushing is disabled.
	 */
	public static function send_server_push_indicators() {
		global $settings;
		if(!$settings->http2_server_push)
			return false;
		
		// Render the preload directives
		$link_header_parts = [];
		foreach(self::$http2_push_items as $push_item)
			$link_header_parts[] = "<{$push_item[1]}>; rel=preload; as={$push_item[0]}";
		
		// Send them in a link: header
		if(!empty($link_header_parts))
			header("link: " . implode(", ", $link_header_parts));
		
		return count(self::$http2_push_items);
	}
	
	/**
	 * Renders the header HTML.
	 * @package core
	 * @return string The rendered HTML that goes in the header.
	 */
	public static function get_header_html()
	{
		global $settings;
		$result = self::$extraHeaderHTML;
		$result .= self::get_css_as_html();
		$result .= self::_get_js();
		
		// We can't use module_exists here because sometimes global $modules
		// hasn't populated yet when we get called O.o
		if(class_exists("search"))
			$result .= "\t\t<link rel='search' type='application/opensearchdescription+xml' href='?action=opensearch-description' title='$settings->sitename Search' />\n";
		
		if(!empty($settings->enable_math_rendering)) {
			$result .= "<script type='text/x-mathjax-config'>
		MathJax.Hub.Config({
			tex2jax: {
				inlineMath: [ ['$','$'], ['\\\\(','\\\\)'] ],
				processEscapes: true,
				skipTags: ['script','noscript','style','textarea','pre','code']
			}
		});
	</script>";
		}
		
		return $result;
	}
	/**
	 * Figures out whether $settings->css is a url, or a string of css.
	 * A url is something starting with "protocol://" or simply a "/".
	 * @return	boolean	True if it's a url - false if we assume it's a string of css.
	 */
	public static function is_css_url() {
		global $settings;
		return preg_match("/^[^\/]*\/\/|^\//", $settings->css);
	}
	/**
	 * Renders all the CSS as HTML.
	 * @package core
	 * @return string The css as HTML, ready to be included in the HTML header.
	 */
	public static function get_css_as_html()
	{
		global $settings, $defaultCSS;

		if(self::is_css_url()) {
			if($settings->css[0] === "/") // Push it if it's a relative resource
				self::add_server_push_indicator("style", $settings->css);
			return "<link rel='stylesheet' href='$settings->css' />\n";
		} else {
			$css = $settings->css == "auto" ? $defaultCSS : $settings->css;
			if(!empty($settings->optimize_pages)) {
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
	
	
	/**
	 * Adds the specified url to a javascript file as a reference to the page.
	 * @package core
	 * @param string $scriptUrl The url of the javascript file to reference.
	 */
	public function add_js_link(string $scriptUrl) {
		static::$jsLinks[] = $scriptUrl;
	}
	/**
	 * Adds a javascript snippet to the page.
	 * @package core
	 * @param string $script The snippet of javascript to add.
	 */
	public function add_js_snippet(string $script) {
		static::$jsSnippets[] = $script;
	}
	/**
	 * Renders the included javascript header for inclusion in the final
	 * rendered page.
	 * @package core
	 * @return	string	The rendered javascript ready for inclusion in the page.
	 */
	private static function _get_js() {
		$result = "<!-- Javascript -->\n";
		foreach(static::$jsSnippets as $snippet)
			$result .= "<script defer>\n$snippet\n</script>\n";
		foreach(static::$jsLinks as $link) {
			// Push it via HTTP/2.0 if it's relative
			if($link[0] === "/") self::add_server_push_indicator("script", $link);
			$result .= "<script src='" . $link . "' defer></script>\n";
		}
		return $result;
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	/**
	 * Adds a string of HTML to the header of the rendered page.
	 * @param string $html The string of HTML to add.
	 */
	public static function add_header_html($html) {
		self::$extraHeaderHTML .= $html;
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	/**
	 * Adds a resource to the list of items to indicate that the web server should send via HTTP/2.0 Server Push.
	 * Note: Only specify static files here, as you might end up with strange (and possibly dangerous) results!
	 * @param string $type The resource type. See https://fetch.spec.whatwg.org/#concept-request-destination for more information.
	 * @param string $path The *relative url path* to the resource.
	 */
	public static function add_server_push_indicator($type, $path) {
		self::$http2_push_items[] = [ $type, $path ];
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	
	/**
	 * Renders a navigation bar from an array of links. See
	 * $settings->nav_links for format information.
	 * @package core
	 * @param array	$nav_links			The links to add to the navigation bar.
	 * @param array	$nav_links_extra	The extra nav links to add to
	 *                               	the "More..." menu.
	 * @param string $class				The class(es) to assign to the rendered
	 * 									navigation bar.
	 */
	public static function render_navigation_bar($nav_links, $nav_links_extra, $class = "") {
		global $settings, $env;
		$result = "<nav class='$class'>\n";

		// Loop over all the navigation links
		foreach($nav_links as $item) {
			if(!is_string($item)) {
				// Output the item as a link to a url
				$result .= "<span><a href='" . str_replace("{page}", rawurlencode($env->page), $item[1]) . "'>$item[0]</a></span>";
				continue;
			}
			
			// The item is a string
			switch($item) {
				//keywords
				case "user-status": // Renders the user status box
					if($env->is_logged_in) {
						$result .= "<span class='inflexible logged-in" . ($env->is_logged_in ? " moderator" : " normal-user") . "'>";
						if(module_exists("feature-user-preferences")) {
							$result .= "<a href='?action=user-preferences'>$settings->user_preferences_button_text</a>";
						}
						$result .= self::render_username($env->user);
						$result .= " <small>(<a href='index.php?action=logout'>Logout</a>)</small>";
						$result .= "</span>";
						//$result .= page_renderer::$nav_divider;
					}
					else {
						$returnto_url = $env->action !== "logout" ? $_SERVER["REQUEST_URI"] : "?action=view&page=" . rawurlencode($settings->defaultpage);
						$result .= "<span class='not-logged-in'><a href='index.php?action=login&returnto=" . rawurlencode($returnto_url) . "'>Login</a></span>";
					}
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

		$result .= "</nav>";
		return $result;
	}
	/**
	 * Renders a username for inclusion in a page.
	 * @package core
	 * @param  string $name The username to render.
	 * @return string       The username rendered in HTML.
	 */
	public static function render_username($name) {
		global $settings;
		$result = "";
		$result .= "<a href='?page=" . rawurlencode(get_user_pagename($name)) . "'>";
		if($settings->avatars_show)
			$result .= "<img class='avatar' src='?action=avatar&user=" . urlencode($name) . "&size=$settings->avatars_size' /> ";
		if(in_array($name, $settings->admins))
			$result .= $settings->admindisplaychar;
		$result .= htmlentities($name);
		$result .= "</a>";

		return $result;
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
	/**
	 * Renders the datalist for the search box as HTML.
	 * @package core
	 * @return string The search box datalist as HTML.
	 */
	public static function generate_all_pages_datalist() {
		global $settings, $pageindex;
		$arrayPageIndex = get_object_vars($pageindex);
		ksort($arrayPageIndex);
		$result = "<datalist id='allpages'>\n";
		
		// If dynamic page sugggestions are enabled, then we should send a loading message instead.
		if($settings->dynamic_page_suggestion_count > 0) {
			$result .= "<option value='Loading suggestions...' />";
		} else {
			foreach($arrayPageIndex as $pagename => $pagedetails) {
				$escapedPageName = str_replace('"', '&quot;', $pagename);
				$result .= "\t\t\t<option value=\"$escapedPageName\" />\n";
			}
		}
		$result .= "\t\t</datalist>";

		return $result;
	}
}

// HTTP/2.0 Server Push static items
foreach($settings->http2_server_push_items as $push_item) {
	page_renderer::add_server_push_indicator($push_item[0], $push_item[1]);
}

// Math rendering support
if(!empty($settings->enable_math_rendering))
{
	page_renderer::add_js_link("https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-MML-AM_CHTML");
}
// alt+enter support in the search box
page_renderer::add_js_snippet('// Alt + Enter support in the top search box
window.addEventListener("load", function(event) {
	document.querySelector("input[type=search]").addEventListener("keyup", function(event) {
		// Listen for Alt + Enter
		if(event.keyCode == 13 && event.altKey) {
			event.stopPropagation();
			event.preventDefault();
			event.cancelBubble = true;
			event.target.form.setAttribute("target", "_blank");
			event.target.form.submit();
			event.target.form.removeAttribute("target");
			return false; // Required by some browsers
		}
	});
});
');

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

// CHANGED: The search redirector has now been moved to below the module registration system, as it was causing a warning here

// Redirect the user to the login page if:
//  - A login is required to view this wiki
//  - The user isn't already requesting the login page
// Note we use $_GET here because $env->action isn't populated at this point
if($settings->require_login_view === true && // If this site requires a login in order to view pages
   !$env->is_logged_in && // And the user isn't logged in
   !in_array($_GET["action"], [ "login", "checklogin", "opensearch-description", "invindex-rebuild", "stats-update" ])) // And the user isn't trying to login, or get the opensearch description, or access actions that apply their own access rules
{
	// Redirect the user to the login page
	http_response_code(307);
	header("x-login-required: yes");
	$url = "?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "&required=true";
	header("location: $url");
	exit(page_renderer::render("Login required - $settings->sitename", "<p>$settings->sitename requires that you login before you are able to access it.</p>
		<p><a href='$url'>Login</a>.</p>"));
}
//////////////////////////////////////
//////////////////////////////////////

$remote_files = [];
/**
 * Registers a request for a remote file to be downloaded before execution. Will block until all files are downloaded.
 * Example definition:
 *     [ "local_filename" => "file.ext", "remote_url": "https://example.com" ]
 * @param	array		$remote_file_def	The remote file definition to register.
 * @throws	Exception	Exception			Throws an exception if a definition for the requested local file already exists.
 */
function register_remote_file($remote_file_def) {
	global $remote_files;
	
	foreach($remote_files as $ex_remote_file_def) {
		if($ex_remote_file_def["local_filename"] == $remote_file_def["local_filename"])
			throw new Exception("Error: A remote file with the local filename '{$remote_file_def["local_filename"]}' is already registered.");
	}
	
	$remote_files[] = $remote_file_def;
}

//////////////////////////
///  Module functions  ///
//////////////////////////
// These functions are	//
// used by modules to	//
// register themselves	//
// or new pages.		//
//////////////////////////
/** A list of all the currently loaded modules. Not guaranteed to be populated until an action is executed. */
$modules = [];
/**
 * Registers a module.
 * @package core
 * @param  array	$moduledata	The module data to register.
 */
function register_module($moduledata)
{
	global $modules;
	//echo("registering module\n");
	//var_dump($moduledata);
	$modules[] = $moduledata;
}
/**
 * Checks to see whether a module with the given id exists.
 * @package core
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

$actions = new stdClass();
/**
 * Registers a new action handler.
 * @package core
 * @param	string		$action_name	The action to register.
 * @param	function	$func			The function to call when the specified
 * 										action is requested.
 */
function add_action($action_name, $func)
{
	global $actions;
	$actions->$action_name = $func;
}
/**
 * Figures out whether a given action is currently registered.
 * Only guaranteed to be accurate in inside an existing action function
 * @package core
 * @param  string  $action_name The name of the action to search for
 * @return boolean              Whether an action with the specified name exists.
 */
function has_action($action_name)
{
	global $actions;
	return !empty($actions->$action_name);
}

$parsers = [
	"none" => function() {
		throw new Exception("No parser registered!");
	}
];
/**
 * Registers a new parser.
 * @package core
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
/**
 * Parses the specified page source using the parser specified in the settings
 * into HTML.
 * The specified parser may (though it's unlikely) render it to other things.
 * @package core
 * @param	string	$source		The source to render.
 * @param	string	$use_cache	Whether to use the on-disk cache. Has no effect if parser caching is disabled in peppermint.json, or the source string is too small.
 * @return	string	The source rendered to HTML.
 */
function parse_page_source($source, $use_cache = true) {
	global $settings, $paths, $parsers, $version;
	$start_time = microtime(true);
	
	if(!$settings->parser_cache || strlen($source) < $settings->parser_cache_min_size) $use_cache = false;
	
	if(!isset($parsers[$settings->parser]))
		exit(page_renderer::render_main("Parsing error - $settings->sitename", "<p>Parsing some page source data failed. This is most likely because $settings->sitename has the parser setting set incorrectly. Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>" . $settings->admindetails_name . "</a>, your $settings->sitename Administrator."));
	
/* Not needed atm because escaping happens when saving, not when rendering *
	if($settings->clean_raw_html)
		$source = htmlentities($source, ENT_QUOTES | ENT_HTML5);
*/
	
	$cache_id = str_replace(["+","/"], ["-","_"], base64_encode(hash("sha256", "$version|$settings->parser|$source", true)));
	$cache_file = "{$paths->cache_directory}/{$cache_id}.html";
	
	$result = null;
	if($use_cache && file_exists($cache_file)) {
		$result = file_get_contents($cache_file);
		$result .= "\n<!-- cache: hit, id: $cache_id, took: " . round((microtime(true) - $start_time)*1000, 5) . "ms -->\n";
	}
	if($result == null) {
		$result = $parsers[$settings->parser]($source);
		// If we should use the cache and we failed to write to it, warn the admin.
		// It's not terribible if we can't write to the cache directory (so we shouldn't stop dead & refuse service), but it's still of concern.
		if($use_cache && !file_put_contents($cache_file, $result))
			error_log("[Pepperminty Wiki] Warning: Failed to write to cache file $cache_file.");
		
		$result .= "\n<!-- cache: " . ($use_cache ? "miss" : "n/a") . ", id: $cache_id, took: " . round((microtime(true) - $start_time)*1000, 5) . "ms -->\n";
	}
	
	return $result;
}

// Function to 
$save_preprocessors = [];
/**
 * Register a new proprocessor that will be executed just before
 * an edit is saved.
 * @package core
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
 * @package core
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
	add_help_section("22-mathematical-mxpressions", "Mathematical Expressions", "<p>$settings->sitename supports rendering of mathematical expressions. Mathematical expressions can be included practically anywhere in your page. Expressions should be written in LaTeX and enclosed in dollar signs like this: <code>&#36;x^2&#36;</code>.</p>
	<p>Note that expression parsing is done on the viewer's computer with javascript (specifically MathJax) and not by $settings->sitename directly (also called client side rendering).</p>");

/** An array of the currently registerd statistic calculators. Not guaranteed to be populated until the requested action function is called. */
$statistic_calculators = [];
/**
 * Registers a statistic calculator against the system.
 * @package core
 * @param	array	$stat_data	The statistic object to register.
 */
function statistic_add($stat_data) {
	global $statistic_calculators;
	$statistic_calculators[$stat_data["id"]] = $stat_data;
}
/**
 * Checks whether a specified statistic has been registered.
 * @package core
 * @param  string  $stat_id The id of the statistic to check the existence of.
 * @return boolean          Whether the specified statistic has been registered.
 */
function has_statistic($stat_id) {
	global $statistic_calculators;
	return !empty($statistic_calculators[$stat_id]);
}

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

// Download all the requested remote files
ini_set("user_agent", "$settings->sitename (Pepperminty-Wiki-Downloader; PHP/" . phpversion() . "; +https://github.com/sbrl/Pepperminty-Wiki/) Pepperminty-Wiki/$version");
foreach($remote_files as $remote_file_def) {
	if(file_exists($remote_file_def["local_filename"]) && filesize($remote_file_def["local_filename"]) > 0)
		continue;
	
	error_log("[ Pepperminty-Wiki/$settings->sitename ] Downloading {$remote_file_def["local_filename"]} from {$remote_file_def["remote_url"]}");
	file_put_contents($remote_file_def["local_filename"], fopen($remote_file_def["remote_url"], "rb"));
}

//////////////////////////////////
/// Final Consistency Measures ///
//////////////////////////////////

if(!isset($pageindex->{$env->page}) && isset($pageindex->{ucwords($env->page)})) {
	http_response_code(307);
	header("location: ?page=" . ucwords($env->page));
	header("content-type: text/plain");
	exit("$env->page doesn't exist on $settings->sitename, but " . ucwords($env->page) . " does. You should be redirected there automatically.");
}

// Redirect to the search page if there isn't a page with the requested name
if(!isset($pageindex->{$env->page}) and isset($_GET["search-redirect"]))
{
	http_response_code(307);
	$url = "?action=search&query=" . rawurlencode($env->page);
	header("location: $url");
	exit(page_renderer::render_minimal("Non existent page - $settings->sitename", "<p>There isn't a page on $settings->sitename with that name. However, you could <a href='$url'>search for this page name</a> in other pages.</p>
		<p>Alternatively, you could <a href='?action=edit&page=" . rawurlencode($env->page) . "&create=true'>create this page</a>.</p>"));
}

//////////////////////////////////


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
