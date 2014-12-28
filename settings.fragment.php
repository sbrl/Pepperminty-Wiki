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
 */

//the site's name
$sitename = "Pepperminty Wiki";

//the url from which to fetch updates. Defaults to the master (development) branch If there is sufficient demand, a separate stable branch will be created.
//currently not implemented (yet).
$updateurl = "https://raw.githubusercontent.com/sbrl/pepperminty-wiki/master/index.php";

//the secret key used to perform 'dangerous' actions, like updating the wiki, and deleting pages. It is strongly advised that you change this!
//note that neither of these features have been added yet.
$sitesecret = "ed420502615bac9037f8f12abd4c9f02";

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

//array of usernames that are administrators.
//administrators can delete and move pages, though this functionality hasn't been added yet.
$admins = [ "admin" ];

//The string that is prepended before an admin's name on the nav bar. defaults to a diamond shape (&#9670;).
$admindisplaychar = "&#9670;";

//contact details for the site administrator. Since user can only be added by editing this file, people will need a contact address to use to ask for an account. Displayed at the bottom of the page, and will be appropriatly obfusticateed to  deter spammers.
$admindetails = [
	"name" => "Administrator",
	"email" => "admin@localhost"
];

//array of links and display text to display at the top of the site
$navlinks = [
	[ "Home", "index.php" ],
	[ "Login", "index.php?action=login" ],
	" | ",
	"search",
	" | ",
	[ "Read", "index.php?page={page}" ],
	[ "Edit", "index.php?action=edit&page={page}" ],
	[ "Printable", "index.php?action=view&printable=yes&page={page}" ],
	" | ",
	[ $admindisplaychar . "Delete", "index.php?action=delete&page={page}" ],
	" | ",
	[ "All Pages", "index.php?action=list" ],
	" | ",
	[ "Credits", "index.php?action=credits" ],
	[ "Help", "index.php?action=help" ]
];

//string of css to include
//may be a url - urls will be referenced via a <link rel='stylesheet' /> tag
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
		page - page name
		printable=[yes/no] - make output printable
	edit - open editor for page
		page - page name
	save - save edits to page
		page - page name
	list - list pages
		category - the category to list [optional]
	login - login to the site
	logout - logout
	checklogin - check login credentials and set cookie
	hash - hash a string with sha256
		string - string to hash
	help - get help
	credits - view the credits
	delete - delete a page
		page - page name
		delete=yes - actually do the deletion (otherwise we display a prompt)
*/
?>
