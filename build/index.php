<?php
$start_time = microtime(true);


/*
 * Pepperminty Wiki
 * ================
 * Inspired by Minty Wiki by am2064
	* Link: https://github.com/am2064/Minty-Wiki
 * 
 * Credits:
	* Code by @Starbeamrainbowlabs
	* Parsedown - by erusev and others on github from http://parsedown.org/
	* Mathematical Expression rendering
		* Code: @con-f-use <https://github.com/con-f-use>
		* Rendering: MathJax (https://www.mathjax.org/)
 * Bug reports:
	* #2 - Incorrect closing tag - nibreh <https://github.com/nibreh/>
	* #8 - Rogue <datalist /> tag - nibreh <https://github.com/nibreh/>
 */
// Initialises a new object to store your wiki's settings in. Please don't touch this.
$settings = new stdClass();

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////// Basic Settings ////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// The site's name. Used all over the place.
$settings->sitename = "Pepperminty Wiki";

// The name of the page that will act as the home page for the wiki. This page
// will be served if the user didn't specify a page.
$settings->defaultpage = "Main Page";

// Contact details for the site administrator. Since users can only be added by
// editing this file, people will need a contact address to use to ask for an
// account.
$settings->admindetails = [
	"name" => "Administrator",
	"email" => "admin@localhost"
];


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Appearance //////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// A url that points to the favicon you want to use for your wiki. By default
// this is set to a data: url of a Peppermint.
// Default favicon credit: Peppermint by bluefrog23
//	Link: https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23
$settings->favicon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAB3VBMVEXhERHbKCjeVVXjb2/kR0fhKirdHBziDg6qAADaHh7qLy/pdXXUNzfMAADYPj7ZPDzUNzfbHx/fERHpamrqMTHgExPdHx/bLCzhLS3fVFTjT0/ibm7kRkbiLi7aKirdISHeFBTqNDTpeHjgERHYJCTVODjYQkLaPj6/AADVOTnpbW3cIyPdFRXcJCThMjLiTU3ibW3fVVXaKyvcERH4ODj+8fH/////fHz+Fxf4KSn0UFD/CAj/AAD/Xl7/wMD/EhL//v70xMT/+Pj/iYn/HBz/g4P/IyP/Kyv/7Oz0QUH/9PT/+vr/ior/Dg7/vr7/aGj/QED/bGz/AQH/ERH/Jib/R0f/goL/0dH/qan/YWH/7e3/Cwv4R0f/MTH/enr/vLz/u7v/cHD/oKD/n5//aWn+9/f/k5P/0tL/trb/QUH/cXH/dHT/wsL/DQ3/p6f/DAz/1dX/XV3/kpL/i4v/Vlb/2Nj/9/f/pKT+7Oz/V1f/iIj/jIz/r6//Zmb/lZX/j4//T0//Dw/4MzP/GBj/+fn/o6P/TEz/xMT/b2//Tk7/OTn/HR3/hIT/ODj/Y2P/CQn/ZGT/6Oj0UlL/Gxv//f3/Bwf/YmL/6+v0w8P/Cgr/tbX0QkL+9fX4Pz/qNzd0dFHLAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfeCxINNSdmw510AAAA5ElEQVQYGQXBzSuDAQCA8eexKXOwmSZepa1JiPJxsJOrCwcnuchBjg4O/gr7D9zk4uAgJzvuMgcTpYxaUZvSm5mUj7TX7ycAqvoLIJBwStVbP0Hom1Z/ejoxrbaR1Jz6nWinbKWttGRgMSSjanPktRY6mB9WtRNTn7Ilh7LxnNpKq2/x5LnBitfz+hx0qxUaxhZ6vwqq9bx6f2XXvuUl9SVQS38NR7cvln3v15tZ9bQpuWDtZN3Lgh5DWJex3Y+z1KrVhw21+CiM74WZo83DiXq0dVBDYNJkFEU7WrwDAZhRtQrwDzwKQbT6GboLAAAAAElFTkSuQmCC";

// A url that points to the site's logo. Leave blank to disable. When enabled
// the logo will be inserted next to the site name on every page.
$settings->logo_url = "//starbeamrainbowlabs.com/images/logos/peppermint.png";

// The side of the site name at which the logo should be placed.
$settings->logo_position = "left";

// Whether to show a list of subpages at the bottom of the page.
$settings->show_subpages = true;

// The depth to which we should display when listing subpages at the bottom of
// the page.
$settings->subpages_display_depth = 3;

// A message that will appear at the bottom of every page. May contain HTML.
$settings->footer_message = "All content is under <a href='?page=License' target='_blank'>this license</a>. Please make sure that you read and understand the license, especially if you are thinking about copying some (or all) of this site's content, as it may restrict you from doing so.";

// A message that will appear just before the submit button on the editing
// page. May contain HTML.
$settings->editing_message = "<a href='?action=help#20-parser-default' target='_blank'>Formatting help</a> (<a href='https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet' target='_blank'>Markdown Cheatsheet</a>)<br />
By submitting your edit or uploading your file, you are agreeing to release your changes under <a href='?action=view&page=License' target='_blank'>this license</a>. Also note that if you don't want your work to be edited by other users of this site, please don't submit it here!";

// The string that is prepended before an admin's name on the nav bar. Defaults
// to a diamond shape (&#9670;).
$settings->admindisplaychar = "&#9670;";

// The string that is prepended a page's name in the page title if it is
// protected. Defaults to a lock symbol. (&#128274;)
$settings->protectedpagechar = "&#128274;";


///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////// Editing ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

// Whether editing is enabled.
$settings->editing = true;

// Whether users who aren't logged in are allowed to edit your wiki.
$settings->anonedits = false;

// The maximum page size in characters.
$settings->maxpagesize = 135000;

// The parser to use when rendering pages. Defaults to a modified version of
// slimdown, originally written by Johnny Broadway <johnny@johnnybroadway.com>.
$settings->parser = "parsedown";

// Whether page sources should be cleaned of HTML before rendering. It is
// STRONGLY recommended that you keep this option turned on.
$settings->clean_raw_html = true;

// Whether to enable client side rendering of methematical expressions.
// Math expressions should be enclosed inside of dollar signs ($).
// Turn off if you don't use it.
$settings->enable_math_rendering = true;


///////////////////////////////////////////////////////////////////////////////
///////////////////////////// Access and Security /////////////////////////////
///////////////////////////////////////////////////////////////////////////////

// An array of usernames and passwords - passwords should be hashed with
// sha256.
$settings->users = [
	"admin" => "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8", //password
	"user" => "873ac9ffea4dd04fa719e8920cd6938f0c23cd678af330939cff53c3d2855f34" //cheese
];

// An array of usernames that are administrators. Administrators can delete and
// move pages.
$settings->admins = [ "admin" ];

// Whether to use the new sha3 hashing algorithm for passwords etc.
$settings->use_sha3 = false;

// Whether to require that users login before they do anything else.
$settings->require_login_view = false;

// The directory in which to store all files, except this main index.php.
$settings->data_storage_dir = ".";

// The secret key used to perform 'dangerous' actions, like updating the wiki.
// It is strongly advised that you change this!
$settings->sitesecret = "ed420502615bac9037f8f12abd4c9f02";

// The amount of time, in seconds, that pages should be blocked from being
// indexed by search engines after their last edit. Aka delayed indexing.
$settings->delayed_indexing_time = 0;


////////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Navigation //////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// An array of links and display text to display at the top of the site.
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
	//"divider",
	[ "All&nbsp;Pages", "index.php?action=list" ],
	"menu"
];
// An array of additional links in the above format that will be shown under
// "More" subsection.
$settings->nav_links_extra = [
	[ "&#x1f3ab; All&nbsp;Tags", "index.php?action=list-tags" ],
	[ "Recent changes", "?action=recent-changes" ],
	[ "&#x1f845; Upload", "index.php?action=upload" ],
	[ "&#x2327; $settings->admindisplaychar" . "Delete", "index.php?action=delete&page={page}" ],
	[ "&#x2398; $settings->admindisplaychar" . "Move", "index.php?action=move&page={page}" ],
	[ "&#x1f510; $settings->admindisplaychar" . "Toggle Protection", "index.php?action=protect&page={page}" ]
];

// An array of links in the above format that will be shown at the bottom of
// the page.
$settings->nav_links_bottom = [
	[ "&#x1f5b6; Printable version", "index.php?action=view&printable=yes&page={page}" ],
	[ "Credits", "index.php?action=credits" ],
	[ "Help", "index.php?action=help" ]
];


///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////// Uploads ///////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

// Whether to allow image uploads to the server.
$settings->upload_enabled = true;

// An array of mime types that are allowed to be uploaded.
$settings->upload_allowed_file_types = [
	"image/jpeg",
	"image/png",
	"image/gif",
	"image/webp",
	"image/svg+xml",
	"video/mp4",
	"video/webm",
	"audio/mp4",
	"audio/mpeg"
];

// The default file type for previews.
$settings->preview_file_type = "image/png";

// The default size of preview images.
$settings->default_preview_size = 640;

// The location of a file that maps mime types onto file extensions and vice
// versa. Used to generate the file extension for an uploaded file. See the
// configuration guide for windows instructions.
$settings->mime_extension_mappings_location = "/etc/mime.types";

// Override mappings to convert mime types into the appropriate file extension.
// Used to override the above file if it assigns weird extensions
// to any mime types.
$settings->mime_mappings_overrides = [
	"text/plain" => "txt",
	"audio/mpeg" => "mp3"
];

// The minimum and maximum allowed sizes of generated preview images in pixels.
$settings->min_preview_size = 1;
$settings->max_preview_size = 2048;


////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////// Search ////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// The number of characters that should be displayed either side of a matching
// term in the context below each search result.
$settings->search_characters_context = 200;

// The weighting to give to search term matches found in a page's title.
$settings->search_title_matches_weighting = 10;

// The weighting to give to search term matches found in a page's tags.
$settings->search_tags_matches_weighting = 3;


////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////// Advanced ///////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// The default action. This action will be performed if no other action is
// specified. It is recommended you set this to "view" - that way the user
// automatically views the default page (see above).
$settings->defaultaction = "view";

// The url from which to fetch updates. Defaults to the master (development)
// branch.
// MAKE SURE THAT THIS POINTS TO A *HTTPS* URL, OTHERWISE SOMEONE COULD INJECT
// A VIRUS INTO YOUR WIKI
$settings->updateurl = "https://raw.githubusercontent.com/sbrl/pepperminty-wiki/master/index.php";

// Whether to optimise all webpages generated.
$settings->optimize_pages = true;

///////////////////////////////////////////////////////////////////////////////
//////////////////////////////// Other Modules ////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

// The maximum number of recent changes to display on the recent changes page.
$settings->max_recent_changes = 512;

// Whether to only allow adminstrators to export the your wiki as a zip using
// the page-export module.
$settings->export_allow_only_admins = false;

// The prefix that should be used in the names of the session variables. See
// the readme for more information.
$settings->sessionprefix = preg_replace("/[^0-9a-z]/i", "", strtolower($settings->sitename));


///////////////////////////////////////////////////////////////////////////////
//////////////////////////////////// Theme ////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

