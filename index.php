<?php
$start_time = time(true);
/*
 * Pepperminty Wiki
 * ================
 * Inspired by Minty Wiki by am2064:
	* Link: https://github.com/am2064/Minty-Wiki
 * 
 * Credits:
	* Slimdown - by Johnny Broadway from https://gist.github.com/jbroadway/2836900
 */

//the site's name
$sitename = "Pepperminty Wiki";

//whether people can edit the site
$editing = true;

//the maximum number of characters allowed in a single page
$maxpagesize = 135000; //135,000 characters, or 50 pages

//whether users who aren't logged in are allowed to edit
$anonedits = false;

//the name of the page that will act as the home pae for the wiki. This page will be served if the user didn't specify a page.
$defaultpage = "Main Page";

//usernames and passwords - passwords should be hashed with sha256
//even though there is an account with the name admin here it doesn't actually get any special privileges, so feel free to change / remove it - this will help to stop spambots
//the same goes for the account with the name user
$users = [
	"admin" => "5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8", //password
	"user" => "873ac9ffea4dd04fa719e8920cd6938f0c23cd678af330939cff53c3d2855f34" //cheese
];

//array of links and display text to display at the top of the site
$navlinks = [
	"Home" => "index.php",
	"Login" => "index.php?action=login",
	" | " => "",
	"{search}" => "",
	" |  " => "",
	"Read" => "index.php?page={page}",
	"Edit" => "index.php?action=edit&page={page}",
	"Printable" => "index.php?action=view&printable=yes&page={page}",
	"  |  " => "",
	"All Pages" => "index.php?action=list",
	"  |  " => "",
	"Credits" => "index.php?action=credits",
	"Help" => "index.php?action=help"
];

//contact details for the site administrator. Since user can only be added by editing this file, people will need a contact address to use to ask for an account. Displayed at the bottom of the page, and will be appropriatly obfusticateed to  deter spammers.
$admindetails = [
	"name" => "Administrator",
	"email" => "admin@localhost"
];

