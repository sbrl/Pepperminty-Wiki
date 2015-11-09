<?php
$start_time = time(true);

// For debugging purposes. Remove or comment out for live sites.
// This will always be commented out for a release.
if(file_exists("php_error.php"))
{
    require("php_error.php");
    \php_error\reportErrors([ "error_reporting_on" => E_ALL | E_STRICT ]);
}


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
// Note that by default the session cookie is prefixed with a variant of the
// sitename so changing this will log everyone out!
$settings->sitename = "Pepperminty Wiki";

// A url that points to the site's logo. Leave blank to disable. When enabled
// the logo will be inserted next to the site name on every page.
$settings->logo_url = "//starbeamrainbowlabs.com/images/logos/peppermint.png";

// The side of the site name at which the logo should be placed. May be set to
// either "left" or "right". Only has an effect if the above is not set to an
// empty string.
$settings->logo_position = "left";

// The url from which to fetch updates. Defaults to the master (development)
// branch If there is sufficient demand, a separate stable branch will be
// created. Note that if you use the automatic updater currently it won't save
// your module choices.
// MAKE SURE THAT THIS POINTS TO A *HTTPS* URL, OTHERWISE SOMEONE COULD INJECT
// A VIRUS INTO YOUR WIKI
$settings->updateurl = "https://raw.githubusercontent.com/sbrl/pepperminty-wiki/master/index.php";

// The secret key used to perform 'dangerous' actions, like updating the wiki,
// and deleting pages. It is strongly advised that you change this!
$settings->sitesecret = "ed420502615bac9037f8f12abd4c9f02";

// The directory in which to store all files, except this main index.php.
// A single dot ('.') denotes the current directory.
// Remember to omit the trailing slash from the directory name, as it is added
// automatically by Pepperminty Wiki.
// Note that this setting is currently experimental.
$settings->data_storage_dir = "../data_test";

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

// Whether to use the new sha3 hashing algorithm that was standardised on the
// 8th August 2015. Only works if you have strawbrary's sha3 extension
// installed. Get it here: https://github.com/strawbrary/php-sha3
// Note: If you change this settings, make sure to update the password hashes
// above! Note that the hash action is aware of this option and will hash
// passwords appropriately based on this setting.
$settings->use_sha3 = false;

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
	[ "Upload", "index.php?action=upload" ],
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

// Whether to allow image uploads to the server. Currently disabled temporarily
// for security reasons while I finish writing the file uploader.
$settings->upload_enabled = true;

// An array of mime types that are allowed to be uploaded.
$settings->upload_allowed_file_types = [
	"image/jpeg",
	"image/png",
	"image/gif",
	"image/webp"
];

// The default file type for previews. Defaults to image/png. Also supports
// image/jpeg and image/webp. image/webp is a new image format that reduces
// image sizez by ~20%, but PHP still has some issues with invalid webp images.
$settings->preview_file_type = "image/png";

// The default size of preview images.
$settings->default_preview_size = 640;

// The location of a file that maps mime types onto file extensions and vice
// versa. Used to generate the file extension for an uploaded file. Set to the
// default location of the mime.types file on Linux. If you aren't using linux,
// download this pastebin and point this setting at it instead:
// http://pastebin.com/mjM3zKjz
$settings->mime_extension_mappings_location = "/etc/mime.types";

// The minimum and maximum sizes of generated preview images in pixels.
$settings->min_preview_size = 1;
$settings->max_preview_size = 2048;

// The maximum distance terms should be apart in the context display below
// search results. This is purely aesthetical - it doesn't affect the search
// algorithm.
$settings->search_max_distance_context_display = 100;

// The number of characters that should be displayed either side of a matching
// term in the context below each search result.
$settings->search_characters_context = 200;

// The weighting to give to search term matches found in a page's title.
$settings->search_title_matches_weighting = 10;

// The weighting to give to search term matches found in a page's tags.
$settings->search_tags_matches_weighting = 3;

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

.preview { text-align: center; }
.preview img { max-width: 100%; }
.image-controls ul { list-style-type: none; margin: 5px; padding: 5px; }
.image-controls li { display: inline-block; margin: 5px; padding: 5px; }
.link-display { margin-left: 0.5rem; }

.printable { padding: 2rem; }

