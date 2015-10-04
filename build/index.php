<?php
$start_time = time(true);


/*
 * Pepperminty Wiki
 * ================
 * Inspired by Minty Wiki by am2064:
	* Link: https://github.com/am2064/Minty-Wiki
 *
 * Credits:
	* Code by @Starbeamrainbowlabs
	* Slimdown - by Johnny Broadway from https://gist.github.com/jbroadway/2836900
 * Bug reports:
	* #2 - Incorrect closing tag - nibreh <https://github.com/nibreh/>
	* #8 - Rogue <datalist /> tag - nibreh <https://github.com/nibreh/>
 */
// Initialises a new object to store your wiki's settings in. Please don't touch this.
$settings = new stdClass();

// The site's name. Used all over the place.
// Note that by default the session cookie is perfixed with a variant of the sitename so changing this will log everyone out!
$settings->sitename = "Pepperminty Wiki";

// The url from which to fetch updates. Defaults to the master (development)
// branch If there is sufficient demand, a separate stable branch will be
// created. Note that if you use the automatic updater currently it won't save
// your module choices.
// MAKE SURE THAT THIS POINTS TO A *HTTPS* URL, OTHERWISE SOMEONE COULD INJECT A VIRUS INTO YOUR WIKI
$settings->updateurl = "https://raw.githubusercontent.com/sbrl/pepperminty-wiki/master/index.php";

// The secret key used to perform 'dangerous' actions, like updating the wiki,
// and deleting pages. It is strongly advised that you change this!
$settings->sitesecret = "ed420502615bac9037f8f12abd4c9f02";

// Determined whether edit is enabled. Set to false to disable disting for all
// users (anonymous or otherwise).
$settings->editing = true;

// The maximum number of characters allowed in a single page. The default is
// 135,000 characters, which is about 50 pages.
$settings->maxpagesize = 135000;

// Whether page sources should be cleaned of HTML before rendering. If set to
// true any raw HTML will be escaped before rendering. Note that this shouldn't
// affect code blocks - they should alwys be escaped. It is STRONGLY
// recommended that you keep this option turned on, *ESPECIALLY* if you allow
// anonymous edits as no sanitizing what so ever is performed on the HTML.
// Also note that some parsers may override this setting and escape HTML
// sequences anyway.
$settings->clean_raw_html = true;

// Determined whether users who aren't logged in are allowed to edit your wiki.
// Set to true to allow anonymous users to log in.
$settings->anonedits = false;

// The name of the page that will act as the home page for the wiki. This page
// will be served if the user didn't specify a page.
$settings->defaultpage = "Main Page";

// The default action. This action will be performed if no other action is
// specified. It is recommended you set this to "view" - that way the user
// automatically views the default page (see above).
$settings->defaultaction = "view";

// The parser to use when rendering pages. Defaults to 'default', which is a
// modified version of slimdown, originally written by
// Johnny Broadway <johnny@johnnybroadway.com>.
$settings->parser = "default";

// Whether to show a list of subpages at the bottom of the page.
$settings->show_subpages = true;

// The depth to which we should display when listing subpages at the bottom of
// the page.
$settings->subpages_display_depth = 3;

// An array of usernames and passwords - passwords should be hashed with
// sha256. Put one user / password on each line, remembering the comma at the
// end. The last user in the list doesn't need a comma after their details though.
$settings->users = [
	"admin" => "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8", //password
	"user" => "873ac9ffea4dd04fa719e8920cd6938f0c23cd678af330939cff53c3d2855f34" //cheese
];

// An array of usernames that are administrators. Administrators can delete and
// move pages.
$settings->admins = [ "admin" ];

// The string that is prepended before an admin's name on the nav bar. Defaults
// to a diamond shape (&#9670;).
$settings->admindisplaychar = "&#9670;";

// The string that is prepended a page's name in the page title if it is
// protected. Defaults to a lock symbol.
$settings->protectedpagechar = "&#128274;";

// Contact details for the site administrator. Since users can only be added by
// editing this file, people will need a contact address to use to ask for an
// account. Displayed at the bottom of the page, and will be appropriately
// obfusticated to deter spammers.
$settings->admindetails = [
	"name" => "Administrator",
	"email" => "admin@localhost"
];

// Whether to only allow adminstrators to export the your wiki as a zip using
// the page-export module.
$settings->export_allow_only_admins = false;

// Array of links and display text to display at the top of the site.
// Format:
//		[ "Display Text", "Link" ]
// You can also use strings here and they will be printed as-is, except the
// following special strings:
// 		user-status		Expands to the user's login information
//						e.g. "Logged in as {name}. | Logout".
//						e.g. "Browsing as Anonymous. | Login".
//		
//		search			Expands to a search box.
//		
//		divider			Expands to a divider to separate stuff.
//		
//		more			Expands to the "More..." submenu.
$settings->nav_links = [
	"user-status",
	[ "Home", "index.php" ],
//	[ "Login", "index.php?action=login" ],
	"search",
	[ "Read", "index.php?page={page}" ],
	[ "Edit", "index.php?action=edit&page={page}" ],
	[ "Printable", "index.php?action=view&printable=yes&page={page}" ],
	//"divider",
	[ "All&nbsp;Pages", "index.php?action=list" ],
	"menu"
];
// An array of additional links in the above format that will be shown under
// "More" subsection.
$settings->nav_links_extra = [
	[ $settings->admindisplaychar . "Delete", "index.php?action=delete&page={page}" ],
	[ $settings->admindisplaychar . "Move", "index.php?action=move&page={page}" ],
	[ $settings->admindisplaychar . "Toggle Protection", "index.php?action=protect&page={page}" ]
];

// An array of links in the above format that will be shown at the bottom of
// the page.
$settings->nav_links_bottom = [
	[ "Credits", "index.php?action=credits" ],
	[ "Help", "index.php?action=help" ]
];

// A message that will appear at the bottom of every page. May contain HTML.
$settings->footer_message = "All content is under <a href='?page=License' target='_blank'>this license</a>. Please make sure that you read and understand the license, especially if you are thinking about copying some (or all) of this site's content, as it may restrict you from doing so.";

// A message that will appear just before the submit button on the editing
// page. May contain HTML.
$settings->editing_message = "By submitting your edit, you are agreeing to release your changes under <a href='?action=view&page=License' target='_blank'>this license</a>. Also note that if you don't want your work to be edited by other users of this site, please don't submit it here!";

// A string of css to include. Will be included in the <head> of every page
// inside a <style> tag. This may also be a url - urls will be referenced via a
// <link rel='stylesheet' /> tag.
$settings->css = "body { margin: 2rem 0; font-family: sans-serif; color: #111111; background: #eee8f2; }

