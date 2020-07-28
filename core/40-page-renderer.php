<?php

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
		<meta name='application-name' content='Pepperminty Wiki {version}' />
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
	 * @param  callable $function The part preprocessor to register.
	 */
	public static function register_part_preprocessor($function) {
		global $settings;

		// Make sure that the function we are about to register is valid
		if(!is_callable($function))
		{
			http_response_code(500);
			$admin_email = hide_email($settings->admindetails_email);
			exit(page_renderer::render("$settings->sitename - Module Error", "<p>$settings->sitename has got a misbehaving module installed that tried to register an invalid HTML handler with the page renderer. Please contact $settings->sitename's administrator {$settings->admindetails_name} at <a href='mailto:$admin_email'>$admin_email</a>."));
		}

		self::$part_processors[] = $function;

		return true;
	}
	
	/**
	 * Renders a HTML page with the content specified.
	 * @package core
	 * @param	string	$title			The title of the page.
	 * @param	string	$content		The (HTML) content of the page.
	 * @param	bool	$body_template	The HTML content template to use.
	 * @return	string	The rendered HTML, ready to send to the client :-)
	 */
	public static function render($title, $content, $body_template = false)
	{
		global $settings, $env, $start_time, $version;
		
		// Hrm, we can't seem to get this working
		// This example URL works: https://httpbin.org/response-headers?Server=httpbin&Content-Type=text%2Fplain%3B+charset%3DUTF-8&Server-Timing=sql-1%3Bdesc%3D%22MySQL%20lookup%20Server%22%3Bdur%3D100%2Csql-2%3Bdur%3D900%3Bdesc%3D%22MySQL%20shard%20Server%20%231%22%2Cfs%3Bdur%3D600%3Bdesc%3D%22FileSystem%22%2Ccache%3Bdur%3D300%3Bdesc%3D%22Cache%22%2Cother%3Bdur%3D200%3Bdesc%3D%22Database%20Write%22%2Cother%3Bdur%3D110%3Bdesc%3D%22Database%20Read%22%2Ccpu%3Bdur%3D1230%3Bdesc%3D%22Total%20CPU%22
		// ..... but setting headers here doesn't (though we haven't tried sending an identical header to the above example yet)
		// header("Server-Timing: foo;desc=\"Test\";dur=123");
		// header("Server-Timing: ".metrics2servertiming($env->perfdata));

		if($body_template === false)
			$body_template = self::$main_content_template;

		if(strlen($settings->logo_url) > 0) {
			// A logo url has been specified
			$logo_html = "<img aria-hidden='true' class='logo" . (isset($_GET["printable"]) ? " small" : "") . "' src='$settings->logo_url' />";
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
	 * @return int|false	The number of resource hints included in the link: header, or false if server pushing is disabled.
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
		
		if(!empty($settings->theme_colour))
			$result .= "\t\t<meta name='theme-color' content='$settings->theme_colour' />\n";
		
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
	 * Before v0.20, this method took no arguments and checked $settings->css directly.
	 * @apiVerion		0.20.0
	 * @param	string	$str	The CSS string to check.
	 * @return	bool	True if it's a url - false if we assume it's a string of css.
	 */
	public static function is_css_url($str) {
		global $settings;
		return preg_match("/^[^\/]*\/\/|^\/[^\*]/", $str);
	}
	/**
	 * Renders all the CSS as HTML.
	 * @package core
	 * @return string The css as HTML, ready to be included in the HTML header.
	 */
	public static function get_css_as_html()
	{
		global $settings, $defaultCSS;
		
		$result = "";
		$css = "";
		if(self::is_css_url($settings->css)) {
			if($settings->css[0] === "/") // Push it if it's a relative resource
				self::add_server_push_indicator("style", $settings->css);
			$result .= "<link rel='stylesheet' href='$settings->css' />\n";
		} else {
			$css .= $settings->css == "auto" ? $defaultCSS : $settings->css;
			
			if(!empty($settings->optimize_pages))
				$css = minify_css($css);
			
		}
		
		if(!empty($settings->css_custom)) {
			if(self::is_css_url($settings->css_custom)) {
				if($settings->css_custom[0] === "/") // Push it if it's a relative resource
					self::add_server_push_indicator("style", $settings->css);
				$result .= "<link rel='stylesheet' href='$settings->css_custom' />\n";
			}
			$css .= "\n/*** Custom CSS ***/\n";
			$css .= !empty($settings->optimize_pages) ? minify_css($settings->css_custom) : $settings->css_custom;
			$css .= "\n/******************/";
		}
		$result .= "<style>\n$css\n</style>\n";
		
		return $result;
	}
	
	
	/**
	 * Adds the specified url to a javascript file as a reference to the page.
	 * @package core
	 * @param string $scriptUrl The url of the javascript file to reference.
	 */
	public static function add_js_link(string $scriptUrl) {
		static::$jsLinks[] = $scriptUrl;
	}
	/**
	 * Adds a javascript snippet to the page.
	 * @package core
	 * @param string $script The snippet of javascript to add.
	 */
	public static function add_js_snippet(string $script) {
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
		
		$mega_menu = false;
		if(is_object($nav_links)) {
			$mega_menu = true;
			$class = trim("$class mega-menu");
			$links_list = [];
			$keys = array_keys(get_object_vars($nav_links));
			foreach($keys as $key) {
				$links_list[] =  "category\0$key";
				$links_list = array_merge(
					$links_list,
					$nav_links->$key
				);
			}
			$nav_links = $links_list;
		}
		
		$result = "<nav class='$class'>\n";
		$is_first_category = true;
		// Loop over all the navigation links
		foreach($nav_links as $item) {
			if(!is_string($item)) {
				// Output the item as a link to a url
				$result .= "<span><a href='" . str_replace("{page}", rawurlencode($env->page), $item[1]) . "'>$item[0]</a></span>";
				continue;
			}
			
			// Extract the item key - a null character can be used to separate extra data from an item type
			$item_key = $item;
			if(strpos($item_key, "\0") !== false)
				$item_key = substr($item_key, 0, strpos($item_key, "\0"));
			
			// The item is a string
			switch($item_key) {
				//keywords
				case "user-status": // Renders the user status box
					if($env->is_logged_in) {
						$result .= "<span class='inflexible logged-in" . ($env->is_logged_in ? " moderator" : " normal-user") . "'>";
						if(module_exists("feature-user-preferences")) {
							$result .= "<a href='?action=user-preferences' aria-label='Change user preferences'>$settings->user_preferences_button_text</a>";
						}
						$result .= self::render_username($env->user);
						$result .= " <small>(<a href='index.php?action=logout'>Logout</a>)</small>";
						$result .= "</span>";
						//$result .= page_renderer::$nav_divider;
					}
					else {
						$returnto_url = $env->action !== "logout" ? $_SERVER["REQUEST_URI"] : "?action=view&page=" . rawurlencode($settings->defaultpage);
						$result .= "<span class='not-logged-in'><a href='index.php?action=login&returnto=" . rawurlencode($returnto_url) . "' rel='nofollow'>Login</a></span>";
					}
					break;

				case "search": // Renders the search bar
					$result .= "<span class='inflexible'><form method='get' action='index.php' style='display: inline;'><input type='search' name='page' list='allpages' placeholder='&#x1f50e; Type a page name here and hit enter' /><input type='hidden' name='search-redirect' value='true' /></form></span>";
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
				
				case "category": // Renders a category header
					if(!$is_first_category) $result .= "</span>";
					$result .= "<span class='category'><strong>" . substr($item, 9) . "</strong>";
					$is_first_category = false;
					break;

				// It isn't a keyword, so just output it directly
				default:
					$result .= "<span>$item</span>";
			}
		}
		if($mega_menu) $result .= "</span>";

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
			$result .= "<img class='avatar' aria-hidden='true' src='?action=avatar&user=" . urlencode($name) . "&size=$settings->avatars_size' /> ";
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
		
		$result = "<datalist id='allpages'>\n";
		
		// If dynamic page sugggestions are enabled, then we should send a loading message instead.
		if($settings->dynamic_page_suggestion_count > 0) {
			$result .= "<option value='Loading suggestions...' />";
		} else {
			$arrayPageIndex = get_object_vars($pageindex);
			$sorter = new Collator("");
			uksort($arrayPageIndex, function($a, $b) use($sorter) : int {
				return $sorter->compare($a, $b);
			});
			
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
	let search_box = document.querySelector("input[type=search]"),
		alt_pressed = false;
	document.addEventListener("keyup", (event) => {
		if(event.keyCode !== 18) return;
		alt_pressed = false;
		console.info("[search box/alt-tracker] alt released");
	});
	document.addEventListener("keydown", (event) => {
		if(event.keyCode !== 18) return;
		alt_pressed = true;
		console.info("[search box/alt-tracker] alt pressed");
	});
	
	search_box.form.addEventListener("submit", (event) => {
		if(!alt_pressed) {
			console.log("[search box/form] Alt wasn\'t pressed");
			event.target.removeAttribute("target");
			return;
		}
		
		console.log("[search box/form] Fiddling target");
		
		event.target.setAttribute("target", "_blank");
		setTimeout(() => {
			alt_pressed = false;
		}, 100);
	});
});
');
