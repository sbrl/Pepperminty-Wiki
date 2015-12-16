<?php
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

// Whether to require that users login before they do anything else. If you
// combine this setting with the `data_storage_dir` option to move the storage
// directory outside your web root, this will completely hide your wiki from
// anyone who isn't logged in.
$settings->require_login_view = false;

// The directory in which to store all files, except this main index.php.
// A single dot ('.') denotes the current directory.
// Remember to omit the trailing slash from the directory name, as it is added
// automatically by Pepperminty Wiki.
// Note that this setting is currently experimental.
$settings->data_storage_dir = ".";

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
// protected. Defaults to a lock symbol. (&#128274;)
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
	[ "All&nbsp;Tags", "index.php?action=list-tags" ],
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
.page-tags-display li::before { content: \"\\A\"; position: relative; top: 0.03rem; left: -0.9rem; width: 0; height: 0; border-top: 0.6rem solid transparent; border-bottom: 0.6rem solid transparent; border-right: 0.5rem solid #D2C3DD; }

.page-list { list-style-type: none; margin: 0.5rem; padding: 0.5rem; }
.page-list li { margin: 0.5rem; padding: 0.5rem; }
.page-list li .size { color: rgba(30, 30, 30, 0.5); }
.page-list li .editor { display: inline-block; margin: 0 0.5rem; }
.page-list li .tags { margin: 0 1rem; }
.tag-list { list-style-type: none; margin: 0.5rem; padding: 0.5rem; }
.tag-list li { margin: 1rem }
.mini-tag { background: #d2c3dd; padding: 0.2rem 0.4rem; color: #fb701a; text-decoration: none; }

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
?>
