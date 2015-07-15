<?php
$start_time = time(true);

{settings}

///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "0.6";
session_start();
///////// Login System /////////
//clear expired sessions
if(isset($_SESSION["$settings->sessionprefix-expiretime"]) and
   $_SESSION["$settings->sessionprefix-expiretime"] < time())
{
	//clear the session variables
	$_SESSION = [];
	session_destroy();
}

if(!isset($_SESSION[$settings->sessionprefix . "-user"]) and
  !isset($_SESSION[$settings->sessionprefix . "-pass"]))
{
	//the user is not logged in
	$isloggedin = false;
}
else
{
	$user = $_SESSION[$settings->sessionprefix . "-user"];
	$pass = $_SESSION[$settings->sessionprefix . "-pass"];
	if($settings->users[$user] == $pass)
	{
		//the user is logged in
		$isloggedin = true;
	}
	else
	{
		//the user's login details are invalid (what is going on here?)
		//unset the session variables, treat them as an anonymous user, and get out of here
		$isloggedin = false;
		unset($user);
		unset($pass);
		//clear the session data
		$_SESSION = []; //delete al lthe variables
		session_destroy(); //destroy the session
	}
}
//check to see if the currently logged in user is an admin
$isadmin = false;
if($isloggedin)
{
	foreach($settings->admins as $admin_username)
	{
		if($admin_username == $user)
		{
			$isadmin = true;
			break;
		}
	}
}
/////// Login System End ///////

///////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////// Security and Consistency Measures ////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////
/*
 * @summary Gets a list of all the sub pagess of the current page.
 * 
 * @param $pageindex - The pageindex to use to search.
 * @param $pagename - The name of the page to list the sub pages of.
 * 
 * @returns An objectt containing all the subpages, and their respective distances from the given page name in the pageindex tree.
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

/*
 * @summary Makes sure that a subpage's parents exist. Note this doesn't check the pagename itself.
 * 
 * @param The pagename to check.
 * 
 */
function check_subpage_parents($pagename)
{
	global $pageindex;
	// Save the new pageindex and return if there aren't any more parent pages to check
	if(strpos($pagename, "/") === false)
	{
		file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
		return;
	}
	
	$parent_pagename = substr($pagename, 0, strrpos($pagename, "/"));
	$parent_page_filename = "$parent_pagename.md";
	if(!file_exists($parent_page_filename))
	{
		// This parent page doesn't exist! Create it and add it to the page index.
		touch($parent_page_filename, 0);
		
		$newentry = new stdClass();
		$newentry->filename = $parent_page_filename;
		$newentry->size = 0;
		$newentry->lastmodified = 0;
		$newentry->lasteditor = "none";
		$pageindex->$parent_pagename = $newentry;
	}
	
	check_subpage_parents($parent_pagename);
}

if(!file_exists("./pageindex.json"))
{
	// From http://in.php.net/manual/en/function.glob.php#106595
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
	$existingpages = glob_recursive("*.md");
	$pageindex = new stdClass();
	// We use a for loop here because foreach doesn't loop over new values inserted
	// while we were looping
	for($i = 0; $i < count($existingpages); $i++)
	{
		$pagefilename = $existingpages[$i];
		
		// Create a new entry
		$newentry = new stdClass();
		$newentry->filename = utf8_encode($pagefilename); // Store the filename
		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified
		// Todo find a way to keep the last editor independent of the page index
		$newentry->lasteditor = utf8_encode("unknown"); // Set the editor to "unknown"
		// Extract the name of the (sub)page without the ".md"
		$pagekey = utf8_encode(substr($pagefilename, 0, -3));
		
		// Subpage parent checker
		if(strpos($pagekey, "/") !== false)
		{
			// We have a sub page people
			// Work out what our direct parent's key must be in order to check to
			// make sure that it actually exists. If it doesn't, then we need to
			// create it.
			$subpage_parent_key = substr($pagekey, 0, strrpos($pagekey, "/"));
			$subpage_parent_filename = "$subpage_parent_key.md";
			if(array_search($subpage_parent_filename, $existingpages) === false)
			{
				// Our parent page doesn't acutally exist - create it
				touch($subpage_parent_filename, 0);
				// Furthermore, we should add this page to the list of existing pages
				// in order for it to be indexed
				$existingpages[] = $subpage_parent_filename;
			}
		}
		
		// Store the new entry in the new page index
		$pageindex->$pagekey = $newentry;
	}
	file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
	unset($existingpages);
}
else
{
	$pageindex = json_decode(file_get_contents("./pageindex.json"));
}
/*
 * @summary makes a path safe
 * 
 * @details paths may only contain alphanumeric characters, spaces, underscores, and dashes
 */
function makepathsafe($string)
{
	return preg_replace("/[^0-9a-zA-Z\_\-\ \/]/i", "", $string);
}