//string of css to include
$css = "body { font-family: sans-serif; color: #333333; background: #f3f3f3; }
textarea[name=content] { display: block; width: 100%; height: 35rem; }
input[name=page] { width: 16rem; }
nav { position: absolute; top: 5px; right: 5px; }
th { text-align: left; }
.sitename { text-align: center; font-size: 2.5rem; color: #222222; }
.footerdivider { margin-top: 4rem; }";
//the favicon
//default: peppermint from https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23
$favicon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAB3VBMVEXhERHbKCjeVVXjb2/kR0fhKirdHBziDg6qAADaHh7qLy/pdXXUNzfMAADYPj7ZPDzUNzfbHx/fERHpamrqMTHgExPdHx/bLCzhLS3fVFTjT0/ibm7kRkbiLi7aKirdISHeFBTqNDTpeHjgERHYJCTVODjYQkLaPj6/AADVOTnpbW3cIyPdFRXcJCThMjLiTU3ibW3fVVXaKyvcERH4ODj+8fH/////fHz+Fxf4KSn0UFD/CAj/AAD/Xl7/wMD/EhL//v70xMT/+Pj/iYn/HBz/g4P/IyP/Kyv/7Oz0QUH/9PT/+vr/ior/Dg7/vr7/aGj/QED/bGz/AQH/ERH/Jib/R0f/goL/0dH/qan/YWH/7e3/Cwv4R0f/MTH/enr/vLz/u7v/cHD/oKD/n5//aWn+9/f/k5P/0tL/trb/QUH/cXH/dHT/wsL/DQ3/p6f/DAz/1dX/XV3/kpL/i4v/Vlb/2Nj/9/f/pKT+7Oz/V1f/iIj/jIz/r6//Zmb/lZX/j4//T0//Dw/4MzP/GBj/+fn/o6P/TEz/xMT/b2//Tk7/OTn/HR3/hIT/ODj/Y2P/CQn/ZGT/6Oj0UlL/Gxv//f3/Bwf/YmL/6+v0w8P/Cgr/tbX0QkL+9fX4Pz/qNzd0dFHLAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfeCxINNSdmw510AAAA5ElEQVQYGQXBzSuDAQCA8eexKXOwmSZepa1JiPJxsJOrCwcnuchBjg4O/gr7D9zk4uAgJzvuMgcTpYxaUZvSm5mUj7TX7ycAqvoLIJBwStVbP0Hom1Z/ejoxrbaR1Jz6nWinbKWttGRgMSSjanPktRY6mB9WtRNTn7Ilh7LxnNpKq2/x5LnBitfz+hx0qxUaxhZ6vwqq9bx6f2XXvuUl9SVQS38NR7cvln3v15tZ9bQpuWDtZN3Lgh5DWJex3Y+z1KrVhw21+CiM74WZo83DiXq0dVBDYNJkFEU7WrwDAZhRtQrwDzwKQbT6GboLAAAAAElFTkSuQmCC";

//the prefix that should be used in the cookie names
//defaults to an all lower case version of the site name with all non alphanumeric characters removed
//remember that changing this will log everyone out since the login cookie's name will have changed
$cookieprefix = preg_replace("/[^0-9a-z]/i", "", strtolower($sitename));

/*
Actions:
	view - view a page
		page - path to page
		printable=[yes/no] - make output printable
	edit - open editor for page
		page - path to page
	save - save edits to page
		page - path to page
	list - list pages
		category - the category to list [optional]
	login - login to the site
	logout - logout
	checklogin - check login credentials and set cookie
	hash - hash a string with sha256
		string - string to hash
	help - get help
	credits - view the credits
*/

///////////////////////////////////////////////////////////////////////////////////////////////
/////////////// Do not edit below this line unless you know what you are doing! ///////////////
///////////////////////////////////////////////////////////////////////////////////////////////
$version = "0.5";
///////// Login System /////////
if(!isset($_COOKIE[$cookieprefix . "-user"]) and
  !isset($_COOKIE[$cookieprefix . "-pass"]))
{
	//the user is not logged in
	$isloggedin = false;
}
else
{
	$user = $_COOKIE[$cookieprefix . "-user"];
	$pass = $_COOKIE[$cookieprefix . "-pass"];
	if($users[$user] == $pass)
	{
		//the user is logged in
		$isloggedin = true;
	}
	else
	{
		//the user's login details are invalid (what is going on here?)
		//unset the cookie and the variables, treat them as an anonymous user, and get out of here
		$isloggedin = false;
		unset($user);
		unset($pass);
		setcookie($cookieprefix . "-user", null, -1, "/");
		setcookie($cookieprefix . "-pass", null, -1, "/");
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
	header("location: index.php?action=view&page=$defaultpage");
	exit();
}

//make sure that the action is set
if(!isset($_GET["action"]))
	$_GET["action"] = "view";

if(!isset($_GET["page"]) or strlen($_GET["page"]) === 0)
	$_GET["page"] = $defaultpage;

//redirect the user to the safe version of the path if they entered an unsafe character
if(makepathsafe($_GET["page"]) !== $_GET["page"])
{
	http_response_code(301);
	header("location: index.php?action=" . rawurlencode($_GET["action"]) . "&page=" . makepathsafe($_GET["page"]));
	header("x-requested-page: " . $_GET["page"]);
	header("x-actual-page: " . makepathsafe($_GET["page"]));
	exit();
}

///////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// HTML fragments //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
function renderpage($title, $content, $minimal = false)
{
	global $sitename, $css, $favicon, $user, $isloggedin, $navlinks, $admindetails, $start_time, $pageindex;
	
	$html = "<!DOCTYPE HTML>
<html><head>
	<meta charset='utf-8' />
	<title>$title</title>
	<link rel='shortcut icon' href='$favicon' />
	<style>$css</style>
</head><body>
	";
	
	//////////
	
	if($minimal)
	{
		$html .= "$content
	<hr class='footerdivider' />
	<p><em>From $sitename, which is managed by " . $admindetails["name"] . ".</em></p>
	<p><em>Timed at " . date("l jS \of F Y \a\\t h:ia T") . ".</em></p>
	<p><em>Powered by Pepperminty Wiki</em></p>";
	}
	else
	{
		$html .= "<nav>\n";
	
		if($isloggedin)
			$html .= "\t\tLogged in as $user. <a href='index.php?action=logout'>Logout</a>. | \n";
		else
			$html .= "\t\tBrowsing as Anonymous. <a href='index.php?action=login'>Login</a>. | \n";

		foreach($navlinks as $display => $url)
		{
			if($display == "{search}")
			{
				//output a search bar
				$html .= "<form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='Type a page name here and hit enter' /></form>";
			}
			else if(strlen($url) === 0)
			{
				//the url has not been set, output $display directly
				$html .= " " . trim($display) . " ";
			}
			else
			{
				//output the display as a link to the url
				$html .= "\t\t<a href='" . str_replace("{page}", $_GET["page"], $url) . "'>$display</a>\n";
			}
		}
	
		$html .= "	</nav>
	<h1 class='sitename'>$sitename</h1>
	$content
	<hr class='footerdivider' />
	<footer>
		<p>Powered by Pepperminty Wiki, which was built by <a href='//starbeamrainbowlabs'>Starbeamrainbowlabs</a>. Send bugs to 'bugs at starbeamrainbowlabs dot com' or open an issue <a href='//github.com/sbrl/Pepperminty-Wiki'>on github</a>.</p>
		<p>This wiki is managed by <a href='mailto:" . hide_email($admindetails["email"]) . "'>" . $admindetails["name"] . "</a>.</p>
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
</head></html>";
	
	return $html;
}

////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////// Slimdown /////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////
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
		'/\n\*(.*)/' => 'self::ul_list',							// ul lists
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
		$item = $regs[1];
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
function human_filesize($bytes, $decimals = 2)
{
	$sz = 'BKMGTPEYZ';
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

switch($_GET["action"])
{
	case "edit":
		if(!$editing)
		{
			http_response_code(203);
			header("location: index.php?page=" . $_GET["page"]);
		}
		
		if(!$isloggedin and !$anonedits)
		{
			//future let the user view the page source instead
			http_response_code(403);
			header("refresh: 5; url=index.php?page=" . $_GET["page"]);
			header("content-type: text/plain");
			exit("You must be logged in to edit $sitename. Redirecting to page view in 5 seconds....");
		}
		$filename = $_GET["page"] . ".md";
		$creatingpage = !isset($pageindex->$_GET["page"]);
		if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
		{
			$title = "Creating " . $_GET["page"];
		}
		else
		{
			$title = "Editing " . $_GET["page"];
		}
		
		
		$pagetext = "";
		if(isset($pageindex->$_GET["page"]))
		{
			$pagetext = file_get_contents($filename);
		}
		
		$content = "<h1>$title</h1>";
		if(!$isloggedin and $anonedits)
		{
			$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
		}
		$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($_GET["page"]) . "&action=save'>
		<textarea name='content'>$pagetext</textarea>
		<input type='submit' value='Save Page' />
	</form>";
		exit(renderpage("$title - $sitename", $content));
		break;
	
	case "save":
		if(!$editing)
		{
			header("location: index.php?page=" . $_GET["page"]);
			exit(renderpage("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
		}
		if(!$isloggedin and !$anonedits)
		{
			http_response_code(403);
			header("refresh: 5; url=index.php?page=" . $_GET["page"]);
			exit("You are not logged in, so you are not allowed to save pages on $sitename. Redirecting in 5 seconds....");
		}
		if(!isset($_POST["content"]))
		{
			http_response_code(400);
			header("refresh: 5; url=index.php?page=" . $_GET["page"]);
			exit("Bad request: No content specified.");
		}
		if(file_put_contents($_GET["page"] . ".md", htmlentities($_POST["content"]), ENT_QUOTES) !== false)
		{
			//update the page index
			if(!isset($pageindex->$_GET["page"]))
			{
				$pageindex->$_GET["page"] = new stdClass();
				$pageindex->$_GET["page"]->filename = $_GET["page"] . ".md";
			}
			$pageindex->$_GET["page"]->size = strlen($_POST["content"]);
			$pageindex->$_GET["page"]->lastmodified = time();
			if($isloggedin)
				$pageindex->$_GET["page"]->lasteditor = utf8_encode($user);
			else
				$pageindex->$_GET["page"]->lasteditor = utf8_encode("anonymous");
			
			file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
			
			if(isset($_GET["newpage"]))
				http_response_code(201);
			else
				http_response_code(200);
			
			header("location: index.php?page=" . $_GET["page"]);
			exit();
		}
		else
		{
			http_response_code(507);
			exit(renderpage("Error saving page - $sitename", "<p>$sitename failed to write your changes to the disk. Your changes have not been saved, however you may be able to recover your edit by pressing the back button in your browser.</p>
			<p>Please tell the administrator of this wiki (" . $admindetails["name"] . ") about this problem.</p>"));
		}
		break;
	
	case "list":
		$title = "All Pages";
		$content = "	<h1>$title on $sitename</h1>
	<table>
		<tr>
			<th>Page Name</th>
			<th>Size</th>
			<th>Last Editor</th>
			<th>Last Edited</th>
			<th>Time Since Last Edit</th>
		</tr>\n";
		foreach($pageindex as $pagename => $pagedetails)
		{
			$content .= "\t\t<tr>
			<td><a href='index.php?page=$pagename'>$pagename</a></td>
			<td>" . human_filesize($pagedetails->size) . "</td>
			<td>$pagedetails->lasteditor</td>
			<td>" . date("l jS \of F Y \a\\t h:ia T", $pagedetails->lastmodified) . "</td>
			<td>" . human_time_since($pagedetails->lastmodified) . "</td>
		</tr>\n";
		}
		$content .= "	</table>";
		exit(renderpage("$title - $sitename", $content));
		break;
	
	case "help":
		$title = "Help - $sitename";
		$content = "	<h1>$sitename Help</h1>
	<p>Welcome to $sitename!</p>
	<p>$sitename is powered by Pepperminty wiki, a complete wiki in a box you can drop into your server.</p>
	<h2>Navigating</h2>
	<p>All the navigation links can be found in the top right corner, along with a box in which you can type a page name and hit enter to be taken to that page (if your site administrator has enabled it).</p>
	<p>In order to edit pages on $sitename, you probably need to be logged in. If you do not already have an account you will need to ask $sitename's administrator for an account since there is not registration form. Note that the $sitename's administrator may have changed these settings to allow anonymous edits.</p>
	<h2>Editing</h2>
	<p>$sitename's editor uses a modified version of slimdown, a flavour of markdown that is implementated using regular expressions. See the credits page for more information and links to the original source for this. A quick reference can be found below:</p>
	<table>
		<tr><th>Type This</th><th>To get this</th>
		<tr><td><code>_italics_</code></td><td><em>italics</em></td></tr>
		<tr><td><code>*bold*</code></td><td><strong>bold</strong></td></tr>
		<tr><td><code># Heading</code></td><td><h2>Heading</h2></td></tr>
		<tr><td><code>## Sub Heading</code></td><td><h3>Sub Heading</h3></td></tr>
		<tr><td><code>[[Internal Link]]</code></td><td><a href='index.php?page=Internal Link'>Internal Link</a></td></tr>
		<tr><td><code>[[Display Text|Internal Link]]</code></td><td><a href='index.php?page=Internal Link'>Display Text</a></td></tr>
		<tr><td><code>[Display text](//google.com/)</code></td><td><a href='//google.com/'>Display Text</a></td></tr>
		<tr><td><code>~~Strikethrough~~</code></td><td><del>Strikethough</del></td></tr>
		<tr><td><code>&gt; Blockquote<br />&gt; Some text</code></td><td><blockquote> Blockquote<br />Some text</td></tr>
		<tr><td><code>
	---
	</code></td><td><hr /></td></tr>
	</table>";
		exit(renderpage($title, $content));
		break;
	
	case "login":
		$title = "Login to $sitename";
		$content = "<h1>Login to $sitename</h1>\n";
		if(isset($_GET["failed"]))
			$content .= "\t\t<p><em>Login failed.</em></p>\n";
		$content .= "\t\t<form method='post' action='index.php?action=checklogin&returnto=" . rawurlencode($_SERVER['REQUEST_URI']) . "'><label for='user'>Username:</label>
			<input type='text' name='user' />
			<br />
			<label for='pass'>Password:</label>
			<input type='password' name='pass' />
			<input type='submit' value='Login' />
		</form>";
		exit(renderpage($title, $content));
		break;
	
	case "checklogin":
		if(isset($_POST["user"]) and isset($_POST["pass"]))
		{
			//the user wants to log in
			$user = $_POST["user"];
			$pass = $_POST["pass"];
			if($users[$user] == hash("sha256", $pass))
			{
				$isloggedin = true;
				$expiretime = time() + 60*60*24*30; //30 days from now
				setcookie($cookieprefix . "-user", $user, $expiretime, "/");
				setcookie($cookieprefix . "-pass", hash("sha256", $pass), $expiretime, "/");
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
		break;
	
	case "logout":
		$isloggedin = false;
		unset($user);
		unset($pass);
		setcookie($cookieprefix . "-user", null, -1, "/");
		setcookie($cookieprefix . "-pass", null, -1, "/");
		exit(renderpage("Logout Successful", "<h1>Logout Successful</h1>
	<p>Logout Successful. You can login again <a href='index.php?action=login'>here</a>.</p>"));
		break;
	
	case "credits":
		$title = "Credits - $sitename";
		$content = "<h1>$sitename credits</h1>
	<p>$sitename is powered by Pepperminty Wiki - an entire wiki packed inside a single file, which was built by <a href='//starbeamrainboowlabs.com'>Starbeamrainbowlabs</a>, and can be found <a href='//github.com/sbrl/Pepperminty-Wiki/'>on github</a>.</p>
	<p>A slightly modified version of slimdown is used to parse text source into HTML. Slimdown is by <a href='https://github.com/jbroadway'>Johnny Broadway</a>, which can be found <a href='https://gist.github.com/jbroadway/2836900'>on github</a>.</p>
	<p>The default favicon is from <a href='//openclipart.org'>Open Clipart</a> by bluefrog23, and can be found <a href='https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23'>here</a>.</p>";
		exit(renderpage($title, $content));
		break;
	
	case "hash":
		if(!isset($_GET["string"]))
		{
			http_response_code(400);
			exit(renderpage("Bad request", "<p>The <code>GET</code> parameter <code>string</code> must be specified.</p>
	<p>It is strongly recommended that you utilise this page via a private or incognito window.</p>"));
		}
		else
		{
			exit(renderpage("Hashed string", "<p><code>" . $_GET["string"] . "</code> → <code>" . hash("sha256", $_GET["string"] . "</code></p>")));
		}
		break;
	
	case "view":
	default:
		//check to make sure that the page exists
		if(!isset($pageindex->$_GET["page"]))
		{
			if($editing)
			{
				//editing is enabled, redirect to the editing page
				http_response_code(307); //temporary redirect
				header("location: index.php?action=edit&newpage=yes&page=" . rawurlencode($_GET["page"]));
				exit();
			}
			else
			{
				//editing is disabled, show an error message
				http_response_code(404);
				exit(renderpage("<p>" . $_GET["page"] . " - $sitename", $_GET["page"] . " does not exist.</p><p>Since editing is currently disabled on this wiki, you may not create this page. If you feel that this page should exist, try contacting this wiki's Administrator.</p>"));
			}
		}
		$title = $_GET["page"] . " - $sitename";
		$content = "<h1>" . $_GET["page"] . "</h1>";
		
		$slimdown_start = microtime(true);
		
		$content .= Slimdown::render(file_get_contents($_GET["page"] . ".md"));
		
		$content .= "\n\t<!-- Took " . (microtime(true) - $slimdown_start) . " seconds to parse markdown -->\n";
		
		if(isset($_GET["printable"]) and $_GET["printable"] === "yes")
			$minimal = true;
		else
			$minimal = false;
		exit(renderpage($title, $content, $minimal));
		break;
}
?>
