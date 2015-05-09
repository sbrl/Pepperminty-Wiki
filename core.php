<?php
$start_time = time(true);

{settings}

///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "0.5";
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
if(!file_exists("./pageindex.json"))
{
	$existingpages = glob("*.md");
	$pageindex = new stdClass();
	foreach($existingpages as $pagefilename)
	{
		$newentry = new stdClass();
		$newentry->filename = utf8_encode($pagefilename);
		$newentry->size = filesize($pagefilename);
		$newentry->lastmodified = filemtime($pagefilename);
		$newentry->lasteditor = utf8_encode("unknown");
		$pagekey = utf8_encode(substr($pagefilename, 0, -3));
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
function makepathsafe($string) { return preg_replace("/[^0-9a-zA-Z\_\-\ ]/i", "", $string); }

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

//Work around an Opera + Syntastic bug where there is no margin at the left hand side if there isn't a query string when accessing a .php file
if(!isset($_GET["action"]) and !isset($_GET["page"]))
{
	http_response_code(302);
	header("location: index.php?action=$settings->defaultaction&page=$defaultpage");
	exit();
}

//make sure that the action is set
if(!isset($_GET["action"]))
	$_GET["action"] = "view";

if(!isset($_GET["page"]) or strlen($_GET["page"]) === 0)
	$_GET["page"] = $settings->defaultpage;

//redirect the user to the safe version of the path if they entered an unsafe character
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
function renderpage($title, $content, $minimal = false)
{
	global $settings, $page, $user, $isloggedin, $isadmin, $start_time, $pageindex;
	
	$html = "<!DOCTYPE HTML>
<html><head>
	<meta charset='utf-8' />
	<title>$title</title>
	<meta name=viewport content='width=device-width, initial-scale=1' />
	<link rel='shortcut icon' href='$settings->favicon' />";
	if(preg_match("/^[^\/]*\/\/|^\//", $settings->css))
	{
		$html .= "\n\t\t<link rel='stylesheet' href='$settings->css' />\n";
	}
	else
	{
		$html .= "\n\t\t<style>$settings->css</style>\n";
	}
	$html .= "</head><body>\n";
	
	//////////
	
	if($minimal)
	{
		$html .= "$content
	<hr class='footerdivider' />
	<p><em>From $settings->sitename, which is managed by " . $settings->admindetails["name"] . ".</em></p>
	<p><em>Timed at " . date("l jS \of F Y \a\\t h:ia T") . ".</em></p>
	<p><em>Powered by Pepperminty Wiki</em></p>";
	}
	else
	{
		$html .= "<nav>\n";
	
		if($isloggedin)
		{
			$html .= "\t\tLogged in as ";
			if($isadmin)
				$html .= $settings->admindisplaychar;
			$html .= "$user. <a href='index.php?action=logout'>Logout</a>. | \n";

		}
		else
			$html .= "\t\tBrowsing as Anonymous. <a href='index.php?action=login'>Login</a>. | \n";

		foreach($settings->navlinks as $item)
		{
			if(is_string($item))
			{
				//the item is a string
				switch($item)
				{
					//keywords
					case "search": //displays a search bar
						$html .= "<form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /></form>";
						break;

					//it isn't a keyword, so just output it directly
					default:
						$html .= $item;
				}
			}
			else
			{
				//output the display as a link to the url
				$html .= "\t\t<a href='" . str_replace("{page}", $page, $item[1]) . "'>$item[0]</a>\n";
			}
		}

		$html .= "	</nav>
	<h1 class='sitename'>$settings->sitename</h1>
	$content
	<hr class='footerdivider' />
	<footer>
		<p>Powered by Pepperminty Wiki, which was built by <a href='//starbeamrainbowlabs.com/'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or open an issue <a href='//github.com/sbrl/Pepperminty-Wiki'>on github</a>.</p>
		<p>Your local friendly administrators are " . implode(", ", $settings->admins) . ".
		<p>This wiki is managed by <a href='mailto:" . hide_email($settings->admindetails["email"]) . "'>" . $settings->admindetails["name"] . "</a>.</p>
	</footer>
	<datalist id='allpages'>\n";
		
		foreach($pageindex as $pagename => $pagedetails)
		{
			$html .= "\t\t<option value='$pagename' />\n";
		}
		$html .= "\t</datalist>";
	}
	
	//////////
	$gentime = microtime(true) - $start_time;
	$html .= "\n\t<!-- Took $gentime seconds to generate -->
</body></html>";
	
	return $html;
}

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
 */
class Slimdown {
	public static $rules = array (
		'/\r\n/' => "\n",											// new line normalisation
		'/(#+)(.*)/' => 'self::header',								// headers
		'/(\*)(.*?)\1/' => '<strong>\2</strong>',					// bold
		'/(_)(.*?)\1/' => '<em>\2</em>',							// emphasis
		'/\[\[([a-zA-Z0-9\_\- ]+)\|([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\2</a>',	//internal links with display text
		'/\[\[([a-zA-Z0-9\_\- ]+)\]\]/' => '<a href=\'index.php?page=\1\'>\1</a>',	//internal links
		'/\[([^\[]+)\]\(([^\)]+)\)/' => '<a href=\'\2\' target=\'_blank\'>\1</a>',	// links
		'/\~\~(.*?)\~\~/' => '<del>\1</del>',						// del
		'/\:\"(.*?)\"\:/' => '<q>\1</q>',							// quote
		'/`(.*?)`/' => '<code>\1</code>',							// inline code
		'/\n\s*(\*|-)(.*)/' => 'self::ul_list',							// ul lists
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
$modules = []; // list that contains all the loaded modules
// function to register a module
function register_module($moduledata)
{
	global $modules;
	//echo("registering module\n");
	//var_dump($moduledata);
	$modules[] = $moduledata;
}

// function to register an action handler
$actions = new stdClass();
function add_action($action_name, $func)
{
	global $actions;
	//echo("adding $action_name\n");
	$actions->$action_name = $func;
}

//////////////////////////////////////////////////////////////////


// %next_module% //


// execute each module's code
foreach($modules as $moduledata)
{
	$moduledata["code"]();
}
// make sure that the credits page exists
if(!isset($actions->credits))
{
	exit(renderpage("Error - $settings->$sitename", "<p>No credits page detected. The credits page is a required module!</p>"));
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
	exit(renderpage("Error - $settings->sitename", ",p>No action called " . strtolower($_GET["action"]) ." has been registered. Perhaps you are missing a module?</p>"));
}
?>
