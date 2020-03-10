<?php
///////////////////////////////////////////////////////////////////////////////
////////////////////// Security and Consistency Measures //////////////////////
///////////////////////////////////////////////////////////////////////////////

// Work around an Opera + Syntaxtic bug where there is no margin at the left
// hand side if there isn't a query string when accessing a .php file.
if(!is_cli() && !isset($_GET["action"]) && !isset($_GET["page"]) && basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) == "index.php")
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