h1 { text-align: center; }
.sitename { margin-top: 5rem; margin-bottom: 3rem; font-size: 2.5rem; }
.logo { max-width: 4rem; max-height: 4rem; vertical-align: middle; }
main:not(.printable) { padding: 2rem; background: #faf8fb; box-shadow: 0 0.1rem 1rem 0.3rem rgba(50, 50, 50, 0.5); }

.search-result { position: relative; }
.search-result::before { content: attr(data-result-number); position: relative; top: 3.2rem; color: rgba(33, 33, 33, 0.3); font-size: 2rem; }
.search-result::after { content: \"Rank: \" attr(data-rank); position: absolute; top: 3.8rem; right: 0.7rem; color: rgba(50, 50, 50, 0.3); }
.search-result > h2 { margin-left: 2rem; }

label:not(.link-display-label) { display: inline-block; min-width: 7rem; }
input[type=text]:not(.link-display), input[type=password], textarea { margin: 0.5rem 0.8rem; }
input[type=text], input[type=password], textarea, #search-box { padding: 0.5rem 0.8rem; background: #d5cbf9; border: 0; border-radius: 0.3rem; font-size: 1rem; color: #442772; }
textarea { min-height: 35rem; font-size: 1.25rem; }
textarea, textarea ~ input[type=submit], #search-box { width: calc(100% - 0.3rem); box-sizing: border-box; }
textarea ~ input[type=submit] { margin: 0.5rem 0.8rem; padding: 0.5rem; font-weight: bolder; }
.editform input[type=text] { width: calc(100% - 0.3rem); box-sizing: border-box; }

.page-tags-display { margin: 0.5rem 0 0 0; padding: 0; list-style-type: none; }
.page-tags-display li { display: inline-block; margin: 0.5rem; padding: 0.5rem; background: #D2C3DD; white-space: nowrap; }
.page-tags-display li a { color: #FB701A; text-decoration: none; }
.page-tags-display li:before { content: \"\\A\"; position: relative; top: 0.03rem; left: -0.9rem; width: 0; height: 0; border-top: 0.6rem solid transparent; border-bottom: 0.6rem solid transparent; border-right: 0.5rem solid #D2C3DD; }

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
$version = "v0.9-dev";
/// Environment ///
$env = new stdClass();
$env->action = $settings->defaultaction;
$env->page = "";
$env->user = "Anonymous";
$env->is_logged_in = false;
$env->is_admin = false;
$env->storage_prefix = $settings->data_storage_dir . DIRECTORY_SEPARATOR;
/// Paths ///
$paths = new stdClass();
$paths->pageindex = "pageindex.json"; // The pageindex
$paths->searchindex = "invindex.json"; // The inverted index used for searching
$paths->idindex = "idindex.json"; // The index that converts ids to page names

// Prepend the storage data directory to all the defined paths.
foreach ($paths as &$path) {
    $path = $env->storage_prefix . $path;
}

$paths->upload_file_prefix = "Files/"; // The prefix to append to uploaded files

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
 * @summary Gets a list of all the sub pages of the current page.
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
	$string = preg_replace("/[^0-9a-zA-Z\_\-\ \/\.]/i", "", $string);
	$string = preg_replace("/\.+/", ".", $string);
	return $string;
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
/*
 * @summary Checks to see if $haystack starts with $needle.
 *
 * @param $haystack {string} The string to search.
 * @param $needle {string} The string to search for at the beginning of $haystack.
 *
 * @returns {boolean} Whether $needle can be found at the beginning of $haystack.
 */
function starts_with($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}
/**
 * mb_stripos all occurences
 * from http://www.pontikis.net/tip/?id=16
 * based on http://www.php.net/manual/en/function.strpos.php#87061
 *
 * Find all occurrences of a needle in a haystack (case-insensitive, UTF8)
 *
 * @param string $haystack
 * @param string $needle
 * @return array or false
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

function system_mime_type_extensions() {
	global $settings;
    # Returns the system MIME type mapping of MIME types to extensions, as defined in /etc/mime.types (considering the first
    # extension listed to be canonical).
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
function system_mime_type_extension($type) {
    # Returns the canonical file extension for the MIME type specified, as defined in /etc/mime.types (considering the first
    # extension listed to be canonical).
    #
    # $type - the MIME type
    static $exts;
    if(!isset($exts))
        $exts = system_mime_type_extensions();
    return isset($exts[$type]) ? $exts[$type] : null;
}

///////////////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////// Security and Consistency Measures ////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

/*
 * Sort out the pageindex. We create it if it doesn't exist, and load and parse
 * it if it does.
 */
if(!file_exists($paths->pageindex))
{
	$existingpages = glob_recursive($env->storage_prefix . "*.md");
	$pageindex = new stdClass();
	// We use a for loop here because foreach doesn't loop over new values inserted
	// while we were looping
	for($i = 0; $i < count($existingpages); $i++)
	{
		$pagefilename = $existingpages[$i];

		// Create a new entry
		$newentry = new stdClass();
		$newentry->filename = utf8_encode(substr($pagefilename, strlen($env->storage_prefix))); // Store the filename
		$newentry->size = filesize($pagefilename); // Store the page size
		$newentry->lastmodified = filemtime($pagefilename); // Store the date last modified
		// Todo find a way to keep the last editor independent of the page index
		$newentry->lasteditor = utf8_encode("unknown"); // Set the editor to "unknown"
		// Extract the name of the (sub)page without the ".md" or the storage dir
		$pagekey = utf8_encode(substr($pagefilename, strlen($env->storage_prefix), -3));

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
	header("x-pageindex-decode-time: " . round(microtime(true) - $pageindex_read_start, 6) . "ms");
}

//////////////////////////
///// Page id system /////
//////////////////////////
if(!file_exists($paths->idindex))
	file_put_contents($paths->idindex, "{}");
$idindex = json_decode(file_get_contents($paths->idindex));
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
	 * @summary Assigns an id to a pagename. Doesn't check to make sure that
	 * 			pagename doesn't exist in the pageindex.
	 */
	protected static function assign($pagename)
	{
		global $idindex, $paths;

		$nextid = count(array_keys(get_object_vars($idindex)));

		if(isset($idindex->$nextid))
			throw new Exception("The pageid is corrupt! Pepperminty Wiki generated the id $nextid, but that id is already in use.");

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
			<p>Powered by Pepperminty Wiki v0.9-dev, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or <a href='//github.com/sbrl/Pepperminty-Wiki' title='Github Issue Tracker'>open an issue</a>.</p>
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
			<p><em>Powered by Pepperminty Wiki v0.9-dev.</em></p>
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
		global $settings, $start_time, $version;

		if($body_template === false)
			$body_template = self::$main_content_template;

		if(strlen($settings->logo_url) > 0)
		{
			// A logo url has been specified
			$logo_html = "<img class='logo' src='$settings->logo_url' />";
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
			"v0.9-dev" => $version,
			"{favicon-url}" => $settings->favicon,
			"{header-html}" => self::get_css_as_html(),

			"{navigation-bar}" => self::render_navigation_bar($settings->nav_links, $settings->nav_links_extra, "top"),
			"{navigation-bar-bottom}" => self::render_navigation_bar($settings->nav_links_bottom, [], "bottom"),

			"{admin-details-name}" => $settings->admindetails["name"],
			"{admin-details-email}" => $settings->admindetails["email"],

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

		$result = str_replace([

		], [
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
						$result .= "<span class='inflexible'><form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /><input type='hidden' name='search-redirect' value='true' /></form></span>";
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

//////////////////////////////////////
///// Extra consistency measures /////
//////////////////////////////////////
if(!isset($pageindex->{$env->page}) and isset($_GET["search-redirect"]))
{
	http_response_code(307);
	$url = "?action=search&query=" . rawurlencode($env->page);
	header("location: $url");
	exit(pagerenderer::render("Non existent page - $settings->sitename", "<p>There isn't a page on $settings->sitename with that name. However, you could <a href='$url'>search for this page name</a> in other pages.</p>
		<p>Alternatively, you could <a href='?action=edit&page=" . rawurlencode($env->page) . "&create=true'>create this page</a>.</p>"));
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
			global $settings;
			
			if(!isset($_GET["string"]))
			{
				http_response_code(422);
				exit(page_renderer::render_main("Missing parameter", "<p>The <code>GET</code> parameter <code>string</code> must be specified.</p>
		<p>It is strongly recommended that you utilise this page via a private or incognito window in order to prevent your password from appearing in your browser history.</p>"));
			}
			else
			{
				exit(page_renderer::render_main("Hashed string", "<p>Algorithm: " . ($settings->use_sha3 ? "sha3" : "sha256") . "</p>\n<p><code>" . $_GET["string"] . "</code> → <code>" . hash_password($_GET["string"]) . "</code></p>"));
			}
		});
	}
]);




register_module([
	"name" => "Page protection",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Exposes Pepperminty Wiki's new page protection mechanism and makes the protect button in the 'More...' menu on the top bar work.",
	"id" => "action-protect",
	"code" => function() {
		add_action("protect", function() {
			global $env, $pageindex, $paths;

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
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));

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
	"version" => "0.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		add_action("raw", function() {
			global $env;

			http_response_code(307);
			header("x-filename: " . rawurlencode($env->page) . ".md");
			header("content-type: text/markdown");
			exit(file_get_contents("$env->storage_prefix$env->page.md"));
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
	}
]);




register_module([
	"name" => "Search",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki. Note that this module, at the moment, just contains test code while I figure out how best to write a search engine.",
	"id" => "feature-search",
	"code" => function() {
		add_action("index", function() {
			global $settings, $env;
			
			$breakable_chars = "\r\n\t .,\\/!\"£$%^&*[]()+`_~#";
			
			header("content-type: text/plain");
			
			$source = file_get_contents("$env->storage_prefix$env->page.md");
			
			$index = search::index($source);
			
			var_dump($env->page);
			var_dump($source);
			
			var_dump($index);
		});
		
		add_action("invindex-rebuild", function() {
			search::rebuild_invindex();
		});
		
		add_action("search", function() {
			global $settings, $env, $pageindex, $paths;
			
			if(!isset($_GET["query"]))
				exit(page_renderer::render("No Search Terms - Error - $settings->sitename", "<p>You didn't specify any search terms. Try typing some into the box above.</p>"));
			
			$search_start = microtime(true);
			
			$invindex = search::load_invindex($paths->searchindex);
			$results = search::query_invindex($_GET["query"], $invindex);

			$search_end = microtime(true) - $search_start;

			$title = $_GET["query"] . " - Search results - $settings->sitename";
			
			$content = "<section>\n";
			$content .= "<h1>Search Results</h1>";
			
			/// Search Box ///
			$content .= "<form method='get' action=''>\n";
			$content .= "	<input type='search' id='search-box' name='query' placeholder='Type your query here and then press enter.' value='" . $_GET["query"] . "' />\n";
			$content .= "	<input type='hidden' name='action' value='search' />\n";
			$content .= "</form>";
			
			$query = $_GET["query"];
			if(isset($pageindex->$query))
			{
				$content .= "<p>There's a page on $settings->sitename called <a href='?page=" . rawurlencode($query) . "'>$query</a>.</p>";
			}
			else
			{
				$content .= "<p>There isn't a page called $query on $settings->sitename, but you can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.</p>";
			}
			
			$i = 0; // todo use $_GET["offset"] and $_GET["result-count"] or something
			foreach($results as $result)
			{
				$link = "?page=" . rawurlencode($result["pagename"]);
				$pagesource = file_get_contents($env->storage_prefix . $result["pagename"] . ".md");
				$context = search::extract_context($_GET["query"], $pagesource);
				$context = search::highlight_context($_GET["query"], $context);
				/*if(strlen($context) == 0)
				{
					$context = search::strip_markup(file_get_contents("$env->page.md", null, null, null, $settings->search_characters_context * 2));
					if($pageindex->{$env->page}->size > $settings->search_characters_context * 2)
						$context .= "...";
				}*/
				
				
				// We add 1 to $i here to convert it from an index to a result
				// number as people expect it to start from 1
				$content .= "<div class='search-result' data-result-number='" . ($i + 1) . "' data-rank='" . $result["rank"] . "'>\n";
				$content .= "	<h2><a href='$link'>" . $result["pagename"] . "</a></h2>\n";
				$content .= "	<p>$context</p>\n";
				$content .= "</div>\n";
				
				$i++;
			}
			
			$content .= "</section>\n";
			
			exit(page_renderer::render($title, $content));
			
			//header("content-type: text/plain");
			//var_dump($results);
		});
	}
]);

class search
{
	// Words that we should exclude from the inverted index.
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
		"everywhere", "except", "few", "fifteen", "fify", "fill", "find",
		"fire", "first", "five", "for", "former", "formerly", "forty", "found",
		"four", "from", "front", "full", "further", "get", "give", "go", "had",
		"has", "hasnt", "have", "he", "hence", "her", "here", "hereafter",
		"hereby", "herein", "hereupon", "hers", "herself", "him", "himself",
		"his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed",
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
	
	public static function index($source)
	{
		$source = html_entity_decode($source, ENT_QUOTES);
		$source_length = strlen($source);
		
		$index = [];
		
		$terms = self::tokenize($source);
		$i = 0;
		foreach($terms as $term)
		{
			$nterm = $term;
			
			// Skip over stop words (see https://en.wikipedia.org/wiki/Stop_words)
			if(in_array($nterm, self::$stop_words)) continue;
			
			if(!isset($index[$nterm]))
			{
				$index[$nterm] = [ "freq" => 0, "offsets" => [] ];
			}
			
			$index[$nterm]["freq"]++;
			$index[$nterm]["offsets"][] = $i;
			
			$i++;
		}
		
		return $index;
	}
	
	public static function tokenize($source)
	{
		$source = strtolower($source);
		return preg_split("/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))|\|/", $source, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	public static function strip_markup($source)
	{
		return str_replace([ "[", "]", "\"", "*", "_", " - ", "`" ], "", $source);
	}
	
	public static function rebuild_invindex()
	{
		global $pageindex;
		
		$invindex = [];
		foreach($pageindex as $pagename => $pagedetails)
		{
			$pagesource = file_get_contents("$env->storage_prefix$pagename.md");
			$index = self::index($pagesource);
			
			self::merge_into_invindex($invindex, ids::getid($pagename), $index);
		}
		
		self::save_invindex("invindex.json", $invindex);
	}
	
	/*
	 * @summary Sorts an index alphabetically. Will also sort an inverted index.
	 * 			This allows us to do a binary search instead of a regular
	 * 			sequential search.
	 */
	public static function sort_index(&$index)
	{
		ksort($index, SORT_NATURAL);
	}
	
	/*
	 * @summary Compares two *regular* indexes to find the differences between them.
	 * 
	 * @param {array} $indexa - The old index.
	 * @param {array} $indexb - The new index.
	 * @param {array} $changed - An array to be filled with the nterms of all
	 * 							 the changed entries.
	 * @param {array} $removed - An array to be filled with the nterms of all
	 * 							 the removed entries.
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
			if(!isset($oldindex[$nterm]) or // If this world is new
			   $newindex[$nterm] !== $oldindex[$nterm]) // If this word has changed
				$changed[$nterm] = $newindex[$nterm];
		}
	}
	
	/*
	 * @summary Reads in and parses an inverted index.
	 */
	// Todo remove this function and make everything streamable
	public static function load_invindex($invindex_filename) {
		$invindex = json_decode(file_get_contents($invindex_filename), true);
		return $invindex;
	}
	
	/*
	 * @summary Merge an index into an inverted index.
	 */
	public static function merge_into_invindex(&$invindex, $pageid, &$index, &$removals = [])
	{
		// Remove all the subentries that were removed since last time
		foreach($removals as $nterm)
		{
			unset($invindex[$nterm][$pageid]);
		}
		
		// Merge all the new / changed index entries into the inverted index
		foreach($index as $nterm => $newentry)
		{
			// If the nterm isn't in the inverted index, then create a space for it
			if(!isset($invindex[$nterm])) $invindex[$nterm] = [];
			$invindex[$nterm][$pageid] = $newentry;
			
			// Sort the page entries for this word by frequency
			uasort($invindex[$nterm], function($a, $b) {
				if($a["freq"] == $b["freq"]) return 0;
				return ($a["freq"] < $b["freq"]) ? +1 : -1;
			});
		}
		
		// Sort the inverted index by rank
		uasort($invindex, function($a, $b) {
			$ac = count($a); $bc = count($b);
			if($ac == $bc) return 0;
			return ($ac < $bc) ? +1 : -1;
		});
	}
	
	public static function save_invindex($filename, &$invindex)
	{
		file_put_contents($filename, json_encode($invindex));
	}
	
	public static function query_invindex($query, &$invindex)
	{
		global $settings, $pageindex;
		
		$query_terms = self::tokenize($query);
		$matching_pages = [];
		
		
		// Loop over each term in the query and find the matching page entries
		for($i = 0; $i < count($query_terms); $i++)
		{
			$qterm = $query_terms[$i];
			
			// Only search the inverted index if it actually exists there
			if(isset($invindex[$qterm]))
			{
				// Loop over each page in the inverted index entry
				foreach($invindex[$qterm] as $pageid => $page_entry)
				{
					// Create an entry in the matching pages array if it doesn't exist
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					$matching_pages[$pageid]["nterms"][$qterm] = $page_entry;
				}
			}
			
			
			// Loop over the pageindex and search the titles / tags
			foreach ($pageindex as $pagename => $pagedata)
			{
				// Get the current page's id
				$pageid = ids::getid($pagename);
				// Consider matches in the page title
				if(stripos($pagename, $qterm) !== false)
				{
					// We found the qterm in the title
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for page title matches if it doesn't exist already
					if(!isset($matching_pages[$pageid]["title-matches"]))
						$matching_pages[$pageid]["title-matches"] = 0;
					
					$matching_pages[$pageid]["title-matches"] += count(mb_stripos_all($pagename, $qterm));
				}
				
				// Consider matches in the page's tags
				if(isset($pagedata->tags) and // If this page has tags
				   stripos(implode(" ", $pagedata->tags), $qterm) !== false) // And we found the qterm in the tags
				{
					if(!isset($matching_pages[$pageid]))
						$matching_pages[$pageid] = [ "nterms" => [] ];
					
					// Set up a counter for tag match if there isn't one already
					if(!isset($matching_pages[$pageid]["tag-matches"]))
						$matching_pages[$pageid]["tag-matches"] = 0;
					$matching_pages[$pageid]["tag-matches"] += count(mb_stripos_all(implode(" ", $pagedata->tags), $qterm));
				}
			}
		}
		
		
		foreach($matching_pages as $pageid => &$pagedata)
		{
			$pagedata["pagename"] = ids::getpagename($pageid);
			$pagedata["rank"] = 0;
			
			foreach($pagedata["nterms"] as $pterm => $entry)
			{
				$pagedata["rank"] += $entry["freq"];
				
				// todo rank by context here
			}
			
			// Consider matches in the title / tags
			if(isset($pagedata["title-matches"]))
				$pagedata["rank"] += $pagedata["title-matches"] * $settings->search_title_matches_weighting;
			if(isset($pagedata["tag-matches"]))
				$pagedata["rank"] += $pagedata["tag-matches"] * $settings->search_tags_matches_weighting;
			
			// todo remove items if the rank is below a threshold
		}
		
		// todo sort by rank here
		uasort($matching_pages, function($a, $b) {
			if($a["rank"] == $b["rank"]) return 0;
			return ($a["rank"] < $b["rank"]) ? +1 : -1;
		});
		
		return $matching_pages;
	}
	
	public static function extract_context($query, $source)
	{
		global $settings;
		
		$nterms = self::tokenize($query);
		$matches = [];
		// Loop over each nterm and find it in the source
		foreach($nterms as $nterm)
		{
			$all_offsets = mb_stripos_all($source, $nterm);
			// Skip over adding matches if there aren't any
			if($all_offsets === false)
				continue;
			foreach($all_offsets as $offset)
			{
				$matches[] = [ $nterm, $offset ];
			}
		}
		
		usort($matches, function($a, $b) {
			if($a[1] == $b[1]) return 0;
			return ($a[1] < $b[1]) ? +1 : -1;
		});
		
		$contexts = [];
		$basepos = 0;
		$matches_count = count($matches);
		while($basepos < $matches_count)
		{
			// Store the next match along - all others will be relative to that
			// one
			$group = [$matches[$basepos]];
			
			// Start scanning at the next one along - we always store the first match
			$scanpos = $basepos + 1;
			$distance = 0;
			
			while(true)
			{
				// Break out if we reach the end
				if($scanpos >= $matches_count) break;
				
				// Find the distance between the current one and the last one
				$distance = $matches[$scanpos][1] - $matches[$scanpos - 1][1];
				
				// Store it if the distance is below the threshold
				if($distance < $settings->search_characters_context)
					$group[] = $matches[$scanpos];
				else
					break;
				
				$scanpos++;
			}
			
			$context_start = $group[0][1] - $settings->search_characters_context;
			$context_end = $group[count($group) - 1][1] + $settings->search_characters_context;
			
			$context = substr($source, $context_start, $context_end - $context_start);
			
			// Strip the markdown from the context - it's most likely going to
			// be broken anyway.
			$context = self::strip_markup($context);
			
			$contexts[] = $context;
			
			$basepos = $scanpos + 1;
		}
		
		return implode(" ... ", $contexts);
	}
	
	public static function highlight_context($query, $context)
	{
		$qterms = self::tokenize($query);
		
		foreach($qterms as $qterm)
		{
			// From http://stackoverflow.com/a/2483859/1460422
			$context = preg_replace("/" . preg_quote($qterm) . "/i", "<strong>$0</strong>", $context);
		}
		
		return $context;
	}
}




register_module([
	"name" => "Uploader",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File:' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		add_action("upload", function() {
			global $settings, $env, $pageindex, $paths;
			
			switch($_SERVER["REQUEST_METHOD"])
			{
				case "GET":
					// Send upload page
					
					if(!$settings->upload_enabled)
						exit(page_renderer::render("Upload Disabled - $setting->sitename", "<p>You can't upload anything at the moment because $settings->sitename has uploads disabled. Try contacting " . $settings->admindetails["name"] . ", your site Administrator. <a href='javascript:history.back();'>Go back</a>.</p>"));
					if(!$env->is_logged_in)
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You are not currently logged in, so you can't upload anything.</p>
		<p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first.</p>"));
					
					exit(page_renderer::render("Upload - $settings->sitename", "<p>Select an image below, and then type a name for it in the box. This server currently supports uploads up to " . human_filesize(get_max_upload_size()) . " in size.</p>
		<p>$settings->sitename currently supports uploading of the following file types: " . implode(", ", $settings->upload_allowed_file_types) . ".</p>
		<form method='post' action='?action=upload' enctype='multipart/form-data'>
			<label for='file'>Select a file to upload.</label>
			<input type='file' name='file' />
			<br />
			<label for='name'>Name:</label>
			<input type='text' name='name'  />
			<br />
			<label for='description'>Description:</label>
			<textarea name='description'></textarea>
			<br />
			<input type='submit' value='Upload' />
		</form>"));
					
					break;
				
				case "POST":
					// Recieve file
					
					// Make sure uploads are enabled
					if(!$settings->upload_enabled)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(412);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because uploads are currently disabled on $settings->sitename. <a href='index.php'>Go back to the main page</a>.</p>"));
					}
					
					// Make sure that the user is logged in
					if(!$env->is_logged_in)
					{
						unlink($_FILES["file"]["tmp_name"]);
						http_response_code(401);
						exit(page_renderer::render("Upload failed - $settings->sitename", "<p>Your upload couldn't be processed because you are not logged in.</p><p>Try <a href='?action=login&returnto=" . rawurlencode("?action=upload") . "'>logging in</a> first."));
					}
					
					// Calculate the target ename, removing any characters we
					// are unsure about.
					$target_name = makepathsafe($_POST["name"]);
					$temp_filename = $_FILES["file"]["tmp_name"];
					
					$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
					$mime_type = finfo_file($mimechecker, $temp_filename);
					finfo_close($mimechecker);
					
					// Perform appropriate checks based on the *real* filetype
					switch(substr($mime_type, 0, strpos($mime_type, "/")))
					{
						case "image":
							$extra_data = [];
							$imagesize = getimagesize($temp_filename, $extra_data);
							// Make sure that the image size is defined
							if(!is_int($imagesize[0]) or !is_int($imagesize[1]))
							{
								http_response_code(415);
								exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file that you uploaded doesn't appear to be an image. $settings->sitename currently only supports uploading images (videos coming soon). <a href='?action=upload'>Go back to try again</a>.</p>"));
							}
							
							break;
						
						case "video":
							http_response_code(501);
							exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You uploaded a video, but $settings->sitename doesn't support them yet. Please try again later.</p>"));
						
						default:
							http_response_code(415);
							exit(page_renderer::render("Upload Error - $settings->sitename", "<p>You uploaded an unnknown file type which couldn't be processed. $settings->sitename thinks that the file you uploaded was a(n) '$mime_type', which isn't supported.</p>"));
					}
					
					$file_extension = system_mime_type_extension($mime_type);
					
					$new_filename = "$paths->upload_file_prefix$target_name.$file_extension";
					$new_description_filename = "$new_filename.md";
					
					if(isset($pageindex->$new_filename))
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>A page or file has already been uploaded with the name '$new_filename'. Try deleting it first. If you do not have permission to delete things, try contacting one of the moderators.</p>"));
					
					if(!file_exists("Files"))
						mkdir("Files", 0664);
					
					if(!move_uploaded_file($temp_filename, $env->storage_prefix . $new_filename))
					{
						http_response_code(409);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded was valid, but $settings->sitename couldn't verify that it was tampered with during the upload process. This probably means that $settings->sitename has been attacked. Please contact " . $settings->admindetails . ", your $settings->sitename Administrator.</p>"));
					}
					
					$description = $_POST["description"];
					
					// Escape the raw html in the provided description if the setting is enabled
					if($settings->clean_raw_html)
						$description = htmlentities($description, ENT_QUOTES);
					
					file_put_contents($env->storage_prefix . $new_description_filename, $description);
					
					// Construct a new entry for the pageindex
					$entry = new stdClass();
					// Point to the description's filepath since this property
					// should point to a markdown file
					$entry->filename = $new_description_filename; 
					$entry->size = strlen($description);
					$entry->lastmodified = time();
					$entry->lasteditor = $env->user;
					$entry->uploadedfile = true;
					$entry->uploadedfilepath = $new_filename;
					$entry->uploadedfilemime = $mime_type;
					// Add the new entry to the pageindex
					// Assign the new entry to the image's filepath as that
					// should be the page name.
					$pageindex->$new_filename = $entry;
					
					// Save the pageindex
					file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
					
					header("location: ?action=view&page=$new_filename&upload=success");
					
					break;
			}
		});
		add_action("preview", function() {
			global $settings, $env, $pageindex;
			
			$filepath = $env->storage_prefix . $pageindex->{$env->page}->uploadedfilepath;
			$mime_type = $pageindex->{$env->page}->uploadedfilemime;
			
			if(isset($_GET["size"]) and $_GET["size"] == "original")
			{
				// Get the file size
				$filesize = filesize($filepath);
				
				// Send some headers
				header("content-length: $filesize");
				header("content-type: $mime_type");
				
				// Open the file and send it to the user
				$handle = fopen($filepath, "rb");
				fpassthru($handle);
				fclose($handle);
				exit();
			}
			
			// Determine the target size of the image
			$target_size = 512;
			if(isset($_GET["size"]))
				$target_size = intval($_GET["size"]);
			if($target_size < $settings->min_preview_size)
				$target_size = $settings->min_preview_size;
			if($target_size > $settings->max_preview_size)
				$target_size = $settings->max_preview_size;
			
			// Determine the output file type
			$output_mime = $settings->preview_file_type;
			if(isset($_GET["type"]) and in_array($_GET["type"], [ "image/png", "image/jpeg", "image/webp" ]))
				$output_mime = $_GET["type"];
			
			switch(substr($mime_type, 0, strpos($mime_type, "/")))
			{
				case "image":
					$image = false;
					switch($mime_type)
					{
						case "image/jpeg":
							$image = imagecreatefromjpeg($filepath);
							break;
						case "image/gif":
							$image = imagecreatefromgif($filepath);
							break;
						case "image/png":
							$image = imagecreatefrompng($filepath);
							break;
						case "image/webp":
							$image = imagecreatefromwebp($filepath);
							break;
						default:
							http_response_code(415);
							$image = errorimage("Unsupported image type.");
							break;
					}
					
					$raw_width = imagesx($image);
					$raw_height = imagesy($image);
					
					$preview_image = resize_image($image, $target_size);
					header("content-type: $output_mime");
					switch($output_mime)
					{
						case "image/jpeg":
							imagejpeg($preview_image);
							break;
						case "image/png":
							imagepng($preview_image);
							break;
						default:
						case "image/webp":
							imagewebp($preview_image);
							break;
					}
					imagedestroy($preview_image);
					break;
				
				default:
					http_response_code(501);
					exit("Unrecognised file type.");
			}
		});
		
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $pageindex, $env, $settings;
			// Todo add the preview to the top of the page here, but only if the current action is view and we are on a page that is a file
			if(isset($pageindex->{$env->page}->uploadedfile) and $pageindex->{$env->page}->uploadedfile == true)
			{
				// We are looking at a page that is paired with an uploaded file
				$filepath = $pageindex->{$env->page}->uploadedfilepath;
				$mime_type = $pageindex->{$env->page}->uploadedfilemime;
				$dimensions = getimagesize($env->storage_prefix . $filepath);
				
				$preview_sizes = [ 256, 512, 768, 1024, 1536 ];
				$preview_html = "<figure class='preview'>
			<img src='?action=preview&size=$settings->default_preview_size&page=" . rawurlencode($env->page) . "' />
			<nav class='image-controls'>
				<ul><li><a href='" . ($env->storage_prefix == "./" ? $filepath : "?action=preview&size=original&page=" . rawurlencode($env->page)) . "'>&#x01f304; Original image</a></li>
				<li>Other Sizes: ";
				foreach($preview_sizes as $size)
					$preview_html .= "<a href='?action=preview&size='$size>$size" . "px</a> ";
				$preview_html .= "</li></ul></nav>
			</figure>
			<h2>File Information</h2>
			<table><tr><th>Name</th><td>" . str_replace("File/", "", $filepath) . "</td>
			<tr><th>Type</th><td>$mime_type</td></tr>
			<tr><th>Size</th><td>" . human_filesize(filesize($filepath)) . "</td></tr>
			<tr><th>Original dimensions</th><td>$dimensions[0] x $dimensions[1]</td></tr>
			<tr><th>Uploaded by</th><td>" . $pageindex->{$env->page}->lasteditor . "</td></tr></table>
			<h2>Description</h2>";
				
				$parts["{content}"] = str_replace("</h1>", "</h1>\n$preview_html", $parts["{content}"]);
			}
		});
	}
]);

//// Pair of functions to calculate the actual maximum upload size supported by the server
//// Lifted from Drupal by @meustrus from  Stackoverflow. Link to answer:
//// http://stackoverflow.com/a/25370978/1460422
// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function get_max_upload_size()
{
	static $max_size = -1;
	if ($max_size < 0) {
		// Start with post_max_size.
		$max_size = parse_size(ini_get('post_max_size'));
		// If upload_max_size is less, then reduce. Except if upload_max_size is
		// zero, which indicates no limit.
		$upload_max = parse_size(ini_get('upload_max_filesize'));
		if ($upload_max > 0 && $upload_max < $max_size) {
			$max_size = $upload_max;
		}
	}
	return $max_size;
}

function parse_size($size) {
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
	$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
	if ($unit) {
		// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	} else {
		return round($size);
	}
}

function errorimage($text)
{
	$width = 640;
	$height = 480;
	$image = imagecreatetruecolor($width, $height);
	imagefill($image, 0, 0, imagecolorallocate($image, 238, 232, 242)); // Set the background to #eee8f2
	$fontwidth = imagefontwidth(3);
	imagetext($image, 3,
		($width / 2) - (($fontwidth * strlen($text)) / 2),
		($height / 2) - (imagefontheight(3) / 2),
		$text,
		imagecolorallocate($image, 17, 17, 17) // #111111
	);
	
	return $image;
}

function resize_image($image, $size)
{
	$cur_width = imagesx($image);
	$cur_height = imagesy($image);
	
	if($cur_width < $size and $cur_height < $size)
		return $image;
	
	$width_ratio = $size / $cur_width;
	$height_ratio = $size / $cur_height;
	$ratio = min($width_ratio, $height_ratio);
	
	$new_height = floor($cur_height * $ratio);
	$new_width = floor($cur_width * $ratio);
	
	header("x-resize-to: $new_width x $new_height\n");
	
	return imagescale($image, $new_width, $new_height);
}




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
			
			//// Credits html renderer ////
			$credits_html = "<ul>\n";
			foreach($credits as $thing => $author_details)
			{
				$credits_html .= "	<li>";
				$credits_html .= "<a href='" . $author_details["thing_url"] . "'>$thing</a> by ";
				$credits_html .= "<a href='" . $author_details["author_url"] . "'>" . $author_details["author"] . "</a>";
				$credits_html .= "</li>\n";
			}
			$credits_html .= "</ul>";
			///////////////////////////////
			
			//// Module html renderer ////
			$modules_html = "<table>
	<tr>
		<th>Name</th>
		<th>Version</th>
		<th>Author</th>
		<th>Description</th>
	</tr>";
			foreach($modules as $module)
			{
				$modules_html .= "	<tr>
		<td title='" . $module["id"] . "'>" . $module["name"] . "</td>
		<td>" . $module["version"] . "</td>
		<td>" . $module["author"] . "</td>
		<td>" . $module["description"] . "</td>
	</tr>\n";
			}
			$modules_html .= "</table>";
			//////////////////////////////
			
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
	</table>
	<h2>Installed Modules</h2>
	$modules_html";
			exit(page_renderer::render_main($title, $content));
		});
	}
]);




register_module([
	"name" => "Page deleter",
	"version" => "0.8",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		add_action("delete", function() {
			global $pageindex, $settings, $env, $paths;
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
			// Delete the associated file if it exists
			if(!empty($pageindex->$page->uploadedfile))
			{
				unlink($env->storage_prefix . $pageindex->$page->uploadedfilepath);
			}
			unset($pageindex->$page); //delete the page from the page index
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT)); //save the new page index
			unlink("$env->storage_prefix$env->page.md"); //delete the page from the disk

			exit(page_renderer::render_main("Deleting $env->page - $settings->sitename", "<p>$env->page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
	}
]);




register_module([
	"name" => "Page editor",
	"version" => "0.12",
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
			
			$filename = "$env->storage_prefix$env->page.md";
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
			$page_tags = implode(", ", (!empty($pageindex->{$env->page}->tags)) ? $pageindex->{$env->page}->tags : []);
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save' class='editform'>
			<textarea name='content'>$pagetext</textarea>
			<input type='text' name='tags' value='$page_tags' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' />
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
			global $pageindex, $settings, $env, $save_preprocessors, $paths; 
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
				exit("$env->page is protected, and you aren't logged in as an administrator or moderator. Your edit was not saved. Redirecting in 5 seconds...");
			}
			if(!isset($_POST["content"]))
			{
				http_response_code(400);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("Bad request: No content specified.");
			}
			
			// Make sure that the directory in which the page needs to be saved exists
			if(!is_dir(dirname("$env->storage_prefix$env->page.md")))
			{
				// Recursively create the directory if needed
				mkdir(dirname("$env->storage_prefix$env->page.md"), null, true);
			}
			
			// Read in the new page content
			$pagedata = $_POST["content"];
			// Santise it if necessary
			if($settings->clean_raw_html)
				$pagedata = htmlentities($pagedata, ENT_QUOTES);
			
			// Read in the new page tags, so long as there are actually some tags to read in
			$page_tags = [];
			if(strlen(trim($_POST["tags"])) > 0)
			{
				$page_tags = explode(",", $_POST["tags"]);
				// Trim off all the whitespace
				foreach($page_tags as &$tag)
					$tag = trim($tag);
			}
			
			// Update the inverted search index
			
			// Construct an index for the old and new page content
			$oldindex = search::index(file_get_contents("$env->page.md"));
			$newindex = search::index($pagedata);
			
			// Compare the indexes of the old and new content
			$additions = [];
			$removals = [];
			search::compare_indexes($oldindex, $newindex, $additions, $removals);
			// Load in the inverted index
			$invindex = search::load_invindex("./invindex.json");
			// Merge the changes into the inverted index
			search::merge_into_invindex($invindex, ids::getid($env->page), $additions, $removals);
			// Save the inverted index back to disk
			search::save_invindex("invindex.json", $invindex);
			
			
			
			if(file_put_contents("$env->storage_prefix$env->page.md", $pagedata) !== false)
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
				$pageindex->$page->tags = $page_tags;
				
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
					file_put_contents("$env->storage_prefix$env->page.md", $pagedata);
				
				
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
				
				if(isset($_GET["newpage"]))
					http_response_code(201);
				else
					http_response_code(200);
				
//				header("content-type: text/plain");
				header("location: index.php?page=$env->page&edit_status=success&redirect=no");
				exit();
			}
			else
			{
				http_response_code(507);
				exit(page_renderer::render_main("Error saving page - $settings->sitename", "<p>$settings->sitename failed to write your changes to the server's disk. Your changes have not been saved, but you might be able to recover your edit by pressing the back button in your browser.</p>
				<p>Please tell the administrator of this wiki (" . $settings->admindetails["name"] . ") about this problem.</p>"));
			}
		});
	}
]);




register_module([
	"name" => "Export",
	"version" => "0.3",
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
				$zip->addFile("$env->storage_prefix$entry->filename", $entry->filename);
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
			
			$title = "All Pages";
			$content = "	<h1>$title on $settings->sitename</h1>";
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			
			$content .= generate_page_list(array_keys($sorted_pageindex));
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		add_action("list-tags", function() {
			global $pageindex, $settings;
			
			if(empty($_GET["tag"]))
			{
				http_response_code(301);
				header("location: ?action=list");
			}
			
			$tag = $_GET["tag"];
			
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			
			$pagelist = [];
			foreach($pageindex as $pagename => $pagedetails)
			{
				if(empty($pagedetails->tags)) continue;
				if(in_array($tag, $pagedetails->tags))
					$pagelist[] = $pagename;
			}
			
			$content = "<h1>$tag</h1>\n";
			$content .= generate_page_list($pagelist);
			
			exit(page_renderer::render("$tag - Page List - $settings->sitename", $content));
		});
	}
]);