nav { display: flex; background-color: #8a62a7; color: #ffa74d;  }
nav.top { position: absolute; top: 0; left: 0; right: 0; box-shadow: inset 0 -0.6rem 0.8rem -0.5rem rgba(50, 50, 50, 0.5); }
nav.bottom { position: absolute; left: 0; right: 0; box-shadow: inset 0 0.8rem 0.8rem -0.5rem rgba(50, 50, 50, 0.5); }

nav > span { flex: 1; text-align: center; line-height: 2; display: inline-block; margin: 0; padding: 0.3rem 0.5rem; border-left: 3px solid #442772; border-right: 3px solid #442772; }
nav:not(.nav-more-menu) a { text-decoration: none; font-weight: bolder; color: inherit; }
.nav-divider { color: transparent; }

.nav-more { position: relative; background-color: #442772; }
.nav-more label { cursor: pointer; }
.nav-more-menu { display: none; position: absolute; flex-direction: column; top: 2.6rem; right: -0.2rem; background-color: #8a62a7; border-top: 3px solid #442772; border-bottom: 3px solid #442772;}
input[type=checkbox]:checked ~ .nav-more-menu { display: block; box-shadow: 0.4rem 0.4rem 1rem 0 rgba(50, 50, 50, 0.5); }
.nav-more-menu span { min-width: 8rem; }

.inflexible { flex: none; }
.off-screen { position: absolute; top: -1000px; left: -1000px;}

input[type=search] { width: 14rem; padding: 0.3rem 0.4rem; font-size: 1rem; color: white; background: rgba(255, 255, 255, 0.4); border: 0; border-radius: 0.3rem; }
input[type=search]::-webkit-input-placeholder { color : rgba(255, 255, 255, 0.75); }
input[type=button], input[type=submit] { cursor: pointer; }


.sidebar { position: relative; z-index: 100; margin-top: 0.6rem; padding: 1rem 3rem 2rem 0.4rem; background: #9e7eb4; box-shadow: inset -0.6rem 0 0.8rem -0.5rem rgba(50, 50, 50, 0.5); }
.sidebar a { color: #ffa74d; }

.sidebar ul { position: relative; margin: 0.3rem 0.3rem 0.3rem 1rem; padding: 0.3rem 0.3rem 0.3rem 1rem; list-style-type: none; }
.sidebar li { position: relative; margin: 0.3rem; padding: 0.3rem; }

.sidebar ul:before { content: \"\"; position: absolute; top: 0; left: 0; height: 100%; border-left: 2px dashed rgba(50, 50, 50, 0.4); }
.sidebar li:before { content: \"\"; position: absolute; width: 1rem; top: 0.8rem; left: -1.2rem; border-bottom: 2px dashed rgba(50, 50, 50, 0.4); }


.printable { padding: 2rem; }

h1 { text-align: center; }
.sitename { margin-top: 5rem; margin-bottom: 3rem; font-size: 2.5rem; }
main:not(.printable) { padding: 2rem; background: #faf8fb; box-shadow: 0 0.1rem 1rem 0.3rem rgba(50, 50, 50, 0.5); }

label { display: inline-block; min-width: 7rem; }
input[type=text], input[type=password], textarea { margin: 0.5rem 0.8rem; padding: 0.5rem 0.8rem; background: #d5cbf9; border: 0; border-radius: 0.3rem; font-size: 1rem; color: #442772; }
textarea { width: calc(100% - 2rem); min-height: 35rem; font-size: 1.25rem; }
textarea ~ input[type=submit] { width: calc(100% - 0.3rem); margin: 0.5rem 0.8rem; padding: 0.5rem; font-weight: bolder; }

footer { padding: 2rem; }
/* #ffdb6d #36962c */";

// A url that points to the favicon you want to use for your wiki. By default
// this is set to a data: url of a Peppermint.
// Default favicon credit: Peppermint by bluefrog23
//	Link: https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23
$settings->favicon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAB3VBMVEXhERHbKCjeVVXjb2/kR0fhKirdHBziDg6qAADaHh7qLy/pdXXUNzfMAADYPj7ZPDzUNzfbHx/fERHpamrqMTHgExPdHx/bLCzhLS3fVFTjT0/ibm7kRkbiLi7aKirdISHeFBTqNDTpeHjgERHYJCTVODjYQkLaPj6/AADVOTnpbW3cIyPdFRXcJCThMjLiTU3ibW3fVVXaKyvcERH4ODj+8fH/////fHz+Fxf4KSn0UFD/CAj/AAD/Xl7/wMD/EhL//v70xMT/+Pj/iYn/HBz/g4P/IyP/Kyv/7Oz0QUH/9PT/+vr/ior/Dg7/vr7/aGj/QED/bGz/AQH/ERH/Jib/R0f/goL/0dH/qan/YWH/7e3/Cwv4R0f/MTH/enr/vLz/u7v/cHD/oKD/n5//aWn+9/f/k5P/0tL/trb/QUH/cXH/dHT/wsL/DQ3/p6f/DAz/1dX/XV3/kpL/i4v/Vlb/2Nj/9/f/pKT+7Oz/V1f/iIj/jIz/r6//Zmb/lZX/j4//T0//Dw/4MzP/GBj/+fn/o6P/TEz/xMT/b2//Tk7/OTn/HR3/hIT/ODj/Y2P/CQn/ZGT/6Oj0UlL/Gxv//f3/Bwf/YmL/6+v0w8P/Cgr/tbX0QkL+9fX4Pz/qNzd0dFHLAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfeCxINNSdmw510AAAA5ElEQVQYGQXBzSuDAQCA8eexKXOwmSZepa1JiPJxsJOrCwcnuchBjg4O/gr7D9zk4uAgJzvuMgcTpYxaUZvSm5mUj7TX7ycAqvoLIJBwStVbP0Hom1Z/ejoxrbaR1Jz6nWinbKWttGRgMSSjanPktRY6mB9WtRNTn7Ilh7LxnNpKq2/x5LnBitfz+hx0qxUaxhZ6vwqq9bx6f2XXvuUl9SVQS38NR7cvln3v15tZ9bQpuWDtZN3Lgh5DWJex3Y+z1KrVhw21+CiM74WZo83DiXq0dVBDYNJkFEU7WrwDAZhRtQrwDzwKQbT6GboLAAAAAElFTkSuQmCC";

// The prefix that should be used in the names of the session variables.
// Defaults to an all lower case version of the site name with all non
// alphanumeric characters removed. Remember that changing this will log
// everyone out since the session variable's name will have changed.
// Normally you won't have to change this - This setting is left over from when
// we used a cookie to store login details.
// By default this is set to a safe variant on your site name.
$settings->sessionprefix = preg_replace("/[^0-9a-z]/i", "", strtolower($settings->sitename));

/*
Actions:
	view - view a page
		page - page name
		printable=[yes/no] - make output printable
	edit - open editor for page
		page - page name
	save - save edits to page
		page - page name
	list - list pages
		category - the category to list [optional] [unimplemented]
	login - login to the site
	logout - logout
	checklogin - check login credentials and set cookie
	hash - hash a string with sha256
		string - string to hash
	help - get help
	update - update the wiki
		do - set to `true` to actually update the wiki
		secret - set to the value of the site's secret
	credits - view the credits
	delete - delete a page
		page - page name
		delete=yes - actually do the deletion (otherwise we display a prompt)
*/



///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "0.8";
$env = new stdClass();
$env->action = $settings->defaultaction;
$env->page = "";
$env->user = "Anonymous";
$env->is_logged_in = false;
$env->is_admin = false;

session_start();
///////// Login System /////////
// Clear expired sessions
if(isset($_SESSION["$settings->sessionprefix-expiretime"]) and
   $_SESSION["$settings->sessionprefix-expiretime"] < time())
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
	if($settings->users[$env->user] == $env->pass)
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

///////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////// Functions ////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

/*
 * @summary	Converts a filesize into a human-readable string.
 * @source	http://php.net/manual/en/function.filesize.php#106569
 * @editor	Starbeamrainbowlabs
 * 
 * @param	$bytes		 - The number of bytes to convert.
 * @param	$decimals	 - The number of decimal places to preserve.
 */
function human_filesize($bytes, $decimals = 2)
{
	$sz = ["B", "KB", "MB", "GB", "TB", "PB", "EB", "YB", "ZB"];
	$factor = floor((strlen($bytes) - 1) / 3);
	return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}
/*
 * @summary	Calculates the time sincce a particular timestamp and returns a
 * 			human-readable result.
 * @source	http://snippets.pro/snippet/137-php-convert-the-timestamp-to-human-readable-format/
 * 
 * @param $time - The timestamp to convert.
 * 
 * @returns {string} - The time since the given timestamp pas a human-readable string.
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

/*
 * @summary A recursive glob() function.
 * 
 * @param $pattern - The glob pattern to use to find filenames.
 * @param $flags - The glob flags to use when finding filenames.
 * 
 * @returns {array} - An array of the filepaths that match the given glob.
 */
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

///////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////// Security and Consistency Measures ////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

/*
 * Sort out the pageindex. We create it if it doesn't exist, and load and parse
 * it if it does.
 */
if(!file_exists("./pageindex.json"))
{
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

// Work around an Opera + Syntaxtic bug where there is no margin at the left hand side if there isn't a query string when accessing a .php file
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

$env->page = $_GET["page"];
$env->action = strtolower($_GET["action"]);

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
			<p>{footer-message}</p>
			<p>Powered by Pepperminty Wiki, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or open an issue <a href='//github.com/sbrl/Pepperminty-Wiki'>on github</a>.</p>
			<p>Your local friendly administrators are {admins-name-list}.
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
			<p><em>Powered by Pepperminty Wiki.</em></p>
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
			$admin_name = $settings->admindetails["name"];
			$admin_email = hide_email($settings->admindetails["email"]);
			exit(page_renderer::render("$settings->sitename - Module Error", "<p>$settings->sitename has got a misbehaving module installed that tried to register an invalid HTML handler with the page renderer. Please contact $settings->sitename's administrator $admin_name at <a href='mailto:$admin_email'>$admin_email</a>."));
		}
		
		self::$part_processors[] = $function;
		
		return true;
	}
	
	public static function render($title, $content, $body_template = false)
	{
		global $settings, $start_time;
		
		if($body_template === false)
			$body_template = self::$main_content_template;
		
		$parts = [
			"{body}" => $body_template,
			
			"{sitename}" => $settings->sitename,
			"{favicon-url}" => $settings->favicon,
			"{header-html}" => self::get_css_as_html(),
			
			"{navigation-bar}" => self::render_navigation_bar($settings->nav_links, $settings->nav_links_extra, "top"),
			"{navigation-bar-bottom}" => self::render_navigation_bar($settings->nav_links_bottom, [], "bottom"),
			
			"{admin-details-name}" => $settings->admindetails["name"],
			"{admin-details-email}" => $settings->admindetails["email"],
			
			"{admins-name-list}" => implode(", ", $settings->admins),
			
			"{generation-date}" => date("l jS \of F Y \a\\t h:ia T"),
			
			"{all-pages-datalist}" => self::generate_all_pages_datalist(),
			
			"{footer-message}" => $settings->footer_message
		];
		
		// Pass the parts through the part processors
		foreach(self::$part_processors as $function)
		{
			$function($parts);
		}
		
		$result = self::$html_template;
		
		$result = str_replace(array_keys($parts), array_values($parts), $result);
		
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
	
	/*
	 * @summary Function to render a navigation bar from an array of links. See
	 * 			$settings->nav_links for format information.
	 * 
	 * @param $nav_links - The links to add to the navigation bar.
	 * @param $nav_links_extra - The extra nav links to add to the "More..."
	 * 							 menu.
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
					case "user-status":
						if($env->is_logged_in)
						{
							$result .= "<span class='inflexible'>Logged in as " . self::render_username($env->user) . ".</span> "/* . page_renderer::$nav_divider*/;
							$result .= "<span><a href='index.php?action=logout'>Logout</a></span>";
							$result .= page_renderer::$nav_divider;
						}
						else
							$result .= "<span class='inflexible'>Browsing as Anonymous.</span>" . /*page_renderer::$nav_divider . */"<span><a href='index.php?action=login'>Login</a></span>" . page_renderer::$nav_divider;
						break;
					
					case "search": // Displays a search bar
						$result .= "<span class='inflexible'><form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /></form></span>";
						break;
					
					case "divider": // Displays a divider
						$result .= page_renderer::$nav_divider;
						break;
					
					case "menu":
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
				$result .= "<span><a href='" . str_replace("{page}", $env->page, $item[1]) . "'>$item[0]</a></span>";
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

// Function to register a new parser.
$parsers = [
	"none" => function() {
		throw new Exception("No parser registered!");
	}
];
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
		exit(page_renderer::render_main("Parsing error - $settings->sitename", "<p>Parsing some page source data failed. This is most likely because $settings->sitename has the parser setting set incorrectly. Please contact <a href='mailto:" . hide_email($settings->admindetails["email"]) . "'>" . $settings->admindetails["name"] . "</a>, your $settings->sitename Administrator."));
	
/* Not needed atm because escaping happens when saving, not when rendering *
	if($settings->clean_raw_html)
		$source = htmlentities($source, ENT_QUOTES | ENT_HTML5);
*/
	return $parsers[$settings->parser]($source);
}

// Function to register a new proprocessor that will be executed just before
// an edit is saved.
$save_preprocessors = [];
function register_save_preprocessor($func)
{
	global $save_preprocessors;
	$save_preprocessors[] = $func;
}

//////////////////////////////////////////////////////////////////



register_module([
	"name" => "Password hashing action",
	"version" => "0.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a utility action (that anyone can use) called hash that hashes a given string. Useful when changing a user's password.",
	"id" => "action-hash",
	"code" => function() {
		add_action("hash", function() {
			if(!isset($_GET["string"]))
			{
				http_response_code(422);
				exit(page_renderer::render_main("Missing parameter", "<p>The <code>GET</code> parameter <code>string</code> must be specified.</p>
		<p>It is strongly recommended that you utilise this page via a private or incognito window in order to prevent your password from appearing in your browser history.</p>"));
			}
			else
			{
				exit(page_renderer::render_main("Hashed string", "<p><code>" . $_GET["string"] . "</code> → <code>" . hash("sha256", $_GET["string"] . "</code></p>")));
			}
		});
	}
]);




register_module([
	"name" => "Page protection",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Exposes Pepperminty Wiki's new page protection mechanism and makes the protect button in the 'More...' menu on the top bar work.",
	"id" => "action-protect",
	"code" => function() {
		add_action("protect", function() {
			global $env, $pageindex;
			
			// Make sure that the user is logged in as an admin / mod.
			if($env->is_admin)
			{
				// They check out ok, toggle the page's protection.
				$page = $env->page;
				
				$toggled = false;
				if(!isset($pageindex->$page->protect))
				{
					$pageindex->$page->protect = true;
					$toggled = true;
				}
				
				if(!$toggled && $pageindex->$page->protect === true)
				{
					$pageindex->$page->protected = false;
					$toggled = false;
				}
				
				if(!$toggled && $pageindex->$page->protect === false)
				{
					$pageindex->$page->protected = true;
					$toggled = true;
				}
				
				// Save the pageindex
				file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
				
				$state = ($pageindex->$page->protect ? "enabled" : "disabled");
				$title = "Page protection $state.";
				exit(page_renderer::render_main($title, "<p>Page protection for $env->page has been $state.</p><p><a href='?action=$env->defaultaction&page=$env->page'>Go back</a>."));
			}
			else
			{
				exit(page_renderer::render_main("Error protecting page", "<p>You are not allowed to protect pages because you are not logged in as a mod or admin. Please try logging out if you are logged in and then try logging in as an administrator.</p>"));
			}
		});
	}
]);




register_module([
	"name" => "Raw page source",
	"version" => "0.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		add_action("raw", function() {
			global $env;
			
			http_response_code(307);
			header("x-filename: " . rawurlencode($env->page) . ".md");
			header("content-type: text/markdown");
			exit(file_get_contents("$env->page.md"));
			exit();
		});
	}
]);




register_module([
	"name" => "Sidebar",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a sidebar to the left hand side of every page. Add '\$settings->sidebar_show = true;' to your configuration, or append '&sidebar=yes' to the url to enable. Adding to the url sets a cookie to remember your setting.",
	"id" => "extra-sidebar",
	"code" => function() {
		$show_sidebar = false;
		
		// Show the sidebar if it is enabled in the settings
		if(isset($settings->sidebar_show) && $settings->sidebar_show === true)
			$show_sidebar = true;
		
		// Also show and persist the sidebar if the special GET parameter
		// sidebar is seet
		if(!$show_sidebar && isset($_GET["sidebar"]))
		{
			$show_sidebar = true;
			// Set a cookie to persist the display of the sidebar
			setcookie("sidebar_show", "true", time() + (60 * 60 * 24 * 30));
		}
		
		// Show the sidebar if the cookie is set
		if(!$show_sidebar && isset($_COOKIE["sidebar_show"]))
			$show_sidebar = true;
		
		// Delete the cookie and hide the sidebar if the special GET paramter
		// nosidebar is set
		if(isset($_GET["nosidebar"]))
		{
			$show_sidebar = false;
			unset($_COOKIE["sidebar_show"]);
			setcookie("sidebar_show", null, time() - 3600);
		}
		
		page_renderer::register_part_preprocessor(function(&$parts) use ($show_sidebar) {
			global $settings, $pageindex;
			
			if($show_sidebar && !isset($_GET["printable"]))
			{
				// Show the sidebar
				$exec_start = microtime(true);
				
				// Sort the pageindex
				$sorted_pageindex = get_object_vars($pageindex);
				ksort($sorted_pageindex, SORT_NATURAL);
				
				$sidebar_contents = "";
				$sidebar_contents .= render_sidebar($sorted_pageindex);
				
				$parts["{body}"] = "<aside class='sidebar'>
			$sidebar_contents
			<!-- Sidebar rendered in " . (microtime(true) - $exec_start) . "s -->
		</aside>
		<div class='main-container'>" . $parts["{body}"] . "</div>
		<!-------------->
		<style>
			body { display: flex; }
			.main-container { flex: 1; }
		</style>";
			}
		});
	}
]);

/* 
 * @summary Renders the sidebar for a given pageindex.
 * 
 * @param $pageindex {array} - The pageindex to render the sidebar for
 * @param $root_pagename {string} - The pagename that should be considered the root of the rendering. You don't usually need to use this, it is used by the algorithm itself since it is recursive.
 * 
 * @returns {string} A HTML rendering of the sidebar for the given pageindex
 */
function render_sidebar($pageindex, $root_pagename = "")
{
	global $settings;
	
	$result = "<ul";
	// If this is the very root of the tree, add an extra class to it
	if($root_pagename == "") $result .= " class='sidebar-tree'";
	$result .=">";
	foreach ($pageindex as $pagename => $details)
	{
		// If we have a valid root pagename, and it isn't present at the
		// beginning of the current pagename, skip it
		if($root_pagename !== "" && strpos($pagename, $root_pagename) !== 0)
			continue;
		
		// The current page is the same as the root page, skip it
		if($pagename == $root_pagename)
			continue;
		
		
		// If the part of the current pagename that comes after the root
		// pagename has a slash in it, skip it as it is a sub-sub page.
		if(strpos(substr($pagename, strlen($root_pagename)), "/") !== false)
			continue;
		
		$result .= "<li><a href='?action=$settings->defaultaction&page=$pagename'>$pagename</a>\n";
		$result .= render_sidebar($pageindex, $pagename);
		$result .= "</li>\n";
	}
	$result .= "</ul>\n";
	
	return $result == "<ul></ul>\n" ? "" : $result;
}




register_module([
	"name" => "Redirect pages",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds support for redirect pages. Uses the same syntax that Mediawiki does.",
	"id" => "feature-redirect",
	"code" => function() {
		register_save_preprocessor(function(&$index_entry, &$pagedata) {
			$matches = [];
			if(preg_match("/^# ?REDIRECT ?\[\[([^\]]+)\]\]/i", $pagedata, $matches) === 1)
			{
				error_log("matches: " . var_export($matches, true));
				// We have found a redirect page!
				// Update the metadata to reflect this.
				$index_entry->redirect = true;
				$index_entry->redirect_target = $matches[1];
			}
			else
			{
				// This page isn't a redirect. Unset the metadata just in case.
				if(isset($index_entry->redirect))
					unset($index_entry->redirect);
				if(isset($index_entry->redirect_target))
					unset($index_entry->redirect_target);
			}
		});
		
		// Todo register a function somewhere else to detect reedirects in the front end
	}
]);




register_module([
	"name" => "Credits",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the credits page. You *must* have this module :D",
	"id" => "page-credits",
	"code" => function() {
		add_action("credits", function() {
			global $settings, $version, $pageindex, $modules;
			
			$credits = [
				"Code" => [
					"author" => "Starbeamrainbowlabs",
					"author_url" => "https://starbeamrmainbowlabs.com/",
					"thing_url" => "https://github.com/sbrl/Pepprminty-Wiki"
				],
				"Slightly modified version of Slimdown" => [
					"author" => "Johnny Broadway",
					"author_url" => "https://github.com/jbroadway",
					"thing_url" => "https://gist.github.com/jbroadway/2836900"
				],
				"Default Favicon" => [
					"author" => "bluefrog23",
					"author_url" => "https://openclipart.org/user-detail/bluefrog23/",
					"thing_url" => "https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23"
				],
				"Bug Reports" => [
					"author" => "nibreh",
					"author_url" => "https://github.com/nibreh/",
					"thing_url" => ""
				]
			];
			$credits_html = "<ul>\n";
			foreach($credits as $thing => $author_details)
			{
				$credits_html .= "	<li>";
				$credits_html .= "<a href='" . $author_details["thing_url"] . "'>$thing</a> by ";
				$credits_html .= "<a href='" . $author_details["author_url"] . "'>" . $author_details["author"] . "</a>";
				$credits_html .= "</li>\n";
			}
			$credits_html .= "</ul>";
			
			$title = "Credits - $settings->sitename";
			$content = "<h1>$settings->sitename credits</h1>
	<p>$settings->sitename is powered by Pepperminty Wiki - an entire wiki packed inside a single file, which was built by <a href='//starbeamrainbowlabs.com'>Starbeamrainbowlabs</a>, and can be found <a href='//github.com/sbrl/Pepperminty-Wiki/'>on GitHub</a> (contributors will ablso be listed here in the future).</p>
	<h2>Main Credits</h2>
	$credits_html
	<h2>Site status</h2>
	<table>
		<tr><th>Site name:</th><td>$settings->sitename (<a href='?action=update'>Update - Administrators only</a>, <a href='?action=export'>Export as zip - Check for permission first</a>)</td></tr>
		<tr><th>Pepperminty Wiki version:</th><td>$version</td></tr>
		<tr><th>Number of pages:</th><td>" . count(get_object_vars($pageindex)) . "</td></tr>
		<tr><th>Number of modules:</th><td>" . count($modules) . "</td></tr>
	</table>";
			exit(page_renderer::render_main($title, $content));
		});
	}
]);




register_module([
	"name" => "Page deleter",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		add_action("delete", function() {
			global $pageindex, $settings, $env;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Deleting $env->page - error", "<p>You tried to delete $env->page, but editing is disabled on this wiki.</p>
				<p>If you wish to delete this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$env->page'>Go back to $env->page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$env->is_admin)
			{
				exit(page_renderer::render_main("Deleting $env->page - error", "<p>You tried to delete $env->page, but you are not an admin so you don't have permission to do that.</p>
				<p>You should try <a href='index.php?action=login'>logging in</a> as an admin.</p>"));
			}
			if(!isset($_GET["delete"]) or $_GET["delete"] !== "yes")
			{
				exit(page_renderer::render_main("Deleting $env->page", "<p>You are about to <strong>delete</strong> $env->page. You can't undo this!</p>
				<p><a href='index.php?action=delete&page=$env->page&delete=yes'>Click here to delete $env->page.</a></p>
				<p><a href='index.php?action=view&page=$env->page'>Click here to go back.</a>"));
			}
			$page = $env->page;
			unset($pageindex->$page); //delete the page from the page index
			file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT)); //save the new page index
			unlink("./$env->page.md"); //delete the page from the disk

			exit(page_renderer::render_main("Deleting $env->page - $settings->sitename", "<p>$env->page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
	}
]);




register_module([
	"name" => "Page editor",
	"version" => "0.11",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	
	"code" => function() {
		
		/*
		 *           _ _ _
		 *   ___  __| (_) |_
		 *  / _ \/ _` | | __|
		 * |  __/ (_| | | |_
		 *  \___|\__,_|_|\__|
		 *             %edit%
		 */
		add_action("edit", function() {
			global $pageindex, $settings, $env;
			
			$filename = "$env->page.md";
			$page = $env->page;
			$creatingpage = !isset($pageindex->$page);
			if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
			{
				$title = "Creating $env->page";
			}
			else
			{
				$title = "Editing $env->page";
			}
			
			$pagetext = "";
			if(isset($pageindex->$page))
			{
				$pagetext = file_get_contents($filename);
			}
			
			if((!$env->is_logged_in and !$settings->anonedits) or // if we aren't logged in  and anonymous edits are disbled
			   !$settings->editing or// or editing is disabled
			   (
				   isset($pageindex->$page) and // the page exists
				   isset($pageindex->$page->protect) and // the protect property exists
				   $pageindex->$page->protect and // the protect property is true
				   !$env->is_admin // the user isn't an admin
			   )
			)
			{
				if(!$creatingpage)
				{
					// The page already exists - let the user view the page source
					exit(page_renderer::render_main("Viewing source for $env->page", "<p>$settings->sitename does not allow anonymous users to make edits. If you are in fact logged in, then this page is probably protected, and you aren't an administrator or moderator. You can view the source of $env->page below, but you can't edit it.</p><textarea name='content' readonly>$pagetext</textarea>"));
				}
				else
				{
					http_response_code(404);
					exit(page_renderer::render_main("404 - $env->page", "<p>The page <code>$env->page</code> does not exist, but you do not have permission to create it.</p><p>If you haven't already, perhaps you should try <a href='index.php?action=login'>logging in</a>.</p>"));
				}
			}
			
			$content = "<h1>$title</h1>";
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save'>
			<textarea name='content'>$pagetext</textarea>
			<p>$settings->editing_message</p>
			<input type='submit' value='Save Page' />
		</form>";
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/*
		 *
		 *  ___  __ ___   _____
		 * / __|/ _` \ \ / / _ \
		 * \__ \ (_| |\ V /  __/
		 * |___/\__,_| \_/ \___|
		 *                %save%
		 */
		add_action("save", function() {
			global $pageindex, $settings, $env, $save_preprocessors; 
			if(!$settings->editing)
			{
				header("location: index.php?page=$env->page");
				exit(page_renderer::render_main("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
			}
			if(!$env->is_logged_in and !$settings->anonedits)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("You are not logged in, so you are not allowed to save pages on $settings->sitename. Redirecting in 5 seconds....");
			}
			$page = $env->page;
			if((
				isset($pageindex->$page) and
				isset($pageindex->page->protect) and
				$pageindex->$page->protect
			) and !$env->is_admin)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("$env->page is protected, and you aren't logged in as an administrastor or moderator. Your edit was not saved. Redirecting in 5 seconds...");
			}
			if(!isset($_POST["content"]))
			{
				http_response_code(400);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("Bad request: No content specified.");
			}
			
			// Make sure that the directory in which the page needs to be saved exists
			if(!is_dir(dirname("$env->page.md")))
			{
				// Recursively create the directory if needed
				mkdir(dirname("$env->page.md"), null, true);
			}
			
			
			$pagedata = $_POST["content"];
			
			if($settings->clean_raw_html)
				$pagedata = htmlentities($pagedata, ENT_QUOTES);
			
			if(file_put_contents("$env->page.md", $pagedata) !== false)
			{
				$page = $env->page;
				// Make sure that this page's parents exist
				check_subpage_parents($page);
				
				// Update the page index
				if(!isset($pageindex->$page))
				{
					$pageindex->$page = new stdClass();
					$pageindex->$page->filename = "$env->page.md";
				}
				$pageindex->$page->size = strlen($_POST["content"]);
				$pageindex->$page->lastmodified = time();
				if($env->is_logged_in)
					$pageindex->$page->lasteditor = utf8_encode($env->user);
				else
					$pageindex->$page->lasteditor = utf8_encode("anonymous");
				
				// A hack to resave the pagedata if the preprocessors have
				// changed it. We need this because the preprocessors *must*
				// run _after_ the pageindex has been updated.
				$pagedata_orig = $pagedata;
				
				// Execute all the preprocessors
				foreach($save_preprocessors as $func)
				{
					$func($pageindex->$page, $pagedata);
				}
				
				if($pagedata !== $pagedata_orig)
					file_put_contents("$env->page.md", $pagedata);
				
				
				file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
				
				if(isset($_GET["newpage"]))
					http_response_code(201);
				else
					http_response_code(200);
				
				header("location: index.php?page=$env->page&edit_status=success&redirect=no");
				exit();
			}
			else
			{
				http_response_code(507);
				exit(page_renderer::render_main("Error saving page - $settings->sitename", "<p>$settings->sitename failed to write your changes to the disk. Your changes have not been saved, but you might be able to recover your edit by pressing the back button in your browser.</p>
				<p>Please tell the administrator of this wiki (" . $settings->admindetails["name"] . ") about this problem.</p>"));
			}
		});
	}
]);




register_module([
	"name" => "Export",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that you can use to export your wiki as a .zip file. Uses \$settings->export_only_allow_admins, which controls whether only admins are allowed to export the wiki.",
	"id" => "page-export",
	"code" => function() {
		add_action("export", function() {
			global $settings, $pageindex, $env;
			
			if($settings->export_allow_only_admins && !$env->is_admin)
			{
				http_response_code(401);
				exit(page_renderer::render("Export error - $settings->sitename", "Only administrators of $settings->sitename are allowed to export the wiki as a zip. <a href='?action=$settings->defaultaction&page='>Return to the $settings->defaultpage</a>."));
			}
			
			$tmpfilename = tempnam(sys_get_temp_dir(), "pepperminty-wiki-");
			
			$zip = new ZipArchive();
			
			if($zip->open($tmpfilename, ZipArchive::CREATE) !== true)
			{
				http_response_code(507);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty Wiki was unable to open a temporary file to store the exported data in. Please contact $settings->sitename's administrator (" . $settings->admindetails["name"] . " at " . hide_email($settings->admindetails["email"]) . ") for assistance."));
			}
			
			foreach($pageindex as $entry)
			{
				$zip->addFile("./$entry->filename", $entry->filename);
			}
			
			if($zip->close() !== true)
			{
				http_response_code(500);
				exit(page_renderer::render("Export error - $settings->sitename", "Pepperminty wiki was unable to close the temporary zip file after creating it. Please contact $settings->sitename's administrator (" . $settings->admindetails["name"] . " at " . hide_email($settings->admindetails["email"]) . ") for assistance."));
			}
			
			header("content-type: application/zip");
			header("content-disposition: attachment; filename=$settings->sitename-export.zip");
			header("content-length: " . filesize($tmpfilename));
			
			$zip_handle = fopen($tmpfilename, "rb");
			fpassthru($zip_handle);
			fclose($zip_handle);
			unlink($tmpfilename);
		});
	}
]);




register_module([
	"name" => "Help page",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the help action. You really want this one.",
	"id" => "page-help",
	"code" => function() {
		add_action("help", function() {
			global $settings, $version;
			
			$title = "Help - $settings->sitename";
			$content = "	<h1>$settings->sitename Help</h1>
	<p>Welcome to $settings->sitename!</p>
	<p>$settings->sitename is powered by Pepperminty wiki, a complete wiki in a box you can drop into your server.</p>
	<h2>Navigating</h2>
	<p>All the navigation links can be found in the top right corner, along with a box in which you can type a page name and hit enter to be taken to that page (if your site administrator has enabled it).</p>
	<p>In order to edit pages on $settings->sitename, you probably need to be logged in. If you do not already have an account you will need to ask $settings->sitename's administrator for an account since there is not registration form. Note that the $settings->sitename's administrator may have changed these settings to allow anonymous edits.</p>
	<h2>Editing</h2>
	<p>$settings->sitename's editor uses a modified version of slimdown, a flavour of markdown that is implementated using regular expressions. See the credits page for more information and links to the original source for this. A quick reference can be found below:</p>
	<table>
		<tr><th>Type This</th><th>To get this</th>
		<tr><td><code>_italics_</code></td><td><em>italics</em></td></tr>
		<tr><td><code>*bold*</code></td><td><strong>bold</strong></td></tr>
		<tr><td><code>~~Strikethrough~~</code></td><td><del>Strikethough</del></td></tr>
		<tr><td><code>`code`</code></td><td><code>code</code></td></tr>
		<tr><td><code># Heading</code></td><td><h2>Heading</h2></td></tr>
		<tr><td><code>## Sub Heading</code></td><td><h3>Sub Heading</h3></td></tr>
		<tr><td><code>[[Internal Link]]</code></td><td><a href='index.php?page=Internal Link'>Internal Link</a></td></tr>
		<tr><td><code>[[Display Text|Internal Link]]</code></td><td><a href='index.php?page=Internal Link'>Display Text</a></td></tr>
		<tr><td><code>[Display text](//google.com/)</code></td><td><a href='//google.com/'>Display Text</a></td></tr>
		<tr><td><code>&gt; Blockquote<br />&gt; Some text</code></td><td><blockquote> Blockquote<br />Some text</td></tr>
		<tr><td><code> - Apples<br /> * Oranges</code></td><td><ul><li>Apples</li><li>Oranges</li></ul></td></tr>
		<tr><td><code>1. This is<br />2. an ordered list</code></td><td><ol><li>This is</li><li>an ordered list</li></ol></td></tr>
		<tr><td><code>
	---
	</code></td><td><hr /></td></tr>
		<!--<tr><tds><code> - One
 - Two
 - Three</code></td><td><ul><li>One</li><li>Two</li><li>Three</li></ul></td></tr>-->
		<tr><td><code>![Alt text](//starbeamrainbowlabs.com/favicon-small.png)</code></td><td><img src='//starbeamrainbowlabs.com/favicon-small.png' alt='Alt text' /></td></code>
	</table>
	
	<p>In addition, the following extra syntax is supported for images:</p>
	
	<pre><code>Size the image to at most 250 pixels wide:
![Alt text](//starbeamrainbowlabs.com/favicon-small.png 250px)

Size the image to at most 120px wide and have it float at the right ahnd size of the page:
![Alt text](//starbeamrainbowlabs.com/favicon-small.png 120px right)</code></pre>
	
	<h2>Administrator Actions</h2>
	<p>By default, the <code>delete</code> and <code>move</code> actions are shown on the nav bar. These can be used by administrators to delete or move pages.</p>
	<p>The other thing admininistrators can do is update the wiki (provided they know the site's secret). This page can be found here: <a href='?action=update'>Update $settings->sitename</a>.</p>
	<p>$settings->sitename is currently running on Pepperminty Wiki <code>$version</code></p>";
			exit(page_renderer::render_main($title, $content));
		});
	}
]);




register_module([
	"name" => "Page list",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that lists all the pages in the index along with their metadata.",
	"id" => "page-list",
	"code" => function() {
		add_action("list", function() {
			global $pageindex, $settings;
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			$title = "All Pages";
			$content = "	<h1>$title on $settings->sitename</h1>
	<table>
		<tr>
			<th>Page Name</th>
			<th>Size</th>
			<th>Last Editor</th>
			<th>Last Edit Time</th>
		</tr>\n";
			// todo list the pages in alphabetical order
		foreach($sorted_pageindex as $pagename => $pagedetails)
		{
			$content .= "\t\t<tr>
			<td><a href='index.php?page=$pagename'>$pagename</a></td>
			<td>" . human_filesize($pagedetails->size) . "</td>
			<td>$pagedetails->lasteditor</td>
			<td>" . human_time_since($pagedetails->lastmodified) . " <small>(" . date("l jS \of F Y \a\\t h:ia T", $pagedetails->lastmodified) . ")</small></td>

		</tr>\n";
			}
			$content .= "	</table>";
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
	}
]);




register_module([
	"name" => "Login",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a pair of actions (login and checklogin) that allow users to login. You need this one if you want your users to be able to login.",
	"id" => "page-login",
	"code" => function() {
		/*
		 *  _             _
		 * | | ___   __ _(_)_ __
		 * | |/ _ \ / _` | | '_ \
		 * | | (_) | (_| | | | | |
		 * |_|\___/ \__, |_|_| |_|
		 *          |___/  %login%
		 */
		add_action("login", function() {
			global $settings;
			$title = "Login to $settings->sitename";
			$content = "<h1>Login to $settings->sitename</h1>\n";
			if(isset($_GET["failed"]))
				$content .= "\t\t<p><em>Login failed.</em></p>\n";
			$content .= "\t\t<form method='post' action='index.php?action=checklogin&returnto=" . rawurlencode($_SERVER['REQUEST_URI']) . "'>
				<label for='user'>Username:</label>
				<input type='text' name='user' id='user' />
				<br />
				<label for='pass'>Password:</label>
				<input type='password' name='pass' id='pass' />
				<br />
				<input type='submit' value='Login' />
			</form>";
			exit(page_renderer::render_main($title, $content));
		});
		
		/*
		 *       _               _    _             _
		 *   ___| |__   ___  ___| | _| | ___   __ _(_)_ __
		 *  / __| '_ \ / _ \/ __| |/ / |/ _ \ / _` | | '_ \
		 * | (__| | | |  __/ (__|   <| | (_) | (_| | | | | |
		 *  \___|_| |_|\___|\___|_|\_\_|\___/ \__, |_|_| |_|
		 *     %checklogin%                   |___/
		 */
		add_action("checklogin", function() {
			global $settings, $env;
			
			//actually do the login
			if(isset($_POST["user"]) and isset($_POST["pass"]))
			{
				//the user wants to log in
				$user = $_POST["user"];
				$pass = $_POST["pass"];
				if($settings->users[$user] == hash("sha256", $pass))
				{
					$env->is_logged_in = true;
					$expiretime = time() + 60*60*24*30; //30 days from now
					$_SESSION["$settings->sessionprefix-user"] = $user;
					$_SESSION["$settings->sessionprefix-pass"] = hash("sha256", $pass);
					$_SESSION["$settings->sessionprefix-expiretime"] = $expiretime;
					//redirect to wherever the user was going
					http_response_code(302);
					if(isset($_POST["goto"]))
						header("location: " . $_POST["returnto"]);
					else
						header("location: index.php");
					exit();
				}
				else
				{
					http_response_code(302);
					header("location: index.php?action=login&failed=yes");
					exit();
				}
			}
			else
			{
				http_response_code(302);
				header("location: index.php?action=login&failed=yes&badrequest=yes");
				exit();
			}
		});
	}
]);



register_module([
	"name" => "Logout",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to let users user out. For security reasons it is wise to add this module since logging in automatically opens a session that is valid for 30 days.",
	"id" => "page-logout",
	"code" => function() {
		add_action("logout", function() {
			global $env;
			$env->is_logged_in = false;
			unset($env->user);
			unset($env->pass);
			//clear the session variables
			$_SESSION = [];
			session_destroy();
			
			exit(page_renderer::render_main("Logout Successful", "<h1>Logout Successful</h1>
		<p>Logout Successful. You can login again <a href='index.php?action=login'>here</a>.</p>"));
		});
	}
]);




register_module([
	"name" => "Page mover",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to move pages.",
	"id" => "page-move",
	"code" => function() {
		add_action("move", function() {
			global $pageindex, $settings, $env;
			if(!$settings->editing)
			{
				exit(page_renderer::render_main("Moving $env->page - error", "<p>You tried to move $env->page, but editing is disabled on this wiki.</p>
				<p>If you wish to move this page, please re-enable editing on this wiki first.</p>
				<p><a href='index.php?page=$env->page'>Go back to $env->page</a>.</p>
				<p>Nothing has been changed.</p>"));
			}
			if(!$env->is_admin)
			{
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $env->page, but you do not have permission to do that.</p>
				<p>You should try <a href='index.php?action=login'>logging in</a> as an admin.</p>"));
			}
			
			if(!isset($_GET["new_name"]) or strlen($_GET["new_name"]) == 0)
				exit(page_renderer::render_main("Moving $env->page", "<h2>Moving $env->page</h2>
				<form method='get' action='index.php'>
					<input type='hidden' name='action' value='move' />
					<label for='old_name'>Old Name:</label>
					<input type='text' name='page' value='$env->page' readonly />
					<br />
					<label for='new_name'>New Name:</label>
					<input type='text' name='new_name' />
					<br />
					<input type='submit' value='Move Page' />
				</form>"));
			
			$new_name = makepathsafe($_GET["new_name"]);
			
			$page = $env->page;
			if(!isset($pageindex->$page))
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $env->page to $new_name, but the page with the name $env->page does not exist in the first place.</p>
				<p>Nothing has been changed.</p>"));
			
			if($env->page == $new_name)
				exit(page_renderer::render_main("Moving $env->page - Error", "<p>You tried to move $page, but the new name you gave is the same as it's current name.</p>
				<p>It is possible that you tried to use some characters in the new name that are not allowed and were removed.</p>
				<p>Page names may only contain alphanumeric characters, dashes, and underscores.</p>"));
			
			//move the page in the page index
			$pageindex->$new_name = new stdClass();
			foreach($pageindex->$page as $key => $value)
			{
				$pageindex->$new_name->$key = $value;
			}
			unset($pageindex->$page);
			file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
			
			//move the page on the disk
			rename("$env->page.md", "$new_name.md");
			
			exit(page_renderer::render_main("Moving $env->page", "<p><a href='index.php?page=$env->page'>$env->page</a> has been moved to <a href='index.php?page=$new_name'>$new_name</a> successfully.</p>"));
		});
	}
]);




register_module([
	"name" => "Update",
	"version" => "0.6.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an update page that downloads the latest stable version of Pepperminty Wiki. This module is currently outdated as it doesn't save your module preferences.",
	"id" => "page-update",
	"code" => function() {
		add_action("update", function() {
			global $settings, $env;
			
			if(!$env->is_admin)
			{
				http_response_code(401);
				exit(page_renderer::render_main("Update - Error", "<p>You must be an administrator to do that.</p>"));
			}
			
			if(!isset($_GET["do"]) or $_GET["do"] !== "true")
			{
				exit(page_renderer::render_main("Update $settings->sitename", "<p>This page allows you to update $settings->sitename.</p>
				<p>Currently, $settings->sitename is using $settings->version of Pepperminty Wiki.</p>
				<p>This script will automatically download and install the latest version of Pepperminty Wiki from the url of your choice (see settings), regardless of whether an update is actually needed (version checking isn't implemented yet).</p>
				<p>To update $settings->sitename, fill out the form below and click click the update button.</p>
				<p>Note that a backup system has not been implemented yet! If this script fails you will loose your wiki's code and have to re-build it.</p>
				<form method='get' action=''>
					<input type='hidden' name='action' value='update' />
					<input type='hidden' name='do' value='true' />
					<label for='secret'>$settings->sitename's secret code</label>
					<input type='text' name='secret' value='' />
					<input type='submit' value='Update' />
				</form>"));
			}
			
			if(!isset($_GET["secret"]) or $_GET["secret"] !== $settings->sitesecret)
			{
				exit(page_renderer::render_main("Update $settings->sitename - Error", "<p>You forgot to enter $settings->sitename's secret code or entered it incorrectly. $settings->sitename's secret can be found in the settings portion of <code>index.php</code>.</p>"));
			}
			
			$settings_separator = "/////////////// Do not edit below this line unless you know what you are doing! ///////////////";
			
			$log = "Beginning update...\n";
			
			$log .= "I am <code>" . __FILE__ . "</code>.\n";
			$oldcode = file_get_contents(__FILE__);
			$log .= "Fetching new code...";
			$newcode = file_get_contents($settings->updateurl);
			$log .= "done.\n";
			
			$log .= "Rewriting <code>" . __FILE__ . "</code>...";
			$settings = substr($oldcode, 0, strpos($oldcode, $settings_separator));
			$code = substr($newcode, strpos($newcode, $settings_separator));
			$result = $settings . $code;
			$log .= "done.\n";
			
			$log .= "Saving...";
			file_put_contents(__FILE__, $result);
			$log .= "done.\n";
			
			$log .= "Update complete. I am now running on the latest version of Pepperminty Wiki.";
			$log .= "The version number that I have updated to can be found on the credits or help ages.";
			
			exit(page_renderer::render_main("Update - Success", "<ul><li>" . implode("</li><li>", explode("\n", $log)) . "</li></ul>"));
		});
	}
]);



register_module([
	"name" => "Page viewer",
	"version" => "0.11",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to view pages. You reallyshould include this one.",
	"id" => "page-view",
	"code" => function() {
		add_action("view", function() {
			global $pageindex, $settings, $env;
			
			// Check to make sure that the page exists
			$page = $env->page;
			if(!isset($pageindex->$page))
			{
				// todo make this intelligent so we only redirect if the user is acutally able to create the page
				if($settings->editing)
				{
					// Editing is enabled, redirect to the editing page
					http_response_code(307); // Temporary redirect
					header("location: index.php?action=edit&newpage=yes&page=" . rawurlencode($env->page));
					exit();
				}
				else
				{
					// Editing is disabled, show an error message
					http_response_code(404);
					exit(page_renderer::render_main("$env->page - 404 - $settings->sitename", "<p>$env->page does not exist.</p><p>Since editing is currently disabled on this wiki, you may not create this page. If you feel that this page should exist, try contacting this wiki's Administrator.</p>"));
				}
			}
			
			// Perform a redirect if the requested page is a redirect page
			if(isset($pageindex->$page->redirect) &&
			   $pageindex->$page->redirect === true)
			{
				$send_redirect = true;
				if(isset($_GET["redirect"]) && $_GET["redirect"] == "no")
					$send_redirect = false;
				
				if($send_redirect)
				{
					// Todo send an explanatory page along with the redirect
					http_response_code(307);
					header("location: ?action=$env->action&page=" . $pageindex->$page->redirect_target . "&redirected_from=$env->page");
					exit();
				}
			}
			
			$title = "$env->page - $settings->sitename";
			if(isset($pageindex->$page->protect) && $pageindex->$page->protect === true)
				$title = $settings->protectedpagechar . $title;
			$content = "<h1>$env->page</h1>\n";
			
			// Add an extra message if the requested was redirected from another page
			if(isset($_GET["redirected_from"]))
				$content .= "<p><em>Redirected from <a href='?page=" . rawurlencode($_GET["redirected_from"]) . "&redirect=no'>" . $_GET["redirected_from"] . "</a>.</em></p>";
			
			$parsing_start = microtime(true);
			
			$content .= parse_page_source(file_get_contents("$env->page.md"));
			
			if($settings->show_subpages)
			{
				$subpages = get_object_vars(get_subpages($pageindex, $env->page));
				
				if(count($subpages) > 0)
				{
					$content .= "<hr />";
					$content .= "Subpages: ";
					foreach($subpages as $subpage => $times_removed)
					{
						if($times_removed <= $settings->subpages_display_depth)
						{
							$content .= "<a href='?action=view&page=" . rawurlencode($subpage) . "'>$subpage</a>, ";
						}
					}
					// Remove the last comma from the content
					$content = substr($content, 0, -2);
				}
			}
			
			$content .= "\n\t\t<!-- Took " . (microtime(true) - $parsing_start) . " seconds to parse page source -->\n";
			
			if(isset($_GET["printable"]) and $_GET["printable"] === "yes")
				exit(page_renderer::render_minimal($title, $content));
			else
				exit(page_renderer::render_main($title, $content));
		});
	}
]);




register_module([
	"name" => "Default Parser",
	"version" => "0.8",
	"author" => "Johnny Broadway & Starbeamrainbowlabs",
	"description" => "The default parser for Pepperminty Wiki. Based on Johnny Broadway's Slimdown (with more than a few modifications). This parser's features are documented in the help page.",
	"id" => "parser-default",
	"code" => function() {
		add_parser("default", function($markdown) {
			return Slimdown::render($markdown);
		});
	}
]);

////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////// Slimdown /////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////// %slimdown% //
////////////////////////////////////////////////////////////////////////////////////////////
/**
 * Slimdown - A very basic regex-based Markdown parser. Supports the
 * following elements (and can be extended via Slimdown::add_rule()):
 *
 * - Headers
 * - Links
 * - Bold
 * - Emphasis
 * - Deletions
 * - Quotes
 * - Inline code
 * - Blockquotes
 * - Ordered/unordered lists
 * - Horizontal rules
 *
 * Author: Johnny Broadway <johnny@johnnybroadway.com>
 * Website: https://gist.github.com/jbroadway/2836900
 * License: MIT
 */

/**
 * Modified by Starbeamrainbowlabs (starbeamrainbowlabs)
 * 
 	* Changed bold to use single asterisks
 	* Changed italics to use single underscores
 	* Added one to add the heading levels (no <h1> tags allowed)
 	* Added wiki style internal link parsing
 	* Added wiki style internal link parsing with display text
 	* Added image support
 */
class Slimdown {
	public static $rules = array (
		'/\r\n/' => "\n",											// new line normalisation
		'/^(#+)(.*)/' => 'self::header',								// headers
		'/(\*)(.*?)\1/' => '<strong>\2</strong>',					// bold
		'/(_)(.*?)\1/' => '<em>\2</em>',							// emphasis
		
		'/!\[(.*)\]\(([^\s]+)\s(\d+.+)\s(left|right)\)/' => '<img src="\2" alt="\1" style="max-width: \3; float: \4;" />',		// images with size
		'/!\[(.*)\]\(([^\s]+)\s(\d+.+)\)/' => '<img src="\2" alt="\1" style="max-width: \3;" />',		// images with size
		'/!\[(.*)\]\((.*)\)/' => '<img src="\2" alt="\1" />',		// basic images
		
		'/\[\[([a-zA-Z0-9\_\- ]+)\|([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\2</a>',	//internal links with display text
		'/\[\[([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\1</a>',	//internal links
		'/\[([^\[]+)\]\(([^\)]+)\)/' => '<a href=\'\2\' target=\'_blank\'>\1</a>',	// links
		'/\~\~(.*?)\~\~/' => '<del>\1</del>',						// del
		'/\:\"(.*?)\"\:/' => '<q>\1</q>',							// quote
		'/`(.*?)`/' => '<code>\1</code>',							// inline code
		'/\n\s*(\*|-)(.*)/' => 'self::ul_list',						// ul lists
		'/\n[0-9]+\.(.*)/' => 'self::ol_list',						// ol lists
		'/\n(&gt;|\>)(.*)/' => 'self::blockquote',					// blockquotes
		'/\n-{3,}/' => "\n<hr />",									// horizontal rule
		'/\n([^\n]+)\n\n/' => 'self::para',							// add paragraphs
		'/<\/ul>\s?<ul>/' => '',									// fix extra ul
		'/<\/ol>\s?<ol>/' => '',									// fix extra ol
		'/<\/blockquote><blockquote>/' => "\n"						// fix extra blockquote
	);
	private static function para ($regs) {
		$line = $regs[1];
		$trimmed = trim ($line);
		if (preg_match ('/^<\/?(ul|ol|li|h|p|bl)/', $trimmed)) {
			return "\n" . $line . "\n";
		}
		return sprintf ("\n<p>%s</p>\n", $trimmed);
	}
	private static function ul_list ($regs) {
		$item = $regs[2];
		return sprintf ("\n<ul>\n\t<li>%s</li>\n</ul>", trim($item));
	}
	private static function ol_list ($regs) {
		$item = $regs[1];
		return sprintf ("\n<ol>\n\t<li>%s</li>\n</ol>", trim($item));
	}
	private static function blockquote ($regs) {
		$item = $regs[2];
		return sprintf ("\n<blockquote>%s</blockquote>", trim($item));
	}
	private static function header ($regs) {
		list ($tmp, $chars, $header) = $regs;
		$level = strlen ($chars);
		return sprintf ('<h%d>%s</h%d>', $level + 1, trim($header), $level + 1);
	}
	
	/**
	 * Add a rule.
	 */
	public static function add_rule ($regex, $replacement) {
		self::$rules[$regex] = $replacement;
	}
	/**
	 * Render some Markdown into HTML.
	 */
	public static function render ($text) {
		foreach (self::$rules as $regex => $replacement) {
			if (is_callable ( $replacement)) {
				$text = preg_replace_callback ($regex, $replacement, $text);
			} else {
				$text = preg_replace ($regex, $replacement, $text);
			}
		}
		return trim ($text);
	}
}
////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////




register_module([
	"name" => "Parsedown",
	"version" => "0.1",
	"author" => "Johnny Broadway, Emanuil Rusev & Starbeamrainbowlabs",
	"description" => "An upgraded parser based on Emanuil Rusev's Parsedown Extra PHP library (https://github.com/erusev/parsedown-extra), which is licensed MIT. Also uses a modified Slimdown engine by Johnny Broadway in order to add support for internal links etc. Please be careful, as this module adds a _ton_ of weight to your installation.",
	"id" => "parser-parsedown",
	"optional" => true,
	"code" => function() {
		$parsedown_extra = new ParsedownExtra();
		add_parser("parsedown", function($source) use ($parsedown_extra) {
			
			$result = $parsedown_extra->text($source);
			
			$result = Parsedown_Slimdown_Extensions::render($result);
			
			return $result;
		});
	}
]);

/**
 * Slimdown - A very basic regex-based Markdown parser. Supports the
 * following elements (and can be extended via Slimdown::add_rule()):
 *
 * - Headers
 * - Links
 * - Bold
 * - Emphasis
 * - Deletions
 * - Quotes
 * - Inline code
 * - Blockquotes
 * - Ordered/unordered lists
 * - Horizontal rules
 *
 * Author: Johnny Broadway <johnny@johnnybroadway.com>
 * Website: https://gist.github.com/jbroadway/2836900
 * License: MIT
 */

/**
 * Modified by Starbeamrainbowlabs (starbeamrainbowlabs)
 * 
 	* Changed bold to use single asterisks
 	* Changed italics to use single underscores
 	* Added one to add the heading levels (no <h1> tags allowed)
 	* Added wiki style internal link parsing
 	* Added wiki style internal link parsing with display text
 	* Added image support
 */
class Parsedown_Slimdown_Extensions {
	public static $rules = array (
		'/\r\n/' => "\n",											// new line normalisation
		
//		'/!\[(.*)\]\(([^\s]+)\s(\d+.+)\s(left|right)\)/' => '<img src="\2" alt="\1" style="max-width: \3; float: \4;" />',		// images with size
//		'/!\[(.*)\]\(([^\s]+)\s(\d+.+)\)/' => '<img src="\2" alt="\1" style="max-width: \3;" />',		// images with size
//		'/!\[(.*)\]\((.*)\)/' => '<img src="\2" alt="\1" />',		// basic images
		
		'/\[\[([a-zA-Z0-9\_\- ]+)\|([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\2</a>',	//internal links with display text
		'/\[\[([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\1</a>',	//internal links
	);
	
	/**
	 * Add a rule.
	 */
	public static function add_rule ($regex, $replacement) {
		self::$rules[$regex] = $replacement;
	}
	/**
	 * Render some Markdown into HTML.
	 */
	public static function render ($text) {
		foreach (self::$rules as $regex => $replacement) {
			if (is_callable ( $replacement)) {
				$text = preg_replace_callback ($regex, $replacement, $text);
			} else {
				$text = preg_replace ($regex, $replacement, $text);
			}
		}
		return trim ($text);
	}
}


#
#
# Parsedown
# http://parsedown.org
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class Parsedown
{
    # ~

    const version = '1.5.4';

    # ~

    function text($text)
    {
        # make sure no definitions are set
        $this->DefinitionData = array();

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        $markup = $this->lines($lines);

        # trim line breaks
        $markup = trim($markup, "\n");

        return $markup;
    }

    #
    # Setters
    #

    function setBreaksEnabled($breaksEnabled)
    {
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    }

    protected $breaksEnabled;

    function setMarkupEscaped($markupEscaped)
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }

    protected $markupEscaped;

    function setUrlsLinked($urlsLinked)
    {
        $this->urlsLinked = $urlsLinked;

        return $this;
    }

    protected $urlsLinked = true;

    #
    # Lines
    #

    protected $BlockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );

    # ~

    protected $unmarkedBlockTypes = array(
        'Code',
    );

    #
    # Blocks
    #

    private function lines(array $lines)
    {
        $CurrentBlock = null;

        foreach ($lines as $line)
        {
            if (chop($line) === '')
            {
                if (isset($CurrentBlock))
                {
                    $CurrentBlock['interrupted'] = true;
                }

                continue;
            }

            if (strpos($line, "\t") !== false)
            {
                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part)
                {
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
                }
            }

            $indent = 0;

            while (isset($line[$indent]) and $line[$indent] === ' ')
            {
                $indent ++;
            }

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['continuable']))
            {
                $Block = $this->{'block'.$CurrentBlock['type'].'Continue'}($Line, $CurrentBlock);

                if (isset($Block))
                {
                    $CurrentBlock = $Block;

                    continue;
                }
                else
                {
                    if (method_exists($this, 'block'.$CurrentBlock['type'].'Complete'))
                    {
                        $CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker]))
            {
                foreach ($this->BlockTypes[$marker] as $blockType)
                {
                    $blockTypes []= $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType)
            {
                $Block = $this->{'block'.$blockType}($Line, $CurrentBlock);

                if (isset($Block))
                {
                    $Block['type'] = $blockType;

                    if ( ! isset($Block['identified']))
                    {
                        $Blocks []= $CurrentBlock;

                        $Block['identified'] = true;
                    }

                    if (method_exists($this, 'block'.$blockType.'Continue'))
                    {
                        $Block['continuable'] = true;
                    }

                    $CurrentBlock = $Block;

                    continue 2;
                }
            }

            # ~

            if (isset($CurrentBlock) and ! isset($CurrentBlock['type']) and ! isset($CurrentBlock['interrupted']))
            {
                $CurrentBlock['element']['text'] .= "\n".$text;
            }
            else
            {
                $Blocks []= $CurrentBlock;

                $CurrentBlock = $this->paragraph($Line);

                $CurrentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($CurrentBlock['continuable']) and method_exists($this, 'block'.$CurrentBlock['type'].'Complete'))
        {
            $CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
        }

        # ~

        $Blocks []= $CurrentBlock;

        unset($Blocks[0]);

        # ~

        $markup = '';

        foreach ($Blocks as $Block)
        {
            if (isset($Block['hidden']))
            {
                continue;
            }

            $markup .= "\n";
            $markup .= isset($Block['markup']) ? $Block['markup'] : $this->element($Block['element']);
        }

        $markup .= "\n";

        # ~

        return $markup;
    }

    #
    # Code

    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) and ! isset($Block['type']) and ! isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['indent'] >= 4)
        {
            $text = substr($Line['body'], 4);

            $Block = array(
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => array(
                        'name' => 'code',
                        'text' => $text,
                    ),
                ),
            );

            return $Block;
        }
    }

    protected function blockCodeContinue($Line, $Block)
    {
        if ($Line['indent'] >= 4)
        {
            if (isset($Block['interrupted']))
            {
                $Block['element']['text']['text'] .= "\n";

                unset($Block['interrupted']);
            }

            $Block['element']['text']['text'] .= "\n";

            $text = substr($Line['body'], 4);

            $Block['element']['text']['text'] .= $text;

            return $Block;
        }
    }

    protected function blockCodeComplete($Block)
    {
        $text = $Block['element']['text']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text']['text'] = $text;

        return $Block;
    }

    #
    # Comment

    protected function blockComment($Line)
    {
        if ($this->markupEscaped)
        {
            return;
        }

        if (isset($Line['text'][3]) and $Line['text'][3] === '-' and $Line['text'][2] === '-' and $Line['text'][1] === '!')
        {
            $Block = array(
                'markup' => $Line['body'],
            );

            if (preg_match('/-->$/', $Line['text']))
            {
                $Block['closed'] = true;
            }

            return $Block;
        }
    }

    protected function blockCommentContinue($Line, array $Block)
    {
        if (isset($Block['closed']))
        {
            return;
        }

        $Block['markup'] .= "\n" . $Line['body'];

        if (preg_match('/-->$/', $Line['text']))
        {
            $Block['closed'] = true;
        }

        return $Block;
    }

    #
    # Fenced Code

    protected function blockFencedCode($Line)
    {
        if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches))
        {
            $Element = array(
                'name' => 'code',
                'text' => '',
            );

            if (isset($matches[1]))
            {
                $class = 'language-'.$matches[1];

                $Element['attributes'] = array(
                    'class' => $class,
                );
            }

            $Block = array(
                'char' => $Line['text'][0],
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => $Element,
                ),
            );

            return $Block;
        }
    }

    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            $Block['element']['text']['text'] .= "\n";

            unset($Block['interrupted']);
        }

        if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text']))
        {
            $Block['element']['text']['text'] = substr($Block['element']['text']['text'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['text']['text'] .= "\n".$Line['body'];;

        return $Block;
    }

    protected function blockFencedCodeComplete($Block)
    {
        $text = $Block['element']['text']['text'];

        $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');

        $Block['element']['text']['text'] = $text;

        return $Block;
    }

    #
    # Header

    protected function blockHeader($Line)
    {
        if (isset($Line['text'][1]))
        {
            $level = 1;

            while (isset($Line['text'][$level]) and $Line['text'][$level] === '#')
            {
                $level ++;
            }

            if ($level > 6)
            {
                return;
            }

            $text = trim($Line['text'], '# ');

            $Block = array(
                'element' => array(
                    'name' => 'h' . min(6, $level),
                    'text' => $text,
                    'handler' => 'line',
                ),
            );

            return $Block;
        }
    }

    #
    # List

    protected function blockList($Line)
    {
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');

        if (preg_match('/^('.$pattern.'[ ]+)(.*)/', $Line['text'], $matches))
        {
            $Block = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'element' => array(
                    'name' => $name,
                    'handler' => 'elements',
                ),
            );

            $Block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $matches[2],
                ),
            );

            $Block['element']['text'] []= & $Block['li'];

            return $Block;
        }
    }

    protected function blockListContinue($Line, array $Block)
    {
        if ($Block['indent'] === $Line['indent'] and preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/', $Line['text'], $matches))
        {
            if (isset($Block['interrupted']))
            {
                $Block['li']['text'] []= '';

                unset($Block['interrupted']);
            }

            unset($Block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $Block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $text,
                ),
            );

            $Block['element']['text'] []= & $Block['li'];

            return $Block;
        }

        if ($Line['text'][0] === '[' and $this->blockReference($Line))
        {
            return $Block;
        }

        if ( ! isset($Block['interrupted']))
        {
            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            return $Block;
        }

        if ($Line['indent'] > 0)
        {
            $Block['li']['text'] []= '';

            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            unset($Block['interrupted']);

            return $Block;
        }
    }

    #
    # Quote

    protected function blockQuote($Line)
    {
        if (preg_match('/^>[ ]?(.*)/', $Line['text'], $matches))
        {
            $Block = array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => 'lines',
                    'text' => (array) $matches[1],
                ),
            );

            return $Block;
        }
    }

    protected function blockQuoteContinue($Line, array $Block)
    {
        if ($Line['text'][0] === '>' and preg_match('/^>[ ]?(.*)/', $Line['text'], $matches))
        {
            if (isset($Block['interrupted']))
            {
                $Block['element']['text'] []= '';

                unset($Block['interrupted']);
            }

            $Block['element']['text'] []= $matches[1];

            return $Block;
        }

        if ( ! isset($Block['interrupted']))
        {
            $Block['element']['text'] []= $Line['text'];

            return $Block;
        }
    }

    #
    # Rule

    protected function blockRule($Line)
    {
        if (preg_match('/^(['.$Line['text'][0].'])([ ]*\1){2,}[ ]*$/', $Line['text']))
        {
            $Block = array(
                'element' => array(
                    'name' => 'hr'
                ),
            );

            return $Block;
        }
    }

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        if ( ! isset($Block) or isset($Block['type']) or isset($Block['interrupted']))
        {
            return;
        }

        if (chop($Line['text'], $Line['text'][0]) === '')
        {
            $Block['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';

            return $Block;
        }
    }

    #
    # Markup

    protected function blockMarkup($Line)
    {
        if ($this->markupEscaped)
        {
            return;
        }

        if (preg_match('/^<(\w*)(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*(\/)/', $Line['text'], $matches))
        {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements))
            {
                return;
            }

            $Block = array(
                'name' => $matches[1],
                'depth' => 0,
                'markup' => $Line['text'],
            );

            $length = strlen($matches[0]);

            $remainder = substr($Line['text'], $length);

            if (trim($remainder) === '')
            {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements))
                {
                    $Block['closed'] = true;

                    $Block['void'] = true;
                }
            }
            else
            {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements))
                {
                    return;
                }

                if (preg_match('/<\/'.$matches[1].'>[ ]*$/i', $remainder))
                {
                    $Block['closed'] = true;
                }
            }

            return $Block;
        }
    }

    protected function blockMarkupContinue($Line, array $Block)
    {
        if (isset($Block['closed']))
        {
            return;
        }

        if (preg_match('/^<'.$Block['name'].'(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*>/i', $Line['text'])) # open
        {
            $Block['depth'] ++;
        }

        if (preg_match('/(.*?)<\/'.$Block['name'].'>[ ]*$/i', $Line['text'], $matches)) # close
        {
            if ($Block['depth'] > 0)
            {
                $Block['depth'] --;
            }
            else
            {
                $Block['closed'] = true;
            }
        }

        if (isset($Block['interrupted']))
        {
            $Block['markup'] .= "\n";

            unset($Block['interrupted']);
        }

        $Block['markup'] .= "\n".$Line['body'];

        return $Block;
    }

    #
    # Reference

    protected function blockReference($Line)
    {
        if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $Line['text'], $matches))
        {
            $id = strtolower($matches[1]);

            $Data = array(
                'url' => $matches[2],
                'title' => null,
            );

            if (isset($matches[3]))
            {
                $Data['title'] = $matches[3];
            }

            $this->DefinitionData['Reference'][$id] = $Data;

            $Block = array(
                'hidden' => true,
            );

            return $Block;
        }
    }

    #
    # Table

    protected function blockTable($Line, array $Block = null)
    {
        if ( ! isset($Block) or isset($Block['type']) or isset($Block['interrupted']))
        {
            return;
        }

        if (strpos($Block['element']['text'], '|') !== false and chop($Line['text'], ' -:|') === '')
        {
            $alignments = array();

            $divider = $Line['text'];

            $divider = trim($divider);
            $divider = trim($divider, '|');

            $dividerCells = explode('|', $divider);

            foreach ($dividerCells as $dividerCell)
            {
                $dividerCell = trim($dividerCell);

                if ($dividerCell === '')
                {
                    continue;
                }

                $alignment = null;

                if ($dividerCell[0] === ':')
                {
                    $alignment = 'left';
                }

                if (substr($dividerCell, - 1) === ':')
                {
                    $alignment = $alignment === 'left' ? 'center' : 'right';
                }

                $alignments []= $alignment;
            }

            # ~

            $HeaderElements = array();

            $header = $Block['element']['text'];

            $header = trim($header);
            $header = trim($header, '|');

            $headerCells = explode('|', $header);

            foreach ($headerCells as $index => $headerCell)
            {
                $headerCell = trim($headerCell);

                $HeaderElement = array(
                    'name' => 'th',
                    'text' => $headerCell,
                    'handler' => 'line',
                );

                if (isset($alignments[$index]))
                {
                    $alignment = $alignments[$index];

                    $HeaderElement['attributes'] = array(
                        'style' => 'text-align: '.$alignment.';',
                    );
                }

                $HeaderElements []= $HeaderElement;
            }

            # ~

            $Block = array(
                'alignments' => $alignments,
                'identified' => true,
                'element' => array(
                    'name' => 'table',
                    'handler' => 'elements',
                ),
            );

            $Block['element']['text'] []= array(
                'name' => 'thead',
                'handler' => 'elements',
            );

            $Block['element']['text'] []= array(
                'name' => 'tbody',
                'handler' => 'elements',
                'text' => array(),
            );

            $Block['element']['text'][0]['text'] []= array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $HeaderElements,
            );

            return $Block;
        }
    }

    protected function blockTableContinue($Line, array $Block)
    {
        if (isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['text'][0] === '|' or strpos($Line['text'], '|'))
        {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell)
            {
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                );

                if (isset($Block['alignments'][$index]))
                {
                    $Element['attributes'] = array(
                        'style' => 'text-align: '.$Block['alignments'][$index].';',
                    );
                }

                $Elements []= $Element;
            }

            $Element = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $Elements,
            );

            $Block['element']['text'][1]['text'] []= $Element;

            return $Block;
        }
    }

    #
    # ~
    #

    protected function paragraph($Line)
    {
        $Block = array(
            'element' => array(
                'name' => 'p',
                'text' => $Line['text'],
                'handler' => 'line',
            ),
        );

        return $Block;
    }

    #
    # Inline Elements
    #

    protected $InlineTypes = array(
        '"' => array('SpecialCharacter'),
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup', 'SpecialCharacter'),
        '>' => array('SpecialCharacter'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );

    # ~

    protected $inlineMarkerList = '!"*_&[:<>`~\\';

    #
    # ~
    #

    public function line($text)
    {
        $markup = '';

        # $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList))
        {
            $marker = $excerpt[0];

            $markerPosition = strpos($text, $marker);

            $Excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->InlineTypes[$marker] as $inlineType)
            {
                $Inline = $this->{'inline'.$inlineType}($Excerpt);

                if ( ! isset($Inline))
                {
                    continue;
                }

                # makes sure that the inline belongs to "our" marker

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition)
                {
                    continue;
                }

                # sets a default inline position

                if ( ! isset($Inline['position']))
                {
                    $Inline['position'] = $markerPosition;
                }

                # the text that comes before the inline
                $unmarkedText = substr($text, 0, $Inline['position']);

                # compile the unmarked text
                $markup .= $this->unmarkedText($unmarkedText);

                # compile the inline
                $markup .= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);

                # remove the examined text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                continue 2;
            }

            # the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $markup .= $this->unmarkedText($unmarkedText);

            $text = substr($text, $markerPosition + 1);
        }

        $markup .= $this->unmarkedText($text);

        return $markup;
    }

    #
    # ~
    #

    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];

        if (preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s', $Excerpt['text'], $matches))
        {
            $text = $matches[2];
            $text = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
            $text = preg_replace("/[ ]*\n/", ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
    }

    protected function inlineEmailTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $Excerpt['text'], $matches))
        {
            $url = $matches[1];

            if ( ! isset($matches[2]))
            {
                $url = 'mailto:' . $url;
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    protected function inlineEmphasis($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]))
        {
            return;
        }

        $marker = $Excerpt['text'][0];

        if ($Excerpt['text'][1] === $marker and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches))
        {
            $emphasis = 'strong';
        }
        elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches))
        {
            $emphasis = 'em';
        }
        else
        {
            return;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ),
        );
    }

    protected function inlineEscapeSequence($Excerpt)
    {
        if (isset($Excerpt['text'][1]) and in_array($Excerpt['text'][1], $this->specialCharacters))
        {
            return array(
                'markup' => $Excerpt['text'][1],
                'extent' => 2,
            );
        }
    }

    protected function inlineImage($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]) or $Excerpt['text'][1] !== '[')
        {
            return;
        }

        $Excerpt['text']= substr($Excerpt['text'], 1);

        $Link = $this->inlineLink($Excerpt);

        if ($Link === null)
        {
            return;
        }

        $Inline = array(
            'extent' => $Link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $Link['element']['attributes']['href'],
                    'alt' => $Link['element']['text'],
                ),
            ),
        );

        $Inline['element']['attributes'] += $Link['element']['attributes'];

        unset($Inline['element']['attributes']['href']);

        return $Inline;
    }

    protected function inlineLink($Excerpt)
    {
        $Element = array(
            'name' => 'a',
            'handler' => 'line',
            'text' => null,
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $Excerpt['text'];

        if (preg_match('/\[((?:[^][]|(?R))*)\]/', $remainder, $matches))
        {
            $Element['text'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        }
        else
        {
            return;
        }

        if (preg_match('/^[(]((?:[^ ()]|[(][^ )]+[)])+)(?:[ ]+("[^"]*"|\'[^\']*\'))?[)]/', $remainder, $matches))
        {
            $Element['attributes']['href'] = $matches[1];

            if (isset($matches[2]))
            {
                $Element['attributes']['title'] = substr($matches[2], 1, - 1);
            }

            $extent += strlen($matches[0]);
        }
        else
        {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches))
            {
                $definition = strlen($matches[1]) ? $matches[1] : $Element['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            }
            else
            {
                $definition = strtolower($Element['text']);
            }

            if ( ! isset($this->DefinitionData['Reference'][$definition]))
            {
                return;
            }

            $Definition = $this->DefinitionData['Reference'][$definition];

            $Element['attributes']['href'] = $Definition['url'];
            $Element['attributes']['title'] = $Definition['title'];
        }

        $Element['attributes']['href'] = str_replace(array('&', '<'), array('&amp;', '&lt;'), $Element['attributes']['href']);

        return array(
            'extent' => $extent,
            'element' => $Element,
        );
    }

    protected function inlineMarkup($Excerpt)
    {
        if ($this->markupEscaped or strpos($Excerpt['text'], '>') === false)
        {
            return;
        }

        if ($Excerpt['text'][1] === '/' and preg_match('/^<\/\w*[ ]*>/s', $Excerpt['text'], $matches))
        {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] === '!' and preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $Excerpt['text'], $matches))
        {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] !== ' ' and preg_match('/^<\w*(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*\//s', $Excerpt['text'], $matches))
        {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }
    }

    protected function inlineSpecialCharacter($Excerpt)
    {
        if ($Excerpt['text'][0] === '&' and ! preg_match('/^&#?\w+;/', $Excerpt['text']))
        {
            return array(
                'markup' => '&amp;',
                'extent' => 1,
            );
        }

        $SpecialCharacter = array('>' => 'gt', '<' => 'lt', '"' => 'quot');

        if (isset($SpecialCharacter[$Excerpt['text'][0]]))
        {
            return array(
                'markup' => '&'.$SpecialCharacter[$Excerpt['text'][0]].';',
                'extent' => 1,
            );
        }
    }

    protected function inlineStrikethrough($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]))
        {
            return;
        }

        if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches))
        {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'text' => $matches[1],
                    'handler' => 'line',
                ),
            );
        }
    }

    protected function inlineUrl($Excerpt)
    {
        if ($this->urlsLinked !== true or ! isset($Excerpt['text'][2]) or $Excerpt['text'][2] !== '/')
        {
            return;
        }

        if (preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE))
        {
            $Inline = array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[0][0],
                    'attributes' => array(
                        'href' => $matches[0][0],
                    ),
                ),
            );

            return $Inline;
        }
    }

    protected function inlineUrlTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $Excerpt['text'], $matches))
        {
            $url = str_replace(array('&', '<'), array('&amp;', '&lt;'), $matches[1]);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    # ~

    protected function unmarkedText($text)
    {
        if ($this->breaksEnabled)
        {
            $text = preg_replace('/[ ]*\n/', "<br />\n", $text);
        }
        else
        {
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text);
            $text = str_replace(" \n", "\n", $text);
        }

        return $text;
    }

    #
    # Handlers
    #

    protected function element(array $Element)
    {
        $markup = '<'.$Element['name'];

        if (isset($Element['attributes']))
        {
            foreach ($Element['attributes'] as $name => $value)
            {
                if ($value === null)
                {
                    continue;
                }

                $markup .= ' '.$name.'="'.$value.'"';
            }
        }

        if (isset($Element['text']))
        {
            $markup .= '>';

            if (isset($Element['handler']))
            {
                $markup .= $this->{$Element['handler']}($Element['text']);
            }
            else
            {
                $markup .= $Element['text'];
            }

            $markup .= '</'.$Element['name'].'>';
        }
        else
        {
            $markup .= ' />';
        }

        return $markup;
    }

    protected function elements(array $Elements)
    {
        $markup = '';

        foreach ($Elements as $Element)
        {
            $markup .= "\n" . $this->element($Element);
        }

        $markup .= "\n";

        return $markup;
    }

    # ~

    protected function li($lines)
    {
        $markup = $this->lines($lines);

        $trimmedMarkup = trim($markup);

        if ( ! in_array('', $lines) and substr($trimmedMarkup, 0, 3) === '<p>')
        {
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);

            $position = strpos($markup, "</p>");

            $markup = substr_replace($markup, '', $position, 4);
        }

        return $markup;
    }

    #
    # Deprecated Methods
    #

    function parse($text)
    {
        $markup = $this->text($text);

        return $markup;
    }

    #
    # Static Methods
    #

    static function instance($name = 'default')
    {
        if (isset(self::$instances[$name]))
        {
            return self::$instances[$name];
        }

        $instance = new static();

        self::$instances[$name] = $instance;

        return $instance;
    }

    private static $instances = array();

    #
    # Fields
    #

    protected $DefinitionData;

    #
    # Read-Only

    protected $specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|',
    );

    protected $StrongRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',
    );

    protected $EmRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    protected $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';

    protected $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );

    protected $textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code',          'strike', 'marquee',
        'q', 'rt', 'ins', 'font',          'strong',
        's', 'tt', 'sub', 'mark',
        'u', 'xm', 'sup', 'nobr',
                   'var', 'ruby',
                   'wbr', 'span',
                          'time',
    );
}