/*
 * @summary Hides an email address from bots by adding random html entities.
 * 
 * @returns The mangled email address.
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

// Work around an Opera + Syntastic bug where there is no margin at the left hand side if there isn't a query string when accessing a .php file
if(!isset($_GET["action"]) and !isset($_GET["page"]))
{
	http_response_code(302);
	header("location: index.php?action=$settings->defaultaction&page=$defaultpage");
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
$page = $_GET["page"];

///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// HTML fragments //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
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
		<!-- Took {generation-time-taken} seconds to generate -->
	</body>
</html>
";
	
	public static $main_content_template = "{navigation-bar}
		<h1 class='sitename'>{sitename}</h1>
		<main>
		{content}
		</main>
		
		<footer>
			<p>Powered by Pepperminty Wiki, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or open an issue <a href='//github.com/sbrl/Pepperminty-Wiki'>on github</a>.</p>
			<p>Your local friendly administrators are {admins-name-list}.
			<p>This wiki is managed by <a href='mailto:{admin-details-email}'>{admin-details-name}</a>.</p>
		</footer>
		{all-pages-datalist}";
	public static $minimal_content_template = "{content}
		<hr class='footerdivider' />
		<p><em>From {sitename}, which is managed by {admin-details-name}.</em></p>
		<p><em>Timed at {generation-date}</em>
		<p><em>Powered by Pepperminty Wiki.</em></p>";
	
	public static function render($title, $content, $body_template)
	{
		global $settings, $start_time;
		
		$result = self::$html_template;
		$result = str_replace("{body}", $body_template, $result);
		$result = str_replace([
			"{sitename}",
			"{favicon-url}",
			"{header-html}",
			
			"{navigation-bar}",
			
			"{admin-details-name}",
			"{admin-details-email}",
			
			"{admins-name-list}",
			
			"{generation-date}",
			
			"{all-pages-datalist}"
		], [
			$settings->sitename,
			$settings->favicon,
			self::get_css_as_html(),
			
			self::render_navigation_bar(),
			
			$settings->admindetails["name"],
			$settings->admindetails["email"],
			
			implode(", ", $settings->admins),
			
			date("l jS \of F Y \a\\t h:ia T"),
			
			self::generate_all_pages_datalist()
		], $result);
		
		$result = str_replace([
			"{title}",
			"{content}"
		], [
			$title,
			$content
		], $result);
		
		$result = str_replace("{generation-time-taken}", microtime(true) - $start_time, $result);
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
	
	
	public static function get_css_as_html()
	{
		global $settings;
		
		if(preg_match("/^[^\/]*\/\/|^\//", $settings->css))
			return "<link rel='stylesheet' href='$settings->css' />";
		else
			return "<style>$settings->css</style>";
	}
	
	public static $nav_divider = "<span class='nav-divider inflexible'> | </span>";
	
	public static function render_navigation_bar()
	{
		global $settings, $user, $isloggedin, $page;
		$result = "<nav>\n";
		
		if($isloggedin)
		{
			$result .= "<span class='inflexible'>Logged in as " . self::render_username($user) . ".</span> "/* . page_renderer::$nav_divider*/;
			$result .= "<span><a href='index.php?action=logout'>Logout</a></span>";
			$result .= page_renderer::$nav_divider;
		}
		else
			$result .= "<span class='inflexible'>Browsing as Anonymous.</span>" . /*page_renderer::$nav_divider . */"<span><a href='index.php?action=login'>Login</a></span>" . page_renderer::$nav_divider;
		
		// Loop over all the navigation links
		foreach($settings->navlinks as $item)
		{
			if(is_string($item))
			{
				// The item is a string
				switch($item)
				{
					//keywords
					case "search": // Displays a search bar
						$result .= "<span class='inflexible'><form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /></form></span>";
						break;
					
					case "divider": // Displays a divider
						$result .= page_renderer::$nav_divider;
						break;
					
					case "menu":
						$result .= "<span class='inflexible nav-more'>More...</span>";
						// todo Add the submenu
						break;
					
					// It isn't a keyword, so just output it directly
					default:
						$result .= "<span>$item</span>";
				}
			}
			else
			{
				// Output the item as a link to a url
				$result .= "<span><a href='" . str_replace("{page}", $page, $item[1]) . "'>$item[0]</a></span>";
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
		
		$result = "<datalist id='allpages'>\n";
		foreach($pageindex as $pagename => $pagedetails)
		{
			$result .= "\t\t\t<option value='$pagename' />\n";
		}
		$result .= "\t\t</datalist>";
		
		return $result;
	}
}

///////////////////////////////////////////
//////////////// Functions ////////////////
///////////////////////////////////////////
//from http://php.net/manual/en/function.filesize.php#106569
//edited by Starbeamrainbowlabs
function human_filesize($bytes, $decimals = 2)
{
	$sz = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "YB", "ZB"];
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
//from http://snippets.pro/snippet/137-php-convert-the-timestamp-to-human-readable-format/
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
///////////////////////////////////////////

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

// Function to register an action handler
$actions = new stdClass();
function add_action($action_name, $func)
{
	global $actions;
	//echo("adding $action_name\n");
	$actions->$action_name = $func;
}

// Function to register a new parser. If multiple parsers are registered then
// only the last parser registered will actually be used.
$parse_page_source = function() {
	throw new Exception("No parser registered!");
};
function add_parser($parser_code)
{
	global $parse_page_source;
	$parse_page_source = $parser_code;
}

//////////////////////////////////////////////////////////////////


// %next_module% //


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
$action_name = strtolower($_GET["action"]);
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