function generate_page_list($pagelist)
{
	global $pageindex;
	
	$result = "<table>
		<tr>
			<th>Page Name</th>
			<th>Size</th>
			<th>Last Editor</th>
			<th>Last Edit Time</th>
		</tr>\n";
	foreach($pagelist as $pagename)
	{
			$result .= "\t\t<tr>
			<td><a href='index.php?page=$pagename'>$pagename</a></td>
			<td>" . human_filesize($pageindex->$pagename->size) . "</td>
			<td>" . $pageindex->$pagename->lasteditor . "</td>
			<td>" . human_time_since($pageindex->$pagename->lastmodified) . " <small>(" . date("l jS \of F Y \a\\t h:ia T", $pageindex->$pagename->lastmodified) . ")</small></td>
	
	</tr>\n";
	}
	$result .= "	</table>";
	
	return $result;
}




register_module([
	"name" => "Login",
	"version" => "0.7",
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
				if($settings->users[$user] == hash_password($pass))
				{
					$env->is_logged_in = true;
					$expiretime = time() + 60*60*24*30; //30 days from now
					$_SESSION["$settings->sessionprefix-user"] = $user;
					$_SESSION["$settings->sessionprefix-pass"] = hash_password($pass);
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

/*
 * @summary Hashes the given password according to the current settings defined
 * 			in $settings.
 * 
 * @param $pass {string} The password to hash.
 * 
 * @returns {string} The hashed password. Uses sha3 if $settings->use_sha3 is
 * 					 enabled, or sha256 otherwise.
 */
function hash_password($pass)
{
	global $settings;
	if($settings->use_sha3)
	{
		return sha3($pass, 256);
	}
	else
	{
		return hash("sha256", $pass);
	}
}




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
	"version" => "0.7",
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
			$pageindex->$new_name->filename = $new_name;
			// If this page has an associated file, then we should move that too
			if(!empty($pageindex->$new_name->uploadedfile))
			{
				// Update the filepath to point to the description and not the image
				$pageindex->$new_name->filename = $pageindex->$new_name->filename . ".md";
				// Move the file in the pageindex
				$pageindex->$new_name->uploadedfilepath = $new_name;
				// Move the file on disk
				rename($env->storage_prefix . $env->page, $env->storage_prefix . $new_name);
			}
			file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
			
			//move the page on the disk
			rename("$env->storage_prefix$env->page.md", "$env->storage_prefix$new_name.md");
			
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
			
			// Add an extra message if the requester was redirected from another page
			if(isset($_GET["redirected_from"]))
				$content .= "<p><em>Redirected from <a href='?page=" . rawurlencode($_GET["redirected_from"]) . "&redirect=no'>" . $_GET["redirected_from"] . "</a>.</em></p>";
			
			$parsing_start = microtime(true);
			
			$content .= parse_page_source(file_get_contents("$env->storage_prefix$env->page.md"));
			
			if(!empty($pageindex->$page->tags))
			{
				$content .= "<ul class='page-tags-display'>\n";
				foreach($pageindex->$page->tags as $tag)
				{
					$content .= "<li><a href='?action=list-tags&tag=$tag'>$tag</a></li>\n";
				}
				$content .= "\n</ul>\n";
			}
			/*else
			{
				$content .= "<aside class='page-tags-display'><small><em>(No tags yet! Add some by <a href='?action=edit&page=" . rawurlencode($env->page) .  "'>editing this page</a>!)</em></small></aside>\n";
			}*/
			
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
		'/(\*+)(.*?)\1/' => '<strong>\2</strong>',					// bold
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