#
#
# Parsedown Extra
# https://github.com/erusev/parsedown-extra
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class ParsedownExtra extends Parsedown
{
    # ~

    const version = '0.7.0';

    # ~

    function __construct()
    {
        if (parent::version < '1.5.0')
        {
            throw new Exception('ParsedownExtra requires a later version of Parsedown');
        }

        $this->BlockTypes[':'] []= 'DefinitionList';
        $this->BlockTypes['*'] []= 'Abbreviation';

        # identify footnote definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'Footnote');

        # identify footnote markers before before links
        array_unshift($this->InlineTypes['['], 'FootnoteMarker');
    }

    #
    # ~

    function text($text)
    {
        $markup = parent::text($text);

        # merge consecutive dl elements

        $markup = preg_replace('/<\/dl>\s+<dl>\s+/', '', $markup);

        # add footnotes

        if (isset($this->DefinitionData['Footnote']))
        {
            $Element = $this->buildFootnoteElement();

            $markup .= "\n" . $this->element($Element);
        }

        return $markup;
    }

    #
    # Blocks
    #

    #
    # Abbreviation

    protected function blockAbbreviation($Line)
    {
        if (preg_match('/^\*\[(.+?)\]:[ ]*(.+?)[ ]*$/', $Line['text'], $matches))
        {
            $this->DefinitionData['Abbreviation'][$matches[1]] = $matches[2];

            $Block = array(
                'hidden' => true,
            );

            return $Block;
        }
    }

    #
    # Footnote

    protected function blockFootnote($Line)
    {
        if (preg_match('/^\[\^(.+?)\]:[ ]?(.*)$/', $Line['text'], $matches))
        {
            $Block = array(
                'label' => $matches[1],
                'text' => $matches[2],
                'hidden' => true,
            );

            return $Block;
        }
    }

    protected function blockFootnoteContinue($Line, $Block)
    {
        if ($Line['text'][0] === '[' and preg_match('/^\[\^(.+?)\]:/', $Line['text']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            if ($Line['indent'] >= 4)
            {
                $Block['text'] .= "\n\n" . $Line['text'];

                return $Block;
            }
        }
        else
        {
            $Block['text'] .= "\n" . $Line['text'];

            return $Block;
        }
    }

    protected function blockFootnoteComplete($Block)
    {
        $this->DefinitionData['Footnote'][$Block['label']] = array(
            'text' => $Block['text'],
            'count' => null,
            'number' => null,
        );

        return $Block;
    }

    #
    # Definition List

    protected function blockDefinitionList($Line, $Block)
    {
        if ( ! isset($Block) or isset($Block['type']))
        {
            return;
        }

        $Element = array(
            'name' => 'dl',
            'handler' => 'elements',
            'text' => array(),
        );

        $terms = explode("\n", $Block['element']['text']);

        foreach ($terms as $term)
        {
            $Element['text'] []= array(
                'name' => 'dt',
                'handler' => 'line',
                'text' => $term,
            );
        }

        $Block['element'] = $Element;

        $Block = $this->addDdElement($Line, $Block);

        return $Block;
    }

    protected function blockDefinitionListContinue($Line, array $Block)
    {
        if ($Line['text'][0] === ':')
        {
            $Block = $this->addDdElement($Line, $Block);

            return $Block;
        }
        else
        {
            if (isset($Block['interrupted']) and $Line['indent'] === 0)
            {
                return;
            }

            if (isset($Block['interrupted']))
            {
                $Block['dd']['handler'] = 'text';
                $Block['dd']['text'] .= "\n\n";

                unset($Block['interrupted']);
            }

            $text = substr($Line['body'], min($Line['indent'], 4));

            $Block['dd']['text'] .= "\n" . $text;

            return $Block;
        }
    }

    #
    # Header

    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);

        if (preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['text'], $matches, PREG_OFFSET_CAPTURE))
        {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['text'] = substr($Block['element']['text'], 0, $matches[0][1]);
        }

        return $Block;
    }

    #
    # Markup

    protected function blockMarkupComplete($Block)
    {
        if ( ! isset($Block['void']))
        {
            $Block['markup'] = $this->processTag($Block['markup']);
        }

        return $Block;
    }

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);

        if (preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['text'], $matches, PREG_OFFSET_CAPTURE))
        {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['text'] = substr($Block['element']['text'], 0, $matches[0][1]);
        }

        return $Block;
    }

    #
    # Inline Elements
    #

    #
    # Footnote Marker

    protected function inlineFootnoteMarker($Excerpt)
    {
        if (preg_match('/^\[\^(.+?)\]/', $Excerpt['text'], $matches))
        {
            $name = $matches[1];

            if ( ! isset($this->DefinitionData['Footnote'][$name]))
            {
                return;
            }

            $this->DefinitionData['Footnote'][$name]['count'] ++;

            if ( ! isset($this->DefinitionData['Footnote'][$name]['number']))
            {
                $this->DefinitionData['Footnote'][$name]['number'] = ++ $this->footnoteCount; # » &
            }

            $Element = array(
                'name' => 'sup',
                'attributes' => array('id' => 'fnref'.$this->DefinitionData['Footnote'][$name]['count'].':'.$name),
                'handler' => 'element',
                'text' => array(
                    'name' => 'a',
                    'attributes' => array('href' => '#fn:'.$name, 'class' => 'footnote-ref'),
                    'text' => $this->DefinitionData['Footnote'][$name]['number'],
                ),
            );

            return array(
                'extent' => strlen($matches[0]),
                'element' => $Element,
            );
        }
    }

    private $footnoteCount = 0;

    #
    # Link

    protected function inlineLink($Excerpt)
    {
        $Link = parent::inlineLink($Excerpt);

        $remainder = substr($Excerpt['text'], $Link['extent']);

        if (preg_match('/^[ ]*{('.$this->regexAttribute.'+)}/', $remainder, $matches))
        {
            $Link['element']['attributes'] += $this->parseAttributeData($matches[1]);

            $Link['extent'] += strlen($matches[0]);
        }

        return $Link;
    }

    #
    # ~
    #

    protected function unmarkedText($text)
    {
        $text = parent::unmarkedText($text);

        if (isset($this->DefinitionData['Abbreviation']))
        {
            foreach ($this->DefinitionData['Abbreviation'] as $abbreviation => $meaning)
            {
                $pattern = '/\b'.preg_quote($abbreviation, '/').'\b/';

                $text = preg_replace($pattern, '<abbr title="'.$meaning.'">'.$abbreviation.'</abbr>', $text);
            }
        }

        return $text;
    }

    #
    # Util Methods
    #

    protected function addDdElement(array $Line, array $Block)
    {
        $text = substr($Line['text'], 1);
        $text = trim($text);

        unset($Block['dd']);

        $Block['dd'] = array(
            'name' => 'dd',
            'handler' => 'line',
            'text' => $text,
        );

        if (isset($Block['interrupted']))
        {
            $Block['dd']['handler'] = 'text';

            unset($Block['interrupted']);
        }

        $Block['element']['text'] []= & $Block['dd'];

        return $Block;
    }

    protected function buildFootnoteElement()
    {
        $Element = array(
            'name' => 'div',
            'attributes' => array('class' => 'footnotes'),
            'handler' => 'elements',
            'text' => array(
                array(
                    'name' => 'hr',
                ),
                array(
                    'name' => 'ol',
                    'handler' => 'elements',
                    'text' => array(),
                ),
            ),
        );

        uasort($this->DefinitionData['Footnote'], 'self::sortFootnotes');

        foreach ($this->DefinitionData['Footnote'] as $definitionId => $DefinitionData)
        {
            if ( ! isset($DefinitionData['number']))
            {
                continue;
            }

            $text = $DefinitionData['text'];

            $text = parent::text($text);

            $numbers = range(1, $DefinitionData['count']);

            $backLinksMarkup = '';

            foreach ($numbers as $number)
            {
                $backLinksMarkup .= ' <a href="#fnref'.$number.':'.$definitionId.'" rev="footnote" class="footnote-backref">&#8617;</a>';
            }

            $backLinksMarkup = substr($backLinksMarkup, 1);

            if (substr($text, - 4) === '</p>')
            {
                $backLinksMarkup = '&#160;'.$backLinksMarkup;

                $text = substr_replace($text, $backLinksMarkup.'</p>', - 4);
            }
            else
            {
                $text .= "\n".'<p>'.$backLinksMarkup.'</p>';
            }

            $Element['text'][1]['text'] []= array(
                'name' => 'li',
                'attributes' => array('id' => 'fn:'.$definitionId),
                'text' => "\n".$text."\n",
            );
        }

        return $Element;
    }

    # ~

    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/[ ]+/', $attributeString, - 1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute)
        {
            if ($attribute[0] === '#')
            {
                $Data['id'] = substr($attribute, 1);
            }
            else # "."
            {
                $classes []= substr($attribute, 1);
            }
        }

        if (isset($classes))
        {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    # ~

    protected function processTag($elementMarkup) # recursive
    {
        # http://stackoverflow.com/q/1148928/200145
        libxml_use_internal_errors(true);

        $DOMDocument = new DOMDocument;

        # http://stackoverflow.com/q/11309194/200145
        $elementMarkup = mb_convert_encoding($elementMarkup, 'HTML-ENTITIES', 'UTF-8');

        # http://stackoverflow.com/q/4879946/200145
        $DOMDocument->loadHTML($elementMarkup);
        $DOMDocument->removeChild($DOMDocument->doctype);
        $DOMDocument->replaceChild($DOMDocument->firstChild->firstChild->firstChild, $DOMDocument->firstChild);

        $elementText = '';

        if ($DOMDocument->documentElement->getAttribute('markdown') === '1')
        {
            foreach ($DOMDocument->documentElement->childNodes as $Node)
            {
                $elementText .= $DOMDocument->saveHTML($Node);
            }

            $DOMDocument->documentElement->removeAttribute('markdown');

            $elementText = "\n".$this->text($elementText)."\n";
        }
        else
        {
            foreach ($DOMDocument->documentElement->childNodes as $Node)
            {
                $nodeMarkup = $DOMDocument->saveHTML($Node);

                if ($Node instanceof DOMElement and ! in_array($Node->nodeName, $this->textLevelElements))
                {
                    $elementText .= $this->processTag($nodeMarkup);
                }
                else
                {
                    $elementText .= $nodeMarkup;
                }
            }
        }

        # because we don't want for markup to get encoded
        $DOMDocument->documentElement->nodeValue = 'placeholder';

        $markup = $DOMDocument->saveHTML($DOMDocument->documentElement);
        $markup = str_replace('placeholder', $elementText, $markup);

        return $markup;
    }

    # ~

    protected function sortFootnotes($A, $B) # callback
    {
        return $A['number'] - $B['number'];
    }

    #
    # Fields
    #

    protected $regexAttribute = '(?:[#.][-\w]+[ ]*)';
}



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