// A string of css to include. Will be included in the <head> of every page
// inside a <style> tag. This may also be a url - urls will be referenced via a
// <link rel='stylesheet' /> tag.
$settings->css = <<<THEMECSS
body { margin: 2rem 0; background: #eee8f2; line-height: 1.45em; color: #111111; font-family: sans-serif; }

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
.nav-more-menu span { min-width: 10rem; }

.inflexible { flex: none; }
.off-screen { position: absolute; top: -1000px; left: -1000px;}

input[type=search] { width: 14rem; padding: 0.3rem 0.4rem; font-size: 1rem; color: white; background: rgba(255, 255, 255, 0.4); border: 0; border-radius: 0.3rem; }
input[type=search]::-webkit-input-placeholder { color : rgba(255, 255, 255, 0.75); }
input[type=button], input[type=submit] { cursor: pointer; }

.sidebar { position: relative; z-index: 100; margin-top: 0.6rem; padding: 1rem 3rem 2rem 0.4rem; background: #9e7eb4; box-shadow: inset -0.6rem 0 0.8rem -0.5rem rgba(50, 50, 50, 0.5); }
.sidebar a { color: #ffa74d; }

.sidebar ul { position: relative; margin: 0.3rem 0.3rem 0.3rem 1rem; padding: 0.3rem 0.3rem 0.3rem 1rem; list-style-type: none; }
.sidebar li { position: relative; margin: 0.3rem; padding: 0.3rem; }

.sidebar ul:before { content: ""; position: absolute; top: 0; left: 0; height: 100%; border-left: 2px dashed rgba(50, 50, 50, 0.4); }
.sidebar li:before { content: ""; position: absolute; width: 1rem; top: 0.8rem; left: -1.2rem; border-bottom: 2px dashed rgba(50, 50, 50, 0.4); }

.preview { text-align: center; }
.preview img, .preview video, .preview audio { --checkerboard-bg: rgba(200, 200, 200, 0.2); max-width: 100%; background-color: #eee; background-image: linear-gradient(45deg, var(--checkerboard-bg) 25%, transparent 25%, transparent 75%, var(--checkerboard-bg) 75%, var(--checkerboard-bg)), linear-gradient(45deg, var(--checkerboard-bg) 25%, transparent 25%, transparent 75%, var(--checkerboard-bg) 75%, var(--checkerboard-bg)); background-size:2em 2em; background-position:0 0, 1em 1em; }
.image-controls ul { list-style-type: none; margin: 5px; padding: 5px; }
.image-controls li { display: inline-block; margin: 5px; padding: 5px; }
.link-display { margin-left: 0.5rem; }

figure:not(.preview) { display: inline-block; }
figure:not(.preview) > :first-child { display: block; }
figcaption { text-align: center; }

.printable { padding: 2rem; }

h1 { text-align: center; }
.sitename { margin-top: 5rem; margin-bottom: 3rem; font-size: 2.5rem; }
.logo { max-width: 4rem; max-height: 4rem; vertical-align: middle; }
.logo.small { max-width: 2rem; max-height: 2rem; }
main:not(.printable) { padding: 2rem 2rem 0.5rem 2rem; background: #faf8fb; box-shadow: 0 0.1rem 1rem 0.3rem rgba(50, 50, 50, 0.5); }

blockquote { padding-left: 1em; border-left: 0.2em solid #442772; border-radius: 0.2rem; }

a.redlink:link { color: rgb(230, 7, 7); }
a.redlink:visited { color: rgb(130, 15, 15); #8b1a1a }

.search-result { position: relative; }
.search-result::before { content: attr(data-result-number); position: relative; top: 3.2rem; color: rgba(33, 33, 33, 0.3); font-size: 2rem; }
.search-result::after { content: "Rank: " attr(data-rank); position: absolute; top: 3.8rem; right: 0.7rem; color: rgba(50, 50, 50, 0.3); }
.search-result > h2 { margin-left: 2rem; }

label:not(.link-display-label) { display: inline-block; min-width: 7rem; }
input[type=text]:not(.link-display), input[type=password], textarea { margin: 0.5rem 0; }
input[type=text], input[type=password], textarea, #search-box { padding: 0.5rem 0.8rem; background: #d5cbf9; border: 0; border-radius: 0.3rem; font-size: 1rem; color: #442772; }
textarea { min-height: 35rem; line-height: 1.3em; font-size: 1.25rem; }
textarea, textarea ~ input[type=submit], #search-box { width: calc(100% - 0.3rem); box-sizing: border-box; }
textarea ~ input[type=submit] { margin: 0.5rem 0; padding: 0.5rem; font-weight: bolder; }
.editform input[type=text] { width: calc(100% - 0.3rem); box-sizing: border-box; }

.file-gallery { margin: 0.5em; padding: 0.5em; list-style-type: none; }
.file-gallery > li { display: inline-block; min-width: attr(data-gallery-width); padding: 1em; text-align: center; }
.file-gallery > li img, .file-gallery > li video, .file-gallery > li audio { display: block; margin: 0 auto; background-color: white; }

.page-tags-display { margin: 0.5rem 0 0 0; padding: 0; list-style-type: none; }
.page-tags-display li { display: inline-block; margin: 0.5rem; padding: 0.5rem; background: #D2C3DD; white-space: nowrap; }
.page-tags-display li a { color: #FB701A; text-decoration: none; }
.page-tags-display li::before { content: "\\A"; position: relative; top: 0.03rem; left: -0.9rem; width: 0; height: 0; border-top: 0.6rem solid transparent; border-bottom: 0.6rem solid transparent; border-right: 0.5rem solid #D2C3DD; }

.page-list { list-style-type: none; margin: 0.3rem; padding: 0.3rem; }
.page-list li:not(.header) { margin: 0.3rem; padding: 0.3rem; }
.page-list li .size { margin-left: 0.7rem; color: rgba(30, 30, 30, 0.5); }
.page-list li .editor { display: inline-block; margin: 0 0.5rem; }
.page-list li .tags { margin: 0 1rem; }
.tag-list { list-style-type: none; margin: 0.5rem; padding: 0.5rem; }
.tag-list li { display: inline-block; margin: 1rem; }
.mini-tag { background: #d2c3dd; padding: 0.2rem 0.4rem; color: #fb701a; text-decoration: none; }

.help-section-header::after { content: "#" attr(id); float: right; color: rgba(0, 0, 0, 0.4); font-size: 0.8rem; font-weight: normal; }

.cursor-query { cursor: help; }

summary { cursor: pointer; }

.larger { color: rgb(9, 180, 0); }
.smaller, .deletion { color: rgb(207, 28, 17); }
.nochange { color: rgb(132, 123, 199); font-style: italic; }
.significant { font-weight: bolder; font-size: 1.1rem; }
.deletion, .deletion > .editor { text-decoration: line-through; }

.highlighted-diff { white-space: pre-wrap; }
.diff-added { background-color: rgba(31, 171, 36, 0.6); color: rgba(23, 125, 27, 1); }
.diff-removed { background-color: rgba(255, 96, 96, 0.6); color: rgba(191, 38, 38, 1); }

.newpage::before { content: "N"; margin: 0 0.3em 0 -1em; font-weight: bolder; text-decoration: underline dotted; }
.upload::before { content: "\1f845"; margin: 0 0.1em 0 -1.1em; }

footer { padding: 2rem; }
/* #ffdb6d #36962c */
THEMECSS;

/*** Notes ***
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
 *************/



///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "v0.12-dev";
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

///////////////////////////////////////////////////////////////////////////////
////////////////////////////////// Functions //////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

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
	global $pageindex, $paths;
	// Save the new pageindex and return if there aren't any more parent pages to check
	if(strpos($pagename, "/") === false)
	{
		file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
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

/**
 * Makes a path safe.
 * Paths may only contain alphanumeric characters, spaces, underscores, and
 * dashes.
 * @param	string	$string	The string to make safe.
 * @return	string			A safe version of the given string.
 */
function makepathsafe($string)
{
	$string = preg_replace("/[^0-9a-zA-Z\_\-\ \/\.]/i", "", $string);
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
 * @param  bool		$log_trace	Whether to send the stack trace to the error log.
 * @return string				A string prepresentation of a stack trace.
 */
function stack_trace($log_trace = true)
{
	$result = "";
	$stackTrace = debug_backtrace();
	$stackHeight = count($stackTrace);
	foreach ($stackTrace as $i => $stackEntry)
	{
		$result .= "#" . ($stackHeight - $i) . " - " . $stackEntry["file"] . ":" . $stackEntry["line"] . " (" . $stackEntry["function"] . ":" . count($stackEntry["args"]) . ")\n";
	}
	
	if($log_trace)
		error_log($result);
	
	return $result;
}

/**
 * Polyfill getallheaders()
 */
if (!function_exists('getallheaders'))  {
    function getallheaders()
    {
        if (!is_array($_SERVER)) {
            return array();
        }

        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
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
	header("x-pageindex-decode-time: " . round((microtime(true) - $pageindex_read_start)*1000, 3) . "ms");
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

// Finish setting up the environment object
$env->page = $_GET["page"];
$env->action = strtolower($_GET["action"]);

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
			<p>Powered by Pepperminty Wiki v0.12-dev, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or <a href='//github.com/sbrl/Pepperminty-Wiki' title='Github Issue Tracker'>open an issue</a>.</p>
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
			<p><em>Powered by Pepperminty Wiki v0.12-dev.</em></p>
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
			"v0.12-dev" => $version,
			"{favicon-url}" => $settings->favicon,
			"{header-html}" => self::get_header_html(),

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
			return "<link rel='stylesheet' href='$settings->css' />";
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
			return "<style>$css</style>";
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
					case "user-status":
						if($env->is_logged_in)
						{
							$result .= "<span class='inflexible'>Logged in as " . self::render_username($env->user) . ".</span> "/* . page_renderer::$nav_divider*/;
							$result .= "<span><a href='index.php?action=logout'>Logout</a></span>";
							$result .= page_renderer::$nav_divider;
						}
						else
							$result .= "<span class='inflexible'>Browsing as Anonymous.</span>" . /*page_renderer::$nav_divider . */"<span><a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>Login</a></span>" . page_renderer::$nav_divider;
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
		$arrayPageIndex = get_object_vars($pageindex);
		ksort($arrayPageIndex);
		$result = "<datalist id='allpages'>\n";
		foreach($arrayPageIndex as $pagename => $pagedetails)
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
		exit(page_renderer::render_main("Parsing error - $settings->sitename", "<p>Parsing some page source data failed. This is most likely because $settings->sitename has the parser setting set incorrectly. Please contact <a href='mailto:" . hide_email($settings->admindetails["email"]) . "'>" . $settings->admindetails["name"] . "</a>, your $settings->sitename Administrator."));

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


register_module([
	"name" => "Password hashing action",
	"version" => "0.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a utility action (that anyone can use) called hash that hashes a given string. Useful when changing a user's password.",
	"id" => "action-hash",
	"code" => function() {
		
		/*
		 * ██   ██  █████  ███████ ██   ██ 
		 * ██   ██ ██   ██ ██      ██   ██ 
		 * ███████ ███████ ███████ ███████ 
		 * ██   ██ ██   ██      ██ ██   ██ 
		 * ██   ██ ██   ██ ███████ ██   ██
		 */
		
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
		
		/*
		 * ██████  ██████   ██████  ████████ ███████  ██████ ████████ 
		 * ██   ██ ██   ██ ██    ██    ██    ██      ██         ██    
		 * ██████  ██████  ██    ██    ██    █████   ██         ██    
		 * ██      ██   ██ ██    ██    ██    ██      ██         ██    
		 * ██      ██   ██  ██████     ██    ███████  ██████    ██    
		 */
		add_action("protect", function() {
			global $env, $pageindex, $paths, $settings;

			// Make sure that the user is logged in as an admin / mod.
			if($env->is_admin)
			{
				// They check out ok, toggle the page's protection.
				$page = $env->page;
				
				if(!isset($pageindex->$page->protect))
				{
					$pageindex->$page->protect = true;
				}
				else if($pageindex->$page->protect === true)
				{
					$pageindex->$page->protect = false;
				}
				else if($pageindex->$page->protect === false)
				{
					$pageindex->$page->protect = true;
				}
				
				// Save the pageindex
				file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
				
				$state = ($pageindex->$page->protect ? "enabled" : "disabled");
				$title = "Page protection $state.";
				exit(page_renderer::render_main($title, "<p>Page protection for $env->page has been $state.</p><p><a href='?action=$settings->defaultaction&page=$env->page'>Go back</a>."));
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
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a 'raw' action that shows you the raw source of a page.",
	"id" => "action-raw",
	"code" => function() {
		global $settings;
		
		/*
		 * ██████   █████  ██     ██ 
		 * ██   ██ ██   ██ ██     ██ 
		 * ██████  ███████ ██  █  ██ 
		 * ██   ██ ██   ██ ██ ███ ██ 
		 * ██   ██ ██   ██  ███ ███  
		 */
		add_action("raw", function() {
			global $env;

			header("x-filename: " . rawurlencode($env->page) . ".md");
			header("content-type: text/markdown");
			exit(file_get_contents("$env->storage_prefix$env->page.md"));
			exit();
		});
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);




register_module([
	"name" => "Sidebar",
	"version" => "0.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a sidebar to the left hand side of every page. Add '\$settings->sidebar_show = true;' to your configuration, or append '&sidebar=yes' to the url to enable. Adding to the url sets a cookie to remember your setting.",
	"id" => "extra-sidebar",
	"code" => function() {
		global $settings;
		
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
			global $settings, $pageindex, $env;
			
			// Don't render a sidebar if the user is logging in and a login is
			// required in order to view pages.
			if($settings->require_login_view && in_array($env->action, [ "login", "checklogin" ]))
				return false;
			
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
		
		add_help_section("50-sidebar", "Sidebar", "<p>$settings->sitename has an optional sidebar which displays a list of all the current pages (but not subpages) that it is currently hosting. It may or may not be enabled.</p>
		<p>If it isn't enabled, it can be enabled for your current browser only by appending <code>sidebar=yes</code> to the current page's query string.</p>");
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
	"name" => "Page History",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to keep unlimited page history, limited only by your disk space. Note that this doesn't store file history (yet). Currently depends on feature-recent-changes for rendering of the history page.",
	"id" => "feature-history",
	"code" => function() {
		
		/*
		 * ██   ██ ██ ███████ ████████  ██████  ██████  ██    ██
		 * ██   ██ ██ ██         ██    ██    ██ ██   ██  ██  ██
		 * ███████ ██ ███████    ██    ██    ██ ██████    ████
		 * ██   ██ ██      ██    ██    ██    ██ ██   ██    ██
		 * ██   ██ ██ ███████    ██     ██████  ██   ██    ██
		 */
		add_action("history", function() {
			global $settings, $env, $pageindex;
			
			
			$content = "<h1>History for $env->page</h1>\n";
			if(!empty($pageindex->{$env->page}->history))
			{
				$content .= "\t\t<ul class='page-list'>\n";
				foreach($pageindex->{$env->page}->history as $revisionData)
				{
					// Only display edits for now
					if($revisionData->type != "edit")
					continue;
					
					// The number (and the sign) of the size difference to display
					$size_display = ($revisionData->sizediff > 0 ? "+" : "") . $revisionData->sizediff;
					$size_display_class = $revisionData->sizediff > 0 ? "larger" : ($revisionData->sizediff < 0 ? "smaller" : "nochange");
					if($revisionData->sizediff > 500 or $revisionData->sizediff < -500)
					$size_display_class .= " significant";
					$size_title_display = human_filesize($revisionData->newsize - $revisionData->sizediff) . " -> " .  human_filesize($revisionData->newsize);
					
					$content .= "<li>#$revisionData->rid " . render_rchange_editor($revisionData->editor) . " " . render_rchange_timestamp($revisionData->timestamp) . " <span class='cursor-query $size_display_class' title='$size_title_display'>($size_display)</span>";
				}
			}
			else
			{
				$content .= "<p style='text-align: center;'><em>(None yet! Try editing this page and then coming back here.)</em></p>\n";
			}
			exit(page_renderer::render_main("$env->page - History - $settings->sitename", $content));
		});
		
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
			global $pageindex, $paths, $env;
			if(!isset($pageinfo->history))
				$pageinfo->history = [];
			
			// Save the *new source* as a revision
			// This results in 2 copies of the current source, but this is ok
			// since any time someone changes something, it create a new
			// revision
			// Note that we can't save the old source here because we'd have no
			// clue who edited it since $pageinfo has already been updated by
			// this point
			
			// TODO Store tag changes here
			$nextRid = count($pageinfo->history); // The next revision id
			$ridFilename = "$pageinfo->filename.r$nextRid";
			// Insert a new entry into the history
			$pageinfo->history[] = [
				"type" => "edit", // We might want to store other types later (e.g. page moves)
				"rid" => $nextRid,
				"timestamp" => time(),
				"filename" => $ridFilename,
				"newsize" => strlen($newsource),
				"sizediff" => strlen($newsource) - strlen($oldsource),
				"editor" => $pageinfo->lasteditor
			];
			
			// Save the new source as a revision
			file_put_contents("$env->storage_prefix$ridFilename", $newsource);
			
			// Save the edited pageindex
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
		});
	}
]);




register_module([
	"name" => "Recent Changes",
	"version" => "0.3.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds recent changes. Access through the 'recent-changes' action.",
	"id" => "feature-recent-changes",
	"code" => function() {
		global $settings, $env, $paths;
		// Add the recent changes json file to $paths for convenience.
		$paths->recentchanges = $env->storage_prefix . "recent-changes.json";
		// Create the recent changes json file if it doesn't exist
		if(!file_exists($paths->recentchanges))
			file_put_contents($paths->recentchanges, "[]");
		
		/*
		 * ██████  ███████  ██████ ███████ ███    ██ ████████         
		 * ██   ██ ██      ██      ██      ████   ██    ██            
		 * ██████  █████   ██      █████   ██ ██  ██    ██            
		 * ██   ██ ██      ██      ██      ██  ██ ██    ██            
		 * ██   ██ ███████  ██████ ███████ ██   ████    ██            
		 * 
		 *  ██████ ██   ██  █████  ███    ██  ██████  ███████ ███████ 
		 * ██      ██   ██ ██   ██ ████   ██ ██       ██      ██      
		 * ██      ███████ ███████ ██ ██  ██ ██   ███ █████   ███████ 
		 * ██      ██   ██ ██   ██ ██  ██ ██ ██    ██ ██           ██ 
		 *  ██████ ██   ██ ██   ██ ██   ████  ██████  ███████ ███████
		 */
		add_action("recent-changes", function() {
			global $settings, $paths, $pageindex;
			
			$content = "\t\t<h1>Recent Changes</h1>\n";
			
			$recent_changes = json_decode(file_get_contents($paths->recentchanges));
			
			if(count($recent_changes) > 0)
			{
				$content .= render_recent_changes($recent_changes);
			}
			else
			{
				// No changes yet :(
				$content .= "<p><em>None yet! Try making a few changes and then check back here.</em></p>\n";
			}
			
			echo(page_renderer::render("Recent Changes - $settings->sitename", $content));
		});
		
		register_save_preprocessor(function(&$pageinfo, &$newsource, &$oldsource) {
			global $env, $settings, $paths;
			
			// Work out the old and new page lengths
			$oldsize = strlen($oldsource);
			$newsize = strlen($newsource);
			// Calculate the page length difference
			$size_diff = $newsize - $oldsize;
			
			$newchange = [
				"type" => "edit",
				"timestamp" => time(),
				"page" => $env->page,
				"user" => $env->user,
				"newsize" => $newsize,
				"sizediff" => $size_diff
			];
			if($oldsize == 0)
				$newchange["newpage"] = true;
			
			add_recent_change($newchange);
		});
		
		add_help_section("800-raw-page-content", "Recent Changes", "<p>The <a href='?action=recent-changes'>recent changes</a> page displays a list of all the most recent changes that have happened around $settings->sitename, arranged in chronological order. It can be found in the \"More...\" menu in the top right by default.</p>
		<p>Each entry displays the name of the page in question, who edited it, how long ago they did so, and the number of characters added or removed. Pages that <em>currently</em> redirect to another page are shown in italics, and hovering over the time since the edit wil show the exact time that the edit was made.</p>");
	}
]);

/**
 * Adds a new recent change to the recent changes file.
 * @param array $rchange The new change to add.
 */
function add_recent_change($rchange)
{
	global $settings, $paths;
	
	$recentchanges = json_decode(file_get_contents($paths->recentchanges), true);
	array_unshift($recentchanges, $rchange);
	
	// Limit the number of entries in the recent changes file if we've
	// been asked to.
	if(isset($settings->max_recent_changes))
		$recentchanges = array_slice($recentchanges, -$settings->max_recent_changes);
	
	// Save the recent changes file back to disk
	file_put_contents($paths->recentchanges, json_encode($recentchanges, JSON_PRETTY_PRINT));
}

function render_recent_changes($recent_changes)
{
	global $pageindex;
	
	// Cache the number of recent changes we are dealing with
	$rchange_count = count($recent_changes);
	
	// Group changes made on the same page and the same day together
	for($i = 0; $i < $rchange_count; $i++)
	{
		for($s = $i + 1; $s < $rchange_count; $s++)
		{
			// Break out if we have reached the end of the day we are scanning
			if(date("dmY", $recent_changes[$i]->timestamp) !== date("dmY", $recent_changes[$s]->timestamp))
				break;
			
			// If we have found a change that has been made on the same page as
			// the one that we are scanning for, move it up next to the change
			// we are scanning for.
			if($recent_changes[$i]->page == $recent_changes[$s]->page)
			{
				// FUTURE: We may need to remove and insert instead of swapping changes around if this causes some changes to appear out of order.
				$temp = $recent_changes[$i + 1];
				$recent_changes[$i + 1] = $recent_changes[$s];
				$recent_changes[$s] = $temp;
				$i++;
			}
		}
	}
	
	$content = "<ul class='page-list'>\n";
	$last_time = 0;
	for($i = 0; $i < $rchange_count; $i++)
	{
		$rchange = $recent_changes[$i];
		if($last_time !== date("dmY", $rchange->timestamp))
			$content .= "<li class='header'><h2>" . date("jS F", $rchange->timestamp) . "</h2></li>\n";
		
		
		$rchange_results = [];
		for($s = $i; $s < $rchange_count; $s++)
		{
			if($recent_changes[$s]->page !== $rchange->page)
				break;
			
			$rchange_results[$s] = render_recent_change($recent_changes[$s]);
			$i++;
		}
		//$content .= render_recent_change($rchange);
		
		$next_entry = implode("\n", $rchange_results);
		if(count($rchange_results) > 1)
		{
			reset($rchange_results);
			$rchange_first = $recent_changes[key($rchange_results)];
			end($rchange_results);
			$rchange_last = $recent_changes[key($rchange_results)];
			
			$pageDisplayHtml = render_rchange_pagename($rchange_first);
			$timeDisplayHtml = render_rchange_timestamp($rchange_first->timestamp);
			$users = [];
			foreach($rchange_results as $key => $rchange_result)
			{
				if(!in_array($recent_changes[$key]->user, $users))
					$users[] = $recent_changes[$key]->user; 
			}
			$userDisplayHtml = render_rchange_editor(implode(", ", $users));
			
			// TODO: COllect up and render a list of participating users
			$next_entry = "<li><details><summary>$pageDisplayHtml $userDisplayHtml $timeDisplayHtml</summary><ul class='page-list'>$next_entry</ul></details></li>";
			
			$content .= "$next_entry\n";
		}
		else
		{
			$content .= implode("\n", $rchange_results);
		}
		
		$last_time = date("dmY", $rchange->timestamp);
	}
	$content .= "\t\t</ul>";
	
	return $content;
}

function render_recent_change($rchange)
{
	$pageDisplayHtml = render_rchange_pagename($rchange);
	$editorDisplayHtml = render_rchange_editor($rchange->user);
	$timeDisplayHtml = render_rchange_timestamp($rchange->timestamp);
	
	$result = "";
	$resultClasses = [];
	switch(isset($rchange->type) ? $rchange->type : "edit")
	{
		case "edit":
			// The number (and the sign) of the size difference to display
			$size_display = ($rchange->sizediff > 0 ? "+" : "") . $rchange->sizediff;
			$size_display_class = $rchange->sizediff > 0 ? "larger" : ($rchange->sizediff < 0 ? "smaller" : "nochange");
			if($rchange->sizediff > 500 or $rchange->sizediff < -500)
				$size_display_class .= " significant";
			
			
			$size_title_display = human_filesize($rchange->newsize - $rchange->sizediff) . " -> " .  human_filesize($rchange->newsize);
			
			if(!empty($rchange->newpage))
				$resultClasses[] = "newpage";
			
			$result .= "<a href='?page=$rchange->page'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml <span class='$size_display_class' title='$size_title_display'>($size_display)</span>";
			break;
		
		case "deletion":
			$resultClasses[] = "deletion";
			$result .= "$pageDisplayHtml $editorDisplayHtml $timeDisplayHtml";
			break;
		
		case "upload":
			$resultClasses[] = "upload";
			$result .= "<a href='?page=$rchange->page'>$pageDisplayHtml</a> $editorDisplayHtml $timeDisplayHtml (" . human_filesize($rchange->filesize) . ")";
			break;
	}
	
	$resultAttributes = " " . (count($resultClasses) > 0 ? "class='" . implode(" ", $resultClasses) . "'" : "");
	$result = "\t\t\t<li$resultAttributes>$result</li>\n";
	
	return $result;
}

function render_rchange_timestamp($timestamp)
{
	return "<time class='cursor-query' title='" . date("l jS \of F Y \a\\t h:ia T", $timestamp) . "'>" . human_time_since($timestamp) . "</time>";
}

function render_rchange_pagename($rchange)
{
	global $pageindex;
	
	// Render the page's name
	$pageDisplayName = $rchange->page;
	if(isset($pageindex->$pageDisplayName) and !empty($pageindex->$pageDisplayName->redirect))
		$pageDisplayName = "<em>$pageDisplayName</em>";
	$pageDisplayLink = "<a href='?page=" . rawurlencode($rchange->page) . "'>$pageDisplayName</a>";
	
	return $pageDisplayName;
}

function render_rchange_editor($editorName)
{
	return "<span class='editor'>&#9998; $editorName</span>";
}




register_module([
	"name" => "Redirect pages",
	"version" => "0.3",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds support for redirect pages. Uses the same syntax that Mediawiki does.",
	"id" => "feature-redirect",
	"code" => function() {
		global $settings;
		
		register_save_preprocessor(function(&$index_entry, &$pagedata) {
			$matches = [];
			if(preg_match("/^# ?REDIRECT ?\[\[([^\]]+)\]\]/i", $pagedata, $matches) === 1)
			{
				//error_log("matches: " . var_export($matches, true));
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
		
		// Register a help section
		add_help_section("25-redirect", "Redirect Pages", "<p>$settings->sitename supports redirect pages. To create a redirect page, enter something like <code># REDIRECT [[pagename]]</code> on the first line of the redirect page's content. This <em>must</em> appear as the first line of the page, with no whitespace before it. You can include content beneath the redirect if you want, too (such as a reason for redirecting the page).</p>");
	}
]);




register_module([
	"name" => "Search",
	"version" => "0.2.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds proper search functionality to Pepperminty Wiki using an inverted index to provide a full text search engine. If pages don't show up, then you might have hit a stop word. If not, try requesting the `invindex-rebuild` action to rebuild the inverted index from scratch.",
	"id" => "feature-search",
	"code" => function() {
		
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
			
			var_dump($env->page);
			var_dump($source);
			
			var_dump($index);
		});
		
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
			search::rebuild_invindex();
		});
		
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
				search::rebuild_invindex();
			
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
				$content .= "<p>There isn't a page called $query on $settings->sitename, but you ";
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
					$content .= "can <a href='?action=edit&page=" . rawurlencode($query) . "'>create it</a>.</p>";
				}
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
		global $pageindex, $env, $paths;
		
		$invindex = [];
		foreach($pageindex as $pagename => $pagedetails)
		{
			$pagesource = file_get_contents("$env->storage_prefix$pagename.md");
			$index = self::index($pagesource);
			
			self::merge_into_invindex($invindex, ids::getid($pagename), $index);
		}
		
		self::save_invindex($paths->searchindex, $invindex);
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
	
	/**
	 * Deletes the given pageid from the given pageindex.
	 * @param  inverted_index	&$invindex	The inverted index.
	 * @param  number			$pageid		The pageid to remove.
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
		$count = count($query_terms);
		for($i = 0; $i < $count; $i++)
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
			$context = preg_replace("/" . str_replace("/", "\/", preg_quote($qterm)) . "/i", "<strong>$0</strong>", $context);
		}
		
		return $context;
	}
}




register_module([
	"name" => "Uploader",
	"version" => "0.5.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the ability to upload files to Pepperminty Wiki. Uploaded files act as pages and have the special 'File/' prefix.",
	"id" => "feature-upload",
	"code" => function() {
		global $settings;
		
		/*
		 * ██    ██ ██████  ██       ██████   █████  ██████  
		 * ██    ██ ██   ██ ██      ██    ██ ██   ██ ██   ██ 
		 * ██    ██ ██████  ██      ██    ██ ███████ ██   ██ 
		 * ██    ██ ██      ██      ██    ██ ██   ██ ██   ██ 
		 *  ██████  ██      ███████  ██████  ██   ██ ██████  
		 */
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
			<p class='editing_message'>$settings->editing_message</p>
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
					
					// Calculate the target name, removing any characters we
					// are unsure about.
					$target_name = makepathsafe($_POST["name"]);
					$temp_filename = $_FILES["file"]["tmp_name"];
					
					$mimechecker = finfo_open(FILEINFO_MIME_TYPE);
					$mime_type = finfo_file($mimechecker, $temp_filename);
					finfo_close($mimechecker);
					
					if(!in_array($mime_type, $settings->upload_allowed_file_types))
					{
						http_response_code(415);
						exit(page_renderer::render("Unknown file type - Upload error - $settings->sitename", "<p>$settings->sitename recieved the file you tried to upload successfully, but detected that the type of file you uploaded is not in the allowed file types list. The file has been discarded.</p>
						<p>The file you tried to upload appeared to be of type <code>$mime_type</code>, but $settings->sitename currently only allows the uploading of the following file types: <code>" . implode("</code>, <code>", $settings->upload_allowed_file_types) . "</code>.</p>
						<p><a href='?action=$settings->defaultaction'>Go back</a> to the Main Page.</p>"));
					}
					
					// Perform appropriate checks based on the *real* filetype
					switch(substr($mime_type, 0, strpos($mime_type, "/")))
					{
						case "image":
							$extra_data = [];
							// Check SVG uploads with a special function
							$imagesize = $mime_type !== "image/svg+xml" ? getimagesize($temp_filename, $extra_data) : upload_check_svg($temp_filename);
							
							// Make sure that the image size is defined
							if(!is_int($imagesize[0]) or !is_int($imagesize[1]))
							{
								http_response_code(415);
								exit(page_renderer::render("Upload Error - $settings->sitename", "<p>Although the file that you uploaded appears to be an image, $settings->sitename has been unable to determine it's dimensions. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>
								<p>You may wish to consider <a href='https://github.com/sbrl/Pepperminty-Wiki'>opening an issue</a> against Pepperminty Wiki (the software that powers $settings->sitename) if this isn't the first time that you have seen this message.</p>"));
							}
							break;
					}
					
					$file_extension = system_mime_type_extension($mime_type);
					
					// Override the detected file extension if a file extension
					// is explicitly specified in the settings
					if(isset($settings->mime_mappings_overrides[$mime_type]))
						$file_extension = $settings->mime_mappings_overrides[$mime_type];
					
					if(in_array($file_extension, [ "php", ".htaccess", "asp" ]))
					{
						http_response_code(415);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded appears to be dangerous and has been discarded. Please contact $settings->sitename's administrator for assistance.</p>
						<p>Additional information: The file uploaded appeared to be of type <code>$mime_type</code>, which mapped onto the extension <code>$file_extension</code>. This file extension has the potential to be executed accidentally by the web server.</p>"));
					}
					
					$new_filename = "$paths->upload_file_prefix$target_name.$file_extension";
					$new_description_filename = "$new_filename.md";
					
					if(isset($pageindex->$new_filename))
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>A page or file has already been uploaded with the name '$new_filename'. Try deleting it first. If you do not have permission to delete things, try contacting one of the moderators.</p>"));
					
					if(!file_exists("Files"))
						mkdir("Files", 0664);
					
					if(!move_uploaded_file($temp_filename, $env->storage_prefix . $new_filename))
					{
						http_response_code(409);
						exit(page_renderer::render("Upload Error - $settings->sitename", "<p>The file you uploaded was valid, but $settings->sitename couldn't verify that it was tampered with during the upload process. This probably means that either is a configuration error, or $settings->sitename has been attacked. Please contact " . $settings->admindetails["name"] . ", your $settings->sitename Administrator.</p>"));
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
					
					if(module_exists("feature-recent-changes"))
					{
						add_recent_change([
							"type" => "upload",
							"timestamp" => time(),
							"page" => $new_filename,
							"user" => $env->user,
							"filesize" => filesize($entry->uploadedfilepath)
						]);
					}
					
					header("location: ?action=view&page=$new_filename&upload=success");
					
					break;
			}
		});
		
		/*
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██ 
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██ 
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██ 
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██ 
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███  
		 */
		add_action("preview", function() {
			global $settings, $env, $pageindex, $start_time;
			
			if(empty($pageindex->{$env->page}->uploadedfilepath))
			{
				$im = errorimage("The page '$env->page' doesn't have an associated file.");
				header("content-type: image/png");
				imagepng($im);
				exit();
			}
			
			$filepath = $env->storage_prefix . $pageindex->{$env->page}->uploadedfilepath;
			$mime_type = $pageindex->{$env->page}->uploadedfilemime;
			
			// If the size is set or original, then send (or redirect to) the original image
			// Also do the same for SVGs if svg rendering is disabled.
			if(isset($_GET["size"]) and $_GET["size"] == "original" or
				(empty($settings->render_svg_previews) && $mime_type == "image/svg+xml"))
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
			
			/// ETag handling ///
			// Generate the etag and send it to the client
			$preview_etag = sha1("$output_mime|$target_size|$filepath|$mime_type");
			$allheaders = getallheaders();
			$allheaders = array_change_key_case($allheaders, CASE_LOWER);
			if(!isset($allheaders["if-none-match"]))
			{
				header("etag: $preview_etag");
			}
			else
			{
				if($allheaders["if-none-match"] === $preview_etag)
				{
					http_response_code(304);
					header("x-generation-time: " . (microtime(true) - $start_time));
					exit();
				}
			}
			/// ETag handling end ///
			
			/* Disabled until we work out what to do about caching previews *
			$previewFilename = "$filepath.preview.$outputFormat";
			if($target_size === $settings->default_preview_size)
			{
				// The request is for the default preview size
				// Check to see if we have a preview pre-rendered
				
			}
			*/
			
			$preview = new Imagick();
			switch(substr($mime_type, 0, strpos($mime_type, "/")))
			{
				case "image":
					$preview->readImage($filepath);
					break;
				
				case "application":
					if($mime_type == "application/pdf")
					{
						$preview = new imagick();
						$preview->readImage("{$filepath}[0]");
						$preview->setResolution(300,300);
						$preview->setImageColorspace(255);
						break;
					}
				
				case "video":
				case "audio":
					if($settings->data_storage_dir == ".")
					{
						// The data storage directory is the current directory
						// Redirect to the file isntead
						http_response_code(307);
						header("location: " . $pageindex->{$env->page}->uploadedfilepath);
						exit();
					}
					// TODO: Add support for ranges here.
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
					break;
				
				default:
					http_response_code(501);
					$preview = errorimage("Unrecognised file type '$mime_type'.", $target_size);
					header("content-type: image/png");
					imagepng($preview);
					exit();
			}
			
			// Scale the image down to the target size
			$preview->resizeImage($target_size, $target_size, imagick::FILTER_LANCZOS, 1, true);
			
			// Send the completed preview image to the user
			header("content-type: $output_mime");
			header("x-generation-time: " . (microtime(true) - $start_time) . "s");
			$outputFormat = substr($output_mime, strpos($output_mime, "/") + 1);
			$preview->setImageFormat($outputFormat);
			echo($preview->getImageBlob());
			/* Disabled while we work out what to do about caching previews *
			// Save a preview file if there isn't one alreaddy
			if(!file_exists($previewFilename))
				file_put_contents($previewFilename, $preview->getImageBlob());
			*/
		});
		
		/*
		 * ██████  ██████  ███████ ██    ██ ██ ███████ ██     ██
		 * ██   ██ ██   ██ ██      ██    ██ ██ ██      ██     ██
		 * ██████  ██████  █████   ██    ██ ██ █████   ██  █  ██
		 * ██      ██   ██ ██       ██  ██  ██ ██      ██ ███ ██
		 * ██      ██   ██ ███████   ████   ██ ███████  ███ ███
		 * 
		 * ██████  ██ ███████ ██████  ██       █████  ██    ██ ███████ ██████
		 * ██   ██ ██ ██      ██   ██ ██      ██   ██  ██  ██  ██      ██   ██
		 * ██   ██ ██ ███████ ██████  ██      ███████   ████   █████   ██████
		 * ██   ██ ██      ██ ██      ██      ██   ██    ██    ██      ██   ██
		 * ██████  ██ ███████ ██      ███████ ██   ██    ██    ███████ ██   ██
		 */
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $pageindex, $env, $settings;
			// Don't do anything if the action isn't view
			if($env->action !== "view")
				return;
			
			if(isset($pageindex->{$env->page}->uploadedfile) and $pageindex->{$env->page}->uploadedfile == true)
			{
				// We are looking at a page that is paired with an uploaded file
				$filepath = $pageindex->{$env->page}->uploadedfilepath;
				$mime_type = $pageindex->{$env->page}->uploadedfilemime;
				$dimensions = $mime_type !== "image/svg+xml" ? getimagesize($env->storage_prefix . $filepath) : getsvgsize($env->storage_prefix . $filepath);
				$fileTypeDisplay = substr($mime_type, 0, strpos($mime_type, "/"));
				$previewUrl = "?action=preview&size=$settings->default_preview_size&page=" . rawurlencode($env->page);
				
				$preview_html = "";
				switch($fileTypeDisplay)
				{
					case "application":
					case "image":
						$preview_sizes = [ 256, 512, 768, 1024, 1440 ];
						$preview_html .= "\t\t\t<figure class='preview'>
				<img src='$previewUrl' />
				<nav class='image-controls'>
					<ul><li><a href='" . ($env->storage_prefix == "./" ? $filepath : "?action=preview&size=original&page=" . rawurlencode($env->page)) . "'>&#x01f304; Original image</a></li>";
						if($mime_type !== "image/svg+xml")
						{
							$preview_html .= "<li>Other Sizes: ";
							foreach($preview_sizes as $size)
								$preview_html .= "<a href='?action=preview&page=" . rawurlencode($env->page) . "&size=$size'>$size" . "px</a> ";
							$preview_html .= "</li>";
						}
						$preview_html .= "</ul></nav>\n\t\t\t</figure>";
						break;
					
					case "video":
						$preview_html .= "\t\t\t<figure class='preview'>
				<video src='$previewUrl' controls preload='metadata'>Your browser doesn't support HTML5 video, but you can still <a href='$previewUrl'>download it</a> if you'd like.</video>
			</figure>";
						break;
						
					case "audio":
						$preview_html .= "\t\t\t<figure class='preview'>
				<audio src='$previewUrl' controls preload='metadata'>Your browser doesn't support HTML5 audio, but you can still <a href='$previewUrl'>download it</a> if you'd like.</audio>
			</figure>";
				}
				
				$fileInfo = [];
				$fileInfo["Name"] = str_replace("File/", "", $filepath);
				$fileInfo["Type"] = $mime_type;
				$fileInfo["Size"] = human_filesize(filesize($filepath));
				switch($fileTypeDisplay)
				{
					case "image":
						$dimensionsKey = $mime_type !== "image/svg+xml" ? "Original demensions" : "Native size";
						$fileInfo[$dimensionsKey] = "$dimensions[0] x $dimensions[1]";
						break;
				}
				$fileInfo["Uploaded by"] = $pageindex->{$env->page}->lasteditor;
				
				$preview_html .= "\t\t\t<h2>File Information</h2>
			<table>";
				foreach ($fileInfo as $displayName => $displayValue)
				{
					$preview_html .= "<tr><th>$displayName</th><td>$displayValue</td></tr>\n";
				}
				$preview_html .= "</table>";
				
				$parts["{content}"] = str_replace("</h1>", "</h1>\n$preview_html", $parts["{content}"]);
			}
		});
		
		// Register a section on the help page on uploading files
		add_help_section("28-uploading-files", "Uploading Files", "<p>$settings->sitename supports the uploading of files, though it is up to " . $settings->admindetails["name"] . ", $settings->sitename's administrator as to whether it is enabled or not (uploads are currently " . (($settings->upload_enabled) ? "enabled" : "disabled") . ").</p>
		<p>Currently Pepperminty Wiki (the software that $settings->sitename uses) only supports the uploading of images, although more file types should be supported in the future (<a href='//github.com/sbrl/Pepperminty-Wiki/issues'>open an issue on GitHub</a> if you are interested in support for more file types).</p>
		<p>Uploading a file is actually quite simple. Click the &quot;Upload&quot; option in the &quot;More...&quot; menu to go to the upload page. The upload page will tell you what types of file $settings->sitename allows, and the maximum supported filesize for files that you upload (this is usually set by the web server that the wiki is running on).</p>
		<p>Use the file chooser to select the file that you want to upload, and then decide on a name for it. Note that the name that you choose should not include the file extension, as this will be determined automatically. Enter a description that will appear on the file's page, and then click upload.</p>");
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

function upload_check_svg($temp_filename)
{
	global $settings;
	// Check for script tags
	if(strpos(file_get_contents($temp_filename), "<script") !== false)
	{
		http_response_code(415);
		exit(page_renderer::render("Upload Error - $settings->sitename", "<p>$settings->sitename detected that you uploaded an SVG image and performed some extra security checks on your file. Whilst performing these checks it was discovered that the file you uploaded contains some Javascript, which could be dangerous. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>
		<p>You may wish to consider <a href='https://github.com/sbrl/Pepperminty-Wiki'>opening an issue</a> against Pepperminty Wiki (the software that powers $settings->sitename) if this isn't the first time that you have seen this message.</p>"));
	}
	
	// Find and return the size of the SVG image
	return getsvgsize($temp_filename);
}

function getsvgsize($svgFilename)
{
	$svg = simplexml_load_file($svgFilename); // Load it as XML
	if($svg === false)
	{
		http_response_code(415);
		exit(page_renderer::render("Upload Error - $settings->sitename", "<p>When $settings->sitename tried to open your SVG file for checking, it found some invalid syntax. The uploaded file has been discarded. <a href='?action=upload'>Go back to try again</a>.</p>"));
	}
	$rootAttrs = $svg->attributes();
	$imageSize = false;
	if(isset($rootAttrs->width) and isset($rootAttrs->height))
		$imageSize = [ intval($rootAttrs->width), intval($rootAttrs->height) ];
	else if(isset($rootAttrs->viewBox))
		$imageSize = array_map("intval", array_slice(explode(" ", $rootAttrs->viewBox), -2, 2));
	
	return $imageSize;
}

function errorimage($text, $target_size)
{
	$width = 640;
	$height = 480;
	
	if(!empty($target_size))
	{
		$width = $target_size;
		$height = $target_size * (2 / 3);
	}
	
	$image = imagecreatetruecolor($width, $height);
	imagefill($image, 0, 0, imagecolorallocate($image, 238, 232, 242)); // Set the background to #eee8f2
	$fontwidth = imagefontwidth(3);
	imagestring($image, 3,
		($width / 2) - (($fontwidth * strlen($text)) / 2),
		($height / 2) - (imagefontheight(3) / 2),
		$text,
		imagecolorallocate($image, 17, 17, 17) // #111111
	);
	
	return $image;
}




register_module([
	"name" => "Credits",
	"version" => "0.7.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the credits page. You *must* have this module :D",
	"id" => "page-credits",
	"code" => function() {
		
		/*
		 *  ██████ ██████  ███████ ██████  ██ ████████ ███████ 
		 * ██      ██   ██ ██      ██   ██ ██    ██    ██      
		 * ██      ██████  █████   ██   ██ ██    ██    ███████ 
		 * ██      ██   ██ ██      ██   ██ ██    ██         ██ 
		 *  ██████ ██   ██ ███████ ██████  ██    ██    ███████ 
		 */
		add_action("credits", function() {
			global $settings, $version, $pageindex, $modules;
			
			$credits = [
				"Code" => [
					"author" => "Starbeamrainbowlabs",
					"author_url" => "https://starbeamrmainbowlabs.com/",
					"thing_url" => "https://github.com/sbrl/Pepprminty-Wiki",
					"icon" => "https://avatars0.githubusercontent.com/u/9929737?v=3&s=24"
				],
				"Mime type to file extension mapper" => [
					"author" => "Chaos",
					"author_url" => "https://stackoverflow.com/users/47529/chaos",
					"thing_url" => "https://stackoverflow.com/a/1147952/1460422",
					"icon" => "https://www.gravatar.com/avatar/aaee40db39ad6b164cfb89cb6ad4d176?s=328&d=identicon&s=24"
				],
				"Parsedown" => [
					"author" => "Emanuil Rusev and others",
					"author_url" => "https://github.com/erusev/",
					"thing_url" => "https://github.com/erusev/parsedown/",
					"icon" => "https://avatars1.githubusercontent.com/u/184170?v=3&s=24"
				],
				"CSS Minification Code" => [
					"author" => "Jean",
					"author_url" => "http://www.catswhocode.com/",
					"thing_url" => "http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php"
				],
				"Slightly modified version of Slimdown" => [
					"author" => "Johnny Broadway",
					"author_url" => "https://github.com/jbroadway",
					"thing_url" => "https://gist.github.com/jbroadway/2836900",
					"icon" => "https://avatars2.githubusercontent.com/u/87886?v=3&s=24"
				],
				"Default Favicon" => [
					"author" => "bluefrog23",
					"author_url" => "https://openclipart.org/user-detail/bluefrog23/",
					"thing_url" => "https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23"
				],
				"Bug Reports" => [
					"author" => "nibreh",
					"author_url" => "https://github.com/nibreh/",
					"thing_url" => "",
					"icon" => "https://avatars2.githubusercontent.com/u/7314006?v=3&s=24"
				]
			];
			
			//// Credits html renderer ////
			$credits_html = "<ul>\n";
			foreach($credits as $thing => $author_details)
			{
				$credits_html .= "	<li>";
				$credits_html .= "<a href='" . $author_details["thing_url"] . "'>$thing</a> by ";
				if(isset($author_details["icon"]))
				$credits_html .= "<img style='vertical-align: middle;' src='" . $author_details["icon"] . "' /> ";
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
	<p>$settings->sitename is powered by Pepperminty Wiki - an entire wiki packed inside a single file, which was built by <a href='//starbeamrainbowlabs.com'>Starbeamrainbowlabs</a>, and can be found <a href='//github.com/sbrl/Pepperminty-Wiki/'>on GitHub</a> (contributors will also be listed here in the future). Pepperminty Wiki is licensed under the <a target='_blank' href='https://www.mozilla.org/en-US/MPL/2.0/'>Mozilla Public License 2.0</a> (<a target='_blank' href='https://tldrlegal.com/license/mozilla-public-license-2.0-(mpl-2)'>simple version</a>).</p>
	<h2>Main Credits</h2>
	$credits_html
	<h2>Site status</h2>
	<table>
		<tr><th>Site name:</th><td>$settings->sitename (<a href='?action=update'>$settings->admindisplaychar Update</a>, <a href='?action=export'>Export as zip - Check for permission first</a>)</td></tr>
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
	"name" => "Debug Information",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a debug action for administrator use only that collects a load of useful information to make reporting bugs easier.",
	"id" => "page-debug-info",
	"code" => function() {
		global $settings, $env;
		
		/*
		 * ██████  ███████ ██████  ██    ██  ██████  
		 * ██   ██ ██      ██   ██ ██    ██ ██       
		 * ██   ██ █████   ██████  ██    ██ ██   ███ 
		 * ██   ██ ██      ██   ██ ██    ██ ██    ██ 
		 * ██████  ███████ ██████   ██████   ██████
		*/
		add_action("debug", function() {
			global $settings, $env, $paths, $version;
			header("content-type: text/plain");
			
			if(!$env->is_admin)
			{
				exit("You must be logged in as an moderator in order to generate debugging information.");
			}
			
			$title = "$settings->sitename debug report";
			echo("$title\n");
			echo(str_repeat("=", strlen($title)) . "\n");
			echo("Powered by Pepperminty Wiki version $version.\n");
			echo("This report may contain personal information.\n\n");
			echo("Environment: ");
			echo(var_export($env, true));
			echo("\nPaths: ");
			var_dump(var_export($paths, true));
			echo("\nServer information:\n");
			echo("uname -a: " . php_uname() . "\n");
			echo("Path: " . getenv("PATH") . "\n");
			echo("Temporary directory: " . sys_get_temp_dir() . "\n");
			echo("Server: " . $_SERVER["SERVER_SOFTWARE"] . "\n");
			echo("Web root: " . $_SERVER["DOCUMENT_ROOT"] . "\n");
			echo("Web server user: " . exec("whoami") . "\n");
			echo("PHP version: " . phpversion() . "\n");
			echo("index.php location: " . __FILE__ . "\n");
			echo("index.php file permissions: " . substr(sprintf('%o', fileperms("./index.php")), -4) . "\n");
			echo("Current folder permissions: " . substr(sprintf('%o', fileperms(".")), -4) . "\n");
			echo("Storage directory permissions: " . substr(sprintf('%o', fileperms($env->storage_prefix)), -4) . "\n");
			echo("Loaded extensions: " . implode(", ", get_loaded_extensions()) . "\n");
			echo("Settings:\n-----\n");
			$settings_export = explode("\n", var_export($settings, true));
			foreach ($settings_export as &$row)
			{
				if(preg_match("/(sitesecret|email)/i", $row)) $row = "********* secret *********"; 
			}
			echo(implode("\n", $settings_export));
			echo("\n-----\n");
			exit();
		});
		
		if($env->is_admin)
		{
			add_help_section("810-debug-information", "Gathering debug information", "<p>As a moderator, $settings->sitename gives you the ability to generate a report on $settings->sitename's installation of Pepperminty Wiki for debugging purposes.</p>
			<p>To generate such a report, visit the <code>debug</code> action or <a href='?action=debug'>click here</a>.</p>");
		}
	}
]);




register_module([
	"name" => "Page deleter",
	"version" => "0.9",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to delete pages.",
	"id" => "page-delete",
	"code" => function() {
		global $settings;
		
		/*
		 * ██████  ███████ ██      ███████ ████████ ███████ 
		 * ██   ██ ██      ██      ██         ██    ██      
		 * ██   ██ █████   ██      █████      ██    █████   
		 * ██   ██ ██      ██      ██         ██    ██      
		 * ██████  ███████ ███████ ███████    ██    ███████ 
		 */
		add_action("delete", function() {
			global $pageindex, $settings, $env, $paths, $modules;
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
			
			// Delete the page from the page index
			unset($pageindex->$page);
			
			// Save the new page index
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT)); 
			
			// Remove the page's name from the id index
			ids::deletepagename($env->page);
			
			// Delete the page from the search index, if that module is installed
			if(module_exists("feature-search"))
			{
				$pageid = ids::getid($env->page);
				$invindex = search::load_invindex($paths->searchindex);
				search::delete_entry($invindex, $pageid);
				search::save_invindex($paths->searchindex, $invindex);
			}
			
			// Delete the page from the disk
			unlink("$env->storage_prefix$env->page.md");
			
			// Add a recent change announcing the deletion if the recent changes
			// module is installed
			if(module_exists("feature-recent-changes"))
			{
				add_recent_change([
					"type" => "deletion",
					"timestamp" => time(),
					"page" => $env->page,
					"user" => $env->user,
				]);
			}
			
			exit(page_renderer::render_main("Deleting $env->page - $settings->sitename", "<p>$env->page has been deleted. <a href='index.php'>Go back to the main page</a>.</p>"));
		});
		
		// Register a help section
		add_help_section("60-delete", "Deleting Pages", "<p>If you are logged in as an adminitrator, then you have the power to delete pages. To do this, click &quot;Delete&quot; in the &quot;More...&quot; menu when browsing the pge you wish to delete. When you are sure that you want to delete the page, click the given link.</p>
		<p><strong>Warning: Once a page has been deleted, you can't bring it back! You will need to recover it from your backup, if you have one (which you really should).</strong></p>");
	}
]);




register_module([
	"name" => "Page editor",
	"version" => "0.14",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	
	"code" => function() {
		global $settings;
		
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
			
			if((!$env->is_logged_in and !$settings->anonedits) or // if we aren't logged in and anonymous edits are disabled
			   !$settings->editing or // or editing is disabled
			   (
				   isset($pageindex->$page) and // or if the page exists
				   isset($pageindex->$page->protect) and // the protect property exists
				   $pageindex->$page->protect and // the protect property is true
				   !$env->is_admin // the user isn't an admin
			   )
			)
			{
				if(!$creatingpage)
				{
					// The page already exists - let the user view the page source
					if($env->is_logged_in)
						exit(page_renderer::render_main("Viewing source for $env->page", "<p>$env->page is protected, and you aren't an administrator or moderator. You can view the source of $env->page below, but you can't edit it.</p><textarea name='content' readonly>$pagetext</textarea>"));
					else
						exit(page_renderer::render_main("Viewing source for $env->page", "<p>$settings->sitename does not allow anonymous users to make edits. You can view the source of $env->page below, but you can't edit it. You could, however, try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p><textarea name='content' readonly>$pagetext</textarea>"));
						
				}
				else
				{
					http_response_code(404);
					exit(page_renderer::render_main("404 - $env->page", "<p>The page <code>$env->page</code> does not exist, but you do not have permission to create it.</p><p>If you haven't already, perhaps you should try <a href='index.php?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "'>logging in</a>.</p>"));
				}
			}
			
			$content = "<h1>$title</h1>";
			$page_tags = implode(", ", (!empty($pageindex->{$env->page}->tags)) ? $pageindex->{$env->page}->tags : []);
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save' class='editform'>
			<input type='hidden' name='prev-content-hash' value='" . sha1($pagetext) . "' />
			<textarea name='content' autofocus tabindex='1'>$pagetext</textarea>
			<input type='text' name='tags' value='$page_tags' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
			<p class='editing-message'>$settings->editing_message</p>
			<input name='submit-edit' type='submit' value='Save Page' tabindex='3' />
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
			// We don't need to santise the input here as Parsedown has an
			// option that does this for us, and is _way_ more intelligent about
			// it.
			
			// Read in the new page tags, so long as there are actually some
			// tags to read in
			$page_tags = [];
			if(strlen(trim($_POST["tags"])) > 0)
			{
				$page_tags = explode(",", $_POST["tags"]);
				// Trim off all the whitespace
				foreach($page_tags as &$tag)
					$tag = trim($tag);
			}
			
			// Check for edit conflicts
			$existing_content_hash = sha1_file($env->storage_prefix . $pageindex->{$env->page}->filename);
			if(isset($_POST["prev-content-hash"]) and
				$existing_content_hash != $_POST["prev-content-hash"])
			{
				$existingPageData = htmlentities(file_get_contents($env->storage_prefix . $env->storage_prefix . $pageindex->{$env->page}->filename));
				// An edit conflict has occurred! We should get the user to fix it.
				$content = "<h1>Resolving edit conflict - $env->page</h1>";
				if(!$env->is_logged_in and $settings->anonedits)
				{
					$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
				}
				$content .= "<p>An edit conflict has arisen because someone else has saved an edit to $env->page since you started editing it. Both texts are shown below, along the differences between the 2 conflicting revisions. To continue, please merge your changes with the existing content. Note that only the text in the existing content box will be kept when you press the \"Resolve Conflict\" button at the bottom of the page.</p>
			
			<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save' class='editform'>
				<h2>Existing content</h2>
				<textarea id='original-content' name='content' autofocus tabindex='1'>$existingPageData</textarea>
				
				<h2>Differences</h2>
				<div id='highlighted-diff' class='highlighted-diff'></div>
				<!--<pre class='highlighted-diff-wrapper'><code id='highlighted-diff'></code></pre>-->
				
				<h2>Your content</h2>
				<textarea id='new-content'>$pagedata</textarea>
				<input type='text' name='tags' value='" . $_POST["tags"] . "' placeholder='Enter some tags for the page here. Separate them with commas.' title='Enter some tags for the page here. Separate them with commas.' tabindex='2' />
				<p class='editing_message'>$settings->editing_message</p>
				<input name='submit-edit' type='submit' value='Resolve Conflict' tabindex='3' />
			</form>";
				
				// Insert a reference to jsdiff to generate the diffs
				$diffScript = <<<'DIFFSCRIPT'
window.addEventListener("load", function(event) {
	var destination = document.getElementById("highlighted-diff"),
		diff = JsDiff.diffWords(document.getElementById("original-content").value, document.getElementById("new-content").value),
		output = "";
	diff.forEach(function(change) {
		var classes = "token";
		if(change.added) classes += " diff-added";
		if(change.removed) classes += " diff-removed";
		output += `<span class='${classes}'>${change.value}</span>`;
	});
	destination.innerHTML = output;
});
DIFFSCRIPT;

				$content .= "\n<script src='https://cdnjs.cloudflare.com/ajax/libs/jsdiff/2.2.2/diff.min.js'></script>
		<script>$diffScript</script>\n";
				
				exit(page_renderer::render_main("Edit Conflict - $env->page - $settings->sitename", $content));
			}
			
			// Update the inverted search index
			
			// Construct an index for the old and new page content
			$oldindex = [];
			$oldpagedata = ""; // We need the old page data in order to pass it to the preprocessor
			if(file_exists("$env->page.md"))
			{
				$oldpagedata = file_get_contents("$env->page.md");
				$oldindex = search::index($oldpagedata);
			}
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
					$func($pageindex->$page, $pagedata, $oldpagedata);
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
		
		add_help_section("15-editing", "Editing", "<p>To edit a page on $settings->sitename, click the edit button on the top bar. Note that you will probably need to be logged in. If you do not already have an account you will need to ask $settings->sitename's administrator for an account since there is no registration form. Note that the $settings->sitename's administrator may have changed these settings to allow anonymous edits.</p>
		<p>Editing is simple. The edit page has a sizeable box that contains a page's current contents. Once you are done altering it, add or change the comma separated list of tags in the field below the editor and then click save page.</p>
		<p>A reference to the syntax that $settings->sitename supports can be found below.</p>");
	}
]);




register_module([
	"name" => "Export",
	"version" => "0.4",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that you can use to export your wiki as a .zip file. Uses \$settings->export_only_allow_admins, which controls whether only admins are allowed to export the wiki.",
	"id" => "page-export",
	"code" => function() {
		global $settings;
		
		/*
		 * ███████ ██   ██ ██████   ██████  ██████  ████████ 
		 * ██       ██ ██  ██   ██ ██    ██ ██   ██    ██    
		 * █████     ███   ██████  ██    ██ ██████     ██    
		 * ██       ██ ██  ██      ██    ██ ██   ██    ██    
		 * ███████ ██   ██ ██       ██████  ██   ██    ██    
		 */
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
		
		// Add a section to the help page
		add_help_section("50-export", "Exporting", "<p>$settings->sitename supports exporting the entire wiki's content as a zip. Note that you may need to be a moderator in order to do this. Also note that you should check for permission before doing so, even if you are able to export without asking.</p>
		<p>To perform an export, go to the credits page and click &quot;Export as zip - Check for permission first&quot;.</p>");
	}
]);




register_module([
	"name" => "Help page",
	"version" => "0.9.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a rather useful help page. Access through the 'help' action. This module also exposes help content added to Pepperminty Wiki's inbuilt invisible help section system.",
	"id" => "page-help",
	"code" => function() {
		global $settings;
		
		/*
		 * ██   ██ ███████ ██      ██████  
		 * ██   ██ ██      ██      ██   ██ 
		 * ███████ █████   ██      ██████  
		 * ██   ██ ██      ██      ██      
		 * ██   ██ ███████ ███████ ██      
		 */
		add_action("help", function() {
			global $paths, $settings, $version, $help_sections, $actions;
			
			// Sort the help sections by key
			ksort($help_sections, SORT_NATURAL);
			
			if(isset($_GET["dev"]) and $_GET["dev"] == "yes")
			{
				$title = "Developers Help - $settings->sitename";
				$content = "<p>$settings->sitename runs on Pepperminty Wiki, an entire wiki packed into a single file. This page contains some information that developers may find useful.</p>
				<p>A full guide to developing a Pepperminty Wiki module can be found <a href='//github.com/sbrl/Pepperminty-Wiki/blob/master/Module_API_Docs.md#module-api-documentation'>on GitHub</a>.</p>
				<h3>Registered Help Sections</h3>
				<p>The following help sections are currently registered:</p>
				<table><tr><th>Index</th><th>Title</th><th>Length</th></tr>\n";
				$totalSize = 0;
				foreach($help_sections as $index => $section)
				{
					$sectionLength = strlen($section["content"]);
					$totalSize += $sectionLength;
					
					$content .= "\t\t\t<tr><td>$index</td><td>" . $section["title"] . "</td><td>" . human_filesize($sectionLength) . "</td></tr>\n";
				}
				$content .= "\t\t\t<tr><th colspan='2' style='text-align: right;'>Total:</th><td>" . human_filesize($totalSize) . "</td></tr>\n";
				$content .= "\t\t</table>\n";
				$content .= "<h3>Registered Actions</h3>
				<p>The following actions are currently registered:</p>\n";
				$content .= "<p>" . implode(", ", array_keys(get_object_vars($actions))) . "</p>";
				$content .= "<h3>Environment</h3>\n";
				$content .= "<p>$settings->sitename's root directory is " . (!is_writeable(__DIR__) ? "not " : "") . "writeable.</p>";
				$content .= "<p>The page index is currently " . human_filesize(filesize($paths->pageindex)) . " in size.</p>";
			}
			else
			{
				$title = "Help - $settings->sitename";
				
				$content = "	<h1>$settings->sitename Help</h1>
		<p>Welcome to $settings->sitename!</p>
		<p>$settings->sitename is powered by Pepperminty Wiki, a complete wiki in a box you can drop into your server and expect it to just <em>work</em>.</p>";
				
				// Todo Insert a table of contents here?
				
				foreach($help_sections as $index => $section)
				{
					// Todo add a button that you can click to get a permanent link
					// to this section.
					$content .= "<h2 id='$index' class='help-section-header'>" . $section["title"] . "</h2>\n";
					$content .= $section["content"] . "\n";
				}
			}
			
			exit(page_renderer::render_main($title, $content));
		});
		
		// Register a help section on general navigation
		add_help_section("5-navigation", "Navigating", "<p>All the navigation links can be found on the top bar, along with a search box (if your site administrator has enabled it). There is also a &quot;More...&quot; menu in the top right that contains some additional links that you may fine useful.</p>
		<p>This page, along with the credits page, can be found on the bar at the bottom of every page.</p>");
		
		add_help_section("999-extra", "Extra Information", "<p>You can find out whch version of Pepperminty Wiki $settings->sitename is using by visiting the <a href='?action=credits'>credits</a> page.</p>
		<p>Information for developers can be found on <a href='?action=help&dev=yes'>this page</a>.</p>");
	}
]);




register_module([
	"name" => "Page list",
	"version" => "0.10.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a page that lists all the pages in the index along with their metadata.",
	"id" => "page-list",
	"code" => function() {
		global $settings;
		
		/*
		 * ██      ██ ███████ ████████ 
		 * ██      ██ ██         ██    
		 * ██      ██ ███████    ██    
		 * ██      ██      ██    ██    
		 * ███████ ██ ███████    ██    
		 */
		add_action("list", function() {
			global $pageindex, $settings;
			
			$title = "All Pages";
			$content = "	<h1>$title on $settings->sitename</h1>";
			
			$sorted_pageindex = get_object_vars($pageindex);
			ksort($sorted_pageindex, SORT_NATURAL);
			
			$content .= generate_page_list(array_keys($sorted_pageindex));
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/*
		 * ██      ██ ███████ ████████ ████████  █████   ██████  ███████ 
		 * ██      ██ ██         ██       ██    ██   ██ ██       ██      
		 * ██      ██ ███████    ██ █████ ██    ███████ ██   ███ ███████ 
		 * ██      ██      ██    ██       ██    ██   ██ ██    ██      ██ 
		 * ███████ ██ ███████    ██       ██    ██   ██  ██████  ███████ 
		 */
		add_action("list-tags", function() {
			global $pageindex, $settings;
			
			if(!isset($_GET["tag"]))
			{
				// Render a list of all tags
				$all_tags = [];
				foreach($pageindex as $entry)
				{
					if(!isset($entry->tags)) continue;
					foreach($entry->tags as $tag)
					{
						if(!in_array($tag, $all_tags)) $all_tags[] = $tag;
					}
				}
				
				$content = "<h1>All tags</h1>
				<ul class='tag-list'>\n";
				foreach($all_tags as $tag)
				{
					$content .= "			<li><a href='?action=list-tags&tag=" . rawurlencode($tag) . "' class='mini-tag'>$tag</a></li>\n";
				}
				$content .= "</ul>\n";
				
				exit(page_renderer::render("All tags - $settings->sitename", $content));
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
			
			$content = "<h1>Tag List: $tag</h1>\n";
			$content .= generate_page_list($pagelist);
			
			$content .= "<p>(<a href='?action=list-tags'>All tags</a>)</p>\n";
			
			exit(page_renderer::render("$tag - Tag List - $settings->sitename", $content));
		});
		
		add_help_section("30-all-pages-tags", "Listing pages and tags", "<p>All the pages and tags on $settings->sitename are listed on a pair of pages to aid navigation. The list of all pages on $settings->sitename can be found by clicking &quot;All Pages&quot; on the top bar. The list of all the tags currently in use can be found by clicking &quot;All Tags&quot; in the &quot;More...&quot; menu in the top right.</p>
		<p>Each tag on either page can be clicked, and leads to a list of all pages that possess that particular tag.</p>
		<p>Redirect pages are shown in italics. A page's last known editor is also shown next to each entry on a list of pages, along with the last known size (which should correct, unless it was changed outside of $settings->sitename) and the time since the last modification (hovering over this will show the exact time that the last modification was made in a tooltip).</p>");
	}
]);

function generate_page_list($pagelist)
{
	global $pageindex;
	// ✎ &#9998; 🕒 &#128338;
	$result = "<ul class='page-list'>\n";
	foreach($pagelist as $pagename)
	{
		// Construct a list of tags that are attached to this page ready for display
		$tags = "";
		// Make sure that this page does actually have some tags first
		if(isset($pageindex->$pagename->tags))
		{
			foreach($pageindex->$pagename->tags as $tag)
			{
				$tags .= "<a href='?action=list-tags&tag=" . rawurlencode($tag) . "' class='mini-tag'>$tag</a>, ";
			}
			$tags = substr($tags, 0, -2); // Remove the last ", " from the tag list
		}
		
		$pageDisplayName = $pagename;
		if(isset($pageindex->$pagename) and
			!empty($pageindex->$pagename->redirect))
			$pageDisplayName = "<em>$pageDisplayName</em>";
		
		$result .= "<li><a href='index.php?page=$pagename'>$pageDisplayName</a>
		<em class='size'>(" . human_filesize($pageindex->$pagename->size) . ")</em>
		<span class='editor'><span class='texticon cursor-query' title='Last editor'>&#9998;</span> " . $pageindex->$pagename->lasteditor . "</span>
		<time class='cursor-query' title='" . date("l jS \of F Y \a\\t h:ia T", $pageindex->$pagename->lastmodified) . "'>" . human_time_since($pageindex->$pagename->lastmodified) . "</time>
		<span class='tags'>$tags</span></li>";
	}
	$result .= "		</ul>\n";
	
	return $result;
}




register_module([
	"name" => "Login",
	"version" => "0.8.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a pair of actions (login and checklogin) that allow users to login. You need this one if you want your users to be able to login.",
	"id" => "page-login",
	"code" => function() {
		global $settings;
		
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
			
			// Build the action url that will actually perform the login
			$login_form_action_url = "index.php?action=checklogin";
			if(isset($_GET["returnto"]))
				$login_form_action_url .= "&returnto=" . rawurlencode($_GET["returnto"]);
			
			$title = "Login to $settings->sitename";
			$content = "<h1>Login to $settings->sitename</h1>\n";
			if(isset($_GET["failed"]))
				$content .= "\t\t<p><em>Login failed.</em></p>\n";
			if(isset($_GET["required"]))
				$content .= "\t\t<p><em>$settings->sitename requires that you login before continuing.</em></p>\n";
			$content .= "\t\t<form method='post' action='$login_form_action_url'>
				<label for='user'>Username:</label>
				<input type='text' name='user' id='user' autofocus />
				<br />
				<label for='pass'>Password:</label>
				<input type='password' name='pass' id='pass' />
				<br />
				<input type='submit' value='Login' />
			</form>\n";
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
					if(isset($_GET["returnto"]))
						header("location: " . $_GET["returnto"]);
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
		
		// Register a section on logging in on the help page.
		add_help_section("30-login", "Logging in", "<p>In order to edit $settings->sitename and have your edit attributed to you, you need to be logged in. Depending on the settings, logging in may be a required step if you want to edit at all. Thankfully, loggging in is not hard. Simply click the &quot;Login&quot; link in the top left, type your username and password, and then click login.</p>
		<p>If you do not have an account yet and would like one, try contacting <a href='mailto:" . hide_email($settings->admindetails["email"]) . "'>" . $settings->admindetails["name"] . "</a>, $settings->sitename's administrator and ask them nicely to see if they can create you an account.</p>");
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
		/*
		 * ██       ██████   ██████   ██████  ██    ██ ████████ 
		 * ██      ██    ██ ██       ██    ██ ██    ██    ██    
		 * ██      ██    ██ ██   ███ ██    ██ ██    ██    ██    
		 * ██      ██    ██ ██    ██ ██    ██ ██    ██    ██    
		 * ███████  ██████   ██████   ██████   ██████     ██    
		 */
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
	"version" => "0.8.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to allow administrators to move pages.",
	"id" => "page-move",
	"code" => function() {
		global $settings;
		
		/*
		 * ███    ███  ██████  ██    ██ ███████ 
		 * ████  ████ ██    ██ ██    ██ ██      
		 * ██ ████ ██ ██    ██ ██    ██ █████   
		 * ██  ██  ██ ██    ██  ██  ██  ██      
		 * ██      ██  ██████    ████   ███████ 
		 */
		add_action("move", function() {
			global $pageindex, $settings, $env, $paths;
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
			
			if(isset($pageindex->$page->uploadedfile) and
				file_exists($new_name))
				exit(page_renderer::render_main("Moving $env->page - Error - $settings->sitename", "<p>Whilst moving the file associated with $env->page, $settings->sitename detected a pre-existing file on the server's file system. Because $settings->sitename can't determine whether the existing file is important to another component of $settings->sitename or it's host web server, the move have been aborted - just in case.</p>
				<p>If you know that this move is actually safe, please get your site administrator (" . $settings->admindetails["name"] . ") to perform the move manually. Their contact address can be found at the bottom of every page (including this one).</p>"));
			
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
			file_put_contents($paths->pageindex, json_encode($pageindex, JSON_PRETTY_PRINT));
			
			// Move the page on the disk
			rename("$env->storage_prefix$env->page.md", "$env->storage_prefix$new_name.md");
			
			// Move the page in the id index
			ids::movepagename($page, $new_name);
			
			// Exit with a nice message
			exit(page_renderer::render_main("Moving $env->page", "<p><a href='index.php?page=$env->page'>$env->page</a> has been moved to <a href='index.php?page=$new_name'>$new_name</a> successfully.</p>"));
		});
		
		// Register a help section
		add_help_section("60-move", "Moving Pages", "<p>If you are logged in as an adminitrator, then you have the power to move pages. To do this, click &quot;Delete&quot; in the &quot;More...&quot; menu when browsing the pge you wish to move. Type in the new name of the page, and then click &quot;Move Page&quot;.</p>");
	}
]);




register_module([
	"name" => "Update",
	"version" => "0.6.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an update page that downloads the latest stable version of Pepperminty Wiki. This module is currently outdated as it doesn't save your module preferences.",
	"id" => "page-update",
	"code" => function() {
		/*
		 * ██    ██ ██████  ██████   █████  ████████ ███████ 
		 * ██    ██ ██   ██ ██   ██ ██   ██    ██    ██      
		 * ██    ██ ██████  ██   ██ ███████    ██    █████   
		 * ██    ██ ██      ██   ██ ██   ██    ██    ██      
		 *  ██████  ██      ██████  ██   ██    ██    ███████ 
		 */
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
	"version" => "0.13",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to view pages. You really should include this one.",
	"id" => "page-view",
	"code" => function() {
		/*
		 * ██    ██ ██ ███████ ██     ██ 
		 * ██    ██ ██ ██      ██     ██ 
		 * ██    ██ ██ █████   ██  █  ██ 
		 *  ██  ██  ██ ██      ██ ███ ██ 
		 *   ████   ██ ███████  ███ ███  
		 */
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
			
			$content .= "\n\t\t<!-- Took " . round((microtime(true) - $parsing_start) * 1000, 2) . "ms to parse page source -->\n";
			
			// Prevent indexing of this page if it's still within the noindex
			// time period
			if(isset($settings->delayed_indexing_time) and
				time() - $pageindex->{$env->page}->lastmodified < $settings->delayed_indexing_time)
				header("x-robots-tag: noindex");
			
			// Content only mode: Send only the raw rendered page
			if(isset($_GET["contentonly"]) and $_GET["contentonly"] === "yes")
				exit(parse_page_source($content));
			// Printable: Sends a printable version of the page
			if(isset($_GET["printable"]) and $_GET["printable"] === "yes")
				exit(page_renderer::render_minimal($title, $content));
			// Normal page
			$settings->footer_message = "Last edited at " . date('h:ia T \o\n j F Y', $pageindex->{$env->page}->lastmodified) . ".</p>\n<p>"; // Add the last edited time to the footer
			exit(page_renderer::render_main($title, $content));
		});
	}
]);




register_module([
	"name" => "Parsedown",
	"version" => "0.8",
	"author" => "Emanuil Rusev & Starbeamrainbowlabs",
	"description" => "An upgraded (now default!) parser based on Emanuil Rusev's Parsedown Extra PHP library (https://github.com/erusev/parsedown-extra), which is licensed MIT. Please be careful, as this module adds some weight to your installation, and also *requires* write access to the disk on first load.",
	"id" => "parser-parsedown",
	"code" => function() {
		global $settings;
		
		$parser = new PeppermintParsedown();
		$parser->setInternalLinkBase("?page=%s");
		add_parser("parsedown", function($source) use ($parser) {
			global $settings;
			if($settings->clean_raw_html)
				$parser->setMarkupEscaped(true);
			else
				$parser->setMarkupEscaped(false);
			$result = $parser->text($source);
			
			return $result;
		});
		
		add_help_section("20-parser-default", "Editor Syntax",
		"<p>$settings->sitename's editor uses an extended version of <a href='http://parsedown.org/'>Parsedown</a> to render pages, which is a fantastic open source Github flavoured markdown parser. You can find a quick reference guide on Github flavoured markdown <a href='https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet'>here</a> by <a href='https://github.com/adam-p/'>adam-p</a>, or if you prefer a book <a href='https://www.gitbook.com/book/roachhd/master-markdown/details'>Mastering Markdown</a> by KB is a good read, and free too!</p>
		<h3>Extra Syntax</h3>
		<p>$settings->sitename's editor also supports some extra custom syntax, some of which is inspired by <a href='https://mediawiki.org/'>Mediawiki</a>.
		<table>
			<tr><th style='width: 40%'>Type this</th><th style='width: 20%'>To get this</th><th>Comments</th></th>
			<tr><td><code>[[Internal link]]</code></td><td><a href='?page=Internal%20link'>Internal Link</a></td><td>An internal link.</td></tr>
			<tr><td><code>[[Display Text|Internal link]]</code></td><td><a href='?page=Internal%20link'>Display Text</a></td><td>An internal link with some display text.</td></tr>
			<tr><td><code>![Alt text](http://example.com/path/to/image.png | 256x256 | right)</code></td><td><img src='http://example.com/path/to/image.png' alt='Alt text' style='float: right; max-width: 256px; max-height: 256px;' /></td><td>An image floating to the right of the page that fits inside a 256px x 256px box, preserving aspect ratio.</td></tr>
			<tr><td><code>![Alt text](http://example.com/path/to/image.png | 256x256 | caption)</code></td><td><figure><img src='http://example.com/path/to/image.png' alt='Alt text' style='max-width: 256px; max-height: 256px;' /><figcaption>Alt text</figcaption></figure></td><td>An image with a caption that fits inside a 256px x 256px box, preserving aspect ratio. The caption is taken from the alt text.</td></tr>
			<tr><td><code>![Alt text](Files/Cheese.png)</code></td><td><img src='index.php?action=preview&page=Files/Cheese.png' alt='Alt text' style='' /></td><td>An example of the short url syntax for images. Simply enter the page name of an image (or video / audio file), and Pepperminty Wiki will sort out the url for you.</td></tr>
		</table>
		<p>Note that the all image image syntax above can be mixed and matched to your liking. The <code>caption</code> option in particular must come last or next to last.</p>
		<h4>Templating</h4>
		<p>$settings->sitename also supports including one page in another page as a <em>template</em>. The syntax is very similar to that of Mediawiki. For example, <code>{{Announcement banner}}</code> will include the contents of the \"Announcement banner\" page, assuming it exists.</p>
		<p>You can also use variables. Again, the syntax here is very similar to that of Mediawiki - they can be referenced in the included page by surrrounding the variable name in triple curly braces (e.g. <code>{{{Announcement text}}}</code>), and set when including a page with the bar syntax (e.g. <code>{{Announcement banner | importance = high | text = Maintenance has been planned for tonight.}}</code>). Currently the only restriction in templates and variables is that you may not include a closing curly brace (<code>}</code>) in the page name, variable name, or value.</p>
		<h5>Special Variables</h5>
		<p>$settings->sitename also supports a number of special built-in variables. Their syntax and function are described below:</p>
		<table>
			<tr><th>Type this</th><th>To get this</th></tr>
			<tr><td><code>{{{@}}}</code></td><td>Lists all variables and their values in a table.</td></tr>
			<tr><td><code>{{{#}}}</code></td><td>Shows a 'stack trace', outlining all the parent includes of the current page being parsed.</td></tr>
			<tr><td><code>{{{~}}}</code></td><td>Outputs the requested pagee's name.</td></tr>
			<tr><td><code>{{{*}}}</code></td><td>Outputs a comma separated list of all the subpages of the current page.</td></tr>
			<tr><td><code>{{{+}}}</code></td><td>Shows a gallery containing all the files that are sub pages of the current page.</td></tr>
		</table>");
	}
]);

/*** Parsedown versions ***
 * Parsedown Core: 1.6.0  *
 * Parsedown Extra: 0.7.0 *
 **************************/
$env->parsedown_paths = new stdClass();
$env->parsedown_paths->parsedown = "https://cdn.rawgit.com/erusev/parsedown/3ebbd730b5c2cf5ce78bc1bf64071407fc6674b7/Parsedown.php";
$env->parsedown_paths->parsedown_extra = "https://cdn.rawgit.com/erusev/parsedown-extra/11a44e076d02ffcc4021713398a60cd73f78b6f5/ParsedownExtra.php";

// Download parsedown and parsedown extra if they don't already exist
if(!file_exists("./Parsedown.php") || filesize("./Parsedown.php") === 0)
	file_put_contents("./Parsedown.php", fopen($env->parsedown_paths->parsedown, "r"));
if(!file_exists("./ParsedownExtra.php") || filesize("./ParsedownExtra.php") === 0)
	file_put_contents("./ParsedownExtra.php", fopen($env->parsedown_paths->parsedown_extra, "r"));

require_once("./Parsedown.php");
require_once("./ParsedownExtra.php");

/*
 * ██████   █████  ██████  ███████ ███████ ██████   ██████  ██     ██ ███    ██
 * ██   ██ ██   ██ ██   ██ ██      ██      ██   ██ ██    ██ ██     ██ ████   ██
 * ██████  ███████ ██████  ███████ █████   ██   ██ ██    ██ ██  █  ██ ██ ██  ██
 * ██      ██   ██ ██   ██      ██ ██      ██   ██ ██    ██ ██ ███ ██ ██  ██ ██
 * ██      ██   ██ ██   ██ ███████ ███████ ██████   ██████   ███ ███  ██   ████
 * 
 * ███████ ██   ██ ████████ ███████ ███    ██ ███████ ██  ██████  ███    ██ ███████
 * ██       ██ ██     ██    ██      ████   ██ ██      ██ ██    ██ ████   ██ ██
 * █████     ███      ██    █████   ██ ██  ██ ███████ ██ ██    ██ ██ ██  ██ ███████
 * ██       ██ ██     ██    ██      ██  ██ ██      ██ ██ ██    ██ ██  ██ ██      ██
 * ███████ ██   ██    ██    ███████ ██   ████ ███████ ██  ██████  ██   ████ ███████
*/
class PeppermintParsedown extends ParsedownExtra
{
	private $internalLinkBase = "./%s";
	
	protected $maxParamDepth = 0;
	protected $paramStack = [];
	
	function __construct()
	{
		// Prioritise our internal link parsing over the regular link parsing
		array_unshift($this->InlineTypes["["], "InternalLink");
		// Prioritise our image parser over the regular image parser
		array_unshift($this->InlineTypes["!"], "ExtendedImage");
		
		$this->inlineMarkerList .= "{";
		if(!isset($this->InlineTypes["{"]) or !is_array($this->InlineTypes["{"]))
			$this->InlineTypes["{"] = [];
		$this->InlineTypes["{"][] = "Template";
	}
	
	/*
	 * ████████ ███████ ███    ███ ██████  ██       █████  ████████ ██ ███    ██  ██████
	 *    ██    ██      ████  ████ ██   ██ ██      ██   ██    ██    ██ ████   ██ ██
	 *    ██    █████   ██ ████ ██ ██████  ██      ███████    ██    ██ ██ ██  ██ ██   ███
	 *    ██    ██      ██  ██  ██ ██      ██      ██   ██    ██    ██ ██  ██ ██ ██    ██
	 *    ██    ███████ ██      ██ ██      ███████ ██   ██    ██    ██ ██   ████  ██████
	 */
	protected function inlineTemplate($fragment)
	{
		global $env, $pageindex;
		
		// Variable parsing
		if(preg_match("/\{\{\{([^}]+)\}\}\}/", $fragment["text"], $matches))
		{
			$params = [];
			if(!empty($this->paramStack))
			{
				$stackEntry = array_slice($this->paramStack, -1)[0];
				$params = !empty($stackEntry) ? $stackEntry["params"] : false;
			}
			
			$variableKey = trim($matches[1]);
			
			$variableValue = false;
			switch ($variableKey)
			{
				case "@": // Lists all variables and their values
					if(!empty($params))
					{
						$variableValue = "<table>
	<tr><th>Key</th><th>Value</th></tr>\n";
						foreach($params as $key => $value)
						{
							$variableValue .= "\t<tr><td>" . $this->escapeText($key) . "</td><td>" . $this->escapeText($value) . "</td></tr>\n";
						}
						$variableValue .= "</table>";
					}
					break;
				case "#": // Shows a stack trace
					$variableValue = "<ol start=\"0\">\n";
					$variableValue .= "\t<li>$env->page</li>\n";
					foreach($this->paramStack as $curStackEntry)
					{
						$variableValue .= "\t<li>" . $curStackEntry["pagename"] . "</li>\n";
					}
					$variableValue .= "</ol>\n";
					break;
				case "~": // Show requested page's name
					if(!empty($this->paramStack))
						$variableValue = $this->escapeText($env->page);
					break;
				case "*": // Lists subpages
					$subpages = get_subpages($pageindex, $env->page);
					$variableValue = [];
					foreach($subpages as $pagename => $depth)
					{
						$variableValue[] = $pagename;
					}
					$variableValue = implode(", ", $variableValue);
					if(strlen($variableValue) === 0)
						$variableValue = "<em>(none yet!)</em>";
					break;
				case "+": // Shows a file gallery for subpages with files
					// If the upload module isn't present, then there's no point
					// in checking for uploaded files
					if(!module_exists("feature-upload"))
						break;
					
					$variableValue = [];
					$subpages = get_subpages($pageindex, $env->page);
					foreach($subpages as $pagename => $depth)
					{
						// Make sure that this is an uploaded file
						if(!$pageindex->$pagename->uploadedfile)
							continue;
						
						$mime_type = $pageindex->$pagename->uploadedfilemime;
						
						$previewSize = 300;
						$previewUrl = "?action=preview&size=$previewSize&page=" . rawurlencode($pagename);
						
						$previewHtml = "";
						switch(substr($mime_type, 0, strpos($mime_type, "/")))
						{
							case "video":
								$previewHtml .= "<video src='$previewUrl' controls preload='metadata'>$pagename</video>\n";
								break;
							case "audio":
								$previewHtml .= "<audio src='$previewUrl' controls preload='metadata'>$pagename</audio>\n";
								break;
							case "application":
							case "image":
							default:
								$previewHtml .= "<img src='$previewUrl' />\n";
								break;
						}
						$previewHtml = "<a href='?page=" . rawurlencode($pagename) . "'>$previewHtml$pagename</a>";
						
						$variableValue[$pagename] = "<li style='min-width: $previewSize" . "px; min-height: $previewSize" . "px;'>$previewHtml</li>";
					}
					
					if(count($variableValue) === 0)
						$variableValue["default"] = "<li><em>(No files found)</em></li>\n";
					$variableValue = implode("\n", $variableValue);
					$variableValue = "<ul class='file-gallery'>$variableValue</ul>";
					break;
			}
			if(isset($params[$variableKey]))
			{
				$variableValue = $params[$variableKey];
				$variableValue = $this->escapeText($variableValue);
			}
			
			if($variableValue !== false)
			{
				return [
					"extent" => strlen($matches[0]),
					"markup" => $variableValue
				];
			}
		}
		else if(preg_match("/\{\{([^}]+)\}\}/", $fragment["text"], $matches))
		{
			$templateElement = $this->templateHandler($matches[1]);
			
			if(!empty($templateElement))
			{
				return [
					"extent" => strlen($matches[0]),
					"element" => $templateElement
				];
			}
		}
	}
	
	protected function templateHandler($source)
	{
		global $pageindex, $env;
		
		
		$parts = explode("|", trim($source, "{}"));
		$parts = array_map("trim", $parts);
		
		// Extract the name of the template page
		$templatePagename = array_shift($parts);
		// If the page that we are supposed to use as the tempalte doesn't
		// exist, then there's no point in continuing.
		if(empty($pageindex->$templatePagename))
			return false;
		
		// Parse the parameters
		$this->maxParamDepth++;
		$params = [];
		$i = 0;
		foreach($parts as $part)
		{
			if(strpos($part, "=") !== false)
			{
				// This param contains an equals sign, so it's a named parameter
				$keyValuePair = explode("=", $part, 2);
				$keyValuePair = array_map("trim", $keyValuePair);
				$params[$keyValuePair[0]] = $keyValuePair[1];
			}
			else
			{
				// This isn't a named parameter
				$params["$i"] = trim($part);
				
				$i++;
			}
		}
		// Add the parsed parameters to the parameter stack
		$this->paramStack[] = [
			"pagename" => $templatePagename,
			"params" => $params
		];
		
		$templateFilePath = $env->storage_prefix . $pageindex->$templatePagename->filename;
		
		$parsedTemplateSource = $this->text(file_get_contents($templateFilePath));
		
		// Remove the parsed parameters from the stack
		array_pop($this->paramStack);
		
		return [
			"name" => "div",
			"text" => $parsedTemplateSource,
			"attributes" => [
				"class" => "template"
			]
		];
	}
	
	protected function inlineInternalLink($fragment)
	{
		global $pageindex;
		
		if(preg_match('/^\[\[([^\]]*)\]\]/', $fragment["text"], $matches))
		{
			$display = $linkPage = $matches[1];
			if(strpos($matches[1], "|"))
			{
				// We have a bar character
				$parts = explode("|", $matches[1], 2);
				$linkPage = $parts[0];
				$display = $parts[1];
			}
			
			// Construct the full url
			$linkUrl = str_replace(
				"%s", rawurlencode($linkPage),
				$this->internalLinkBase
			);
			
			$result = [
				"extent" => strlen($matches[0]),
				"element" => [
					"name" => "a",
					"text" => $display,
					"attributes" => [
						"href" => $linkUrl
					]
				]
			];
			
			if(empty($pageindex->$linkPage))
				$result["element"]["attributes"]["class"] = "redlink";
			
			return $result;
		}
		return;
	}
	
	protected function inlineExtendedImage($fragment)
	{
		global $pageindex;
		///^!\[(.*)\]\(([^ |)]+)\s*(?:\|([^|)]*)(?:\|([^)]*))?)?\)/
		if(preg_match('/^!\[(.*)\]\(([^ |)]+)\s*(?:\|([^|)]*))?(?:\|([^|)]*))?(?:\|([^)]*))?\)/', $fragment["text"], $matches))
		{
			/*
			 * 0 - Everything
			 * 1 - Alt text
			 * 2 - Url
			 * 3 - First param (optional)
			 * 4 - Second param (optional)
			 * 5 - Third param (optional)
			 */
			$altText = $matches[1];
			$imageUrl = str_replace("&amp;", "&", $matches[2]); // Decode & to allow it in preview urls
			$param1 = empty($matches[3]) ? false : strtolower(trim($matches[3]));
			$param2 = empty($matches[4]) ? false : strtolower(trim($matches[4]));
			$param3 = empty($matches[5]) ? false : strtolower(trim($matches[5]));
			$floatDirection = false;
			$imageSize = false;
			$imageCaption = false;
			
			if($this->isFloatValue($param1))
			{
				// Param 1 is a valid css float: ... value
				$floatDirection = $param1;
				$imageSize = $this->parseSizeSpec($param2);
			}
			else if($this->isFloatValue($param2))
			{
				// Param 2 is a valid css float: ... value
				$floatDirection = $param2;
				$imageSize = $this->parseSizeSpec($param1);
			}
			else if($this->isFloatValue($param3))
			{
				$floatDirection = $param3;
				$imageSize = $this->parseSizeSpec($param1);
			}
			else if($param1 === false and $param2 === false)
			{
				// Neither params were specified
				$floatDirection = false;
				$imageSize = false;
			}
			else
			{
				// Neither of them are floats, but at least one is specified
				// This must mean that the first param is a size spec like
				// 250x128.
				$imageSize = $this->parseSizeSpec($param1);
			}
			
			if($param1 !== false && strtolower(trim($param1)) == "caption")
				$imageCaption = true;
				if($param2 !== false && strtolower(trim($param2)) == "caption")
					$imageCaption = true;
			if($param3 !== false && strtolower(trim($param3)) == "caption")
				$imageCaption = true;
			
			if(isset($pageindex->$imageUrl) and $pageindex->$imageUrl->uploadedfile)
			{
				// We have a short url! Expand it.
				$imageUrl = "index.php?action=preview&size=" . max($imageSize["x"], $imageSize["y"]) ."&page=" . rawurlencode($imageUrl);
			}
			
			$style = "";
			if($imageSize !== false)
				$style .= " max-width: " . $imageSize["x"] . "px; max-height: " . $imageSize["y"] . "px;";
			if($floatDirection)
				$style .= " float: $floatDirection;";
			
			$urlExtension = pathinfo($imageUrl, PATHINFO_EXTENSION);
			$urlType = system_extension_mime_type($urlExtension);
			$result = [];
			switch(substr($urlType, 0, strpos($urlType, "/")))
			{
				case "audio":
					$result = [
						"extent" => strlen($matches[0]),
						"element" => [
							"name" => "audio",
							"text" => $altText,
							"attributes" => [
								"src" => $imageUrl,
								"controls" => "controls",
								"preload" => "metadata",
								"style" => trim($style)
							]
						]
					];
					break;
				case "video":
					$result = [
						"extent" => strlen($matches[0]),
						"element" => [
							"name" => "video",
							"text" => $altText,
							"attributes" => [
								"src" => $imageUrl,
								"controls" => "controls",
								"preload" => "metadata",
								"style" => trim($style)
							]
						]
					];
					break;
				case "image":
				default:
					// If we can't work out what it is, then assume it's an image
					$result = [
						"extent" => strlen($matches[0]),
						"element" => [
							"name" => "img",
							"attributes" => [
								"src" => $imageUrl,
								"alt" => $altText,
								"title" => $altText,
								"style" => trim($style)
							]
						]
					];
					break;
			}
			
			if($imageCaption)
			{
				$rawStyle = $result["element"]["attributes"]["style"];
				$containerStyle = preg_replace('/^.*float/', "float", $rawStyle);
				$mediaStyle = preg_replace('/\s*float.*;/', "", $rawStyle);
				$result["element"] = [
					"name" => "figure",
					"text" => [
						$result["element"],
						[
							"name" => "figcaption",
							"text" => $altText
						],
					],
					"attributes" => [
						"style" => $containerStyle
					],
					"handler" => "elements"
				];
				$result["element"]["text"][0]["attributes"]["style"] = $mediaStyle;
			}
			return $result;
		}
	}
	
	# ~
	# Utility Methods
	# ~
	
	private function isFloatValue($value)
	{
		return in_array(strtolower($value), [ "left", "right" ]);
	}
	
	private function parseSizeSpec($text)
	{
		if(strpos($text, "x") === false)
			return false;
		$parts = explode("x", $text, 2);
		
		if(count($parts) != 2)
			return false;
		
		array_map("trim", $parts);
		array_map("intval", $parts);
		
		if(in_array(0, $parts))
			return false;
		
		return [
			"x" => $parts[0],
			"y" => $parts[1]
		];
	}
	
	protected function escapeText($text)
	{
		return htmlentities($text, ENT_COMPAT | ENT_HTML5);
	}
	
	/**
	 * Sets the base url to be used for internal links. '%s' will be replaced
	 * with a URL encoded version of the page name.
	 * @param string $url The url to use when parsing internal links.
	 */
	public function setInternalLinkBase($url)
	{
		$this->internalLinkBase = $url;
	}
}



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
