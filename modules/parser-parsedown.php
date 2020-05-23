<?php
register_module([
	"name" => "Parsedown",
	"version" => "0.10.2",
	"author" => "Emanuil Rusev & Starbeamrainbowlabs",
	"description" => "An upgraded (now default!) parser based on Emanuil Rusev's Parsedown Extra PHP library (https://github.com/erusev/parsedown-extra), which is licensed MIT. Please be careful, as this module adds some weight to your installation.",
	"extra_data" => [
		/********** Parsedown versions **********
		 * Parsedown Core:		1.8.0-beta-7	*
		 * Parsedown Extra:		0.8.0-beta-1	*
		 * Parsedown Extreme:	0.1.6 *removed* *
		 ****************************************/
		"Parsedown.php" => "https://raw.githubusercontent.com/erusev/parsedown/1610e4747c88a53676f94f752b447f4eff03c28d/Parsedown.php",
		// "ParsedownExtra.php" => "https://raw.githubusercontent.com/erusev/parsedown-extra/91ac3ff98f0cea243bdccc688df43810f044dcef/ParsedownExtra.php",
		// "Parsedown.php" => "https://raw.githubusercontent.com/erusev/parsedown/3825db53a2be5d9ce54436a9cc557c6bdce1808a/Parsedown.php",
		"ParsedownExtra.php" => "https://raw.githubusercontent.com/erusev/parsedown-extra/352d03d941fc801724e82e49424ff409175261fd/ParsedownExtra.php"
		// Parsedown Extreme is causing PHP 7.4+ errors, and isn't rendering correctly with the security features we have turned on.
		// "ParsedownExtended.php" => "https://raw.githubusercontent.com/BenjaminHoegh/ParsedownExtended/8e1224e61a199cb513c47398353a27f6ba822da6/ParsedownExtended.php"
		// "ParsedownExtreme.php" => "https://raw.githubusercontent.com/BenjaminHoegh/parsedown-extreme/adae4136534ad1e4159fe04c74c4683681855b84/ParsedownExtreme.php"
	],
	"id" => "parser-parsedown",
	"code" => function() {
		global $settings;
		
		$parser = new PeppermintParsedown();
		$parser->setInternalLinkBase("?page=%s");
		add_parser("parsedown", function($source, $untrusted) use ($parser) {
			global $settings;
			$parser->setsafeMode($untrusted || $settings->all_untrusted);
			$parser->setMarkupEscaped($settings->clean_raw_html);
			$result = $parser->text($source);
			
			return $result;
		}, function($source) {
			global $version, $settings, $pageindex;
			$id_text = "$version|$settings->parser|$source";
			
			// Find template includes
			preg_match_all(
				'/\{\{\s*([^|]+)\s*(?:\|[^}]*)?\}\}/',
				$source, $includes
			);
			foreach($includes[1] as $include_pagename) {
				if(empty($pageindex->$include_pagename))
					continue;
				$id_text .= "|$include_pagename:" . parsedown_pagename_resolve(
					$pageindex->$include_pagename->lastmodified
				);
			}
			
			return str_replace(["+","/"], ["-","_"], base64_encode(hash(
				"sha256",
				$id_text,
				true
			)));
		});
		
		add_action("parsedown-render-ext", function() {
			global $settings, $env, $paths;
			
			if(!$settings->parser_ext_renderers_enabled) {
				http_response_code(403);
				header("content-type: image/png");
				imagepng(errorimage("Error: External diagram renderer support\nhas been disabled on $settings->sitename.\nTry contacting {$settings->admindetails_name}, $settings->sitename's administrator."));
				exit();
			}
			
			if(!isset($_GET["source"])) {
				http_response_code(400);
				header("content-type: image/png");
				imagepng(errorimage("Error: No source text \nspecified."));
				exit();
			}
			
			if(!isset($_GET["language"])) {
				http_response_code(400);
				header("content-type: image/png");
				imagepng(errorimage("Error: No external renderer \nlanguage specified."));
				exit();
			}
			
			$source = $_GET["source"];
			$language = $_GET["language"];
			
			if(!isset($settings->parser_ext_renderers->$language)) {
				$message = "Error: Unknown language {$_GET["language"]}.\nSupported languages:\n";
				foreach($settings->parser_ext_renderers as $language => $spec)
					$message .= "$spec->name ($language)\n";
					
				http_response_code(400);
				header("content-type: image/png");
				imagepng(errorimage(trim($message)));
				exit();
			}
			
			$renderer = $settings->parser_ext_renderers->$language;
			
			$cache_id = hash("sha256",
				hash("sha256", $language) . 
				hash("sha256", $source) . 
				($_GET["immutable_key"] ?? "")
			);
			
			$cache_file_location = "{$paths->cache_directory}/render_ext/$cache_id." . system_mime_type_extension($renderer->output_format);
			
			// If it exists on disk already, then serve that instead
			if(file_exists($cache_file_location)) {
				header("cache-control: public, max-age=31536000, immutable");
				header("content-type: $renderer->output_format");
				header("content-length: " . filesize($cache_file_location));
				header("x-cache: render_ext/hit");
				readfile($cache_file_location);
				exit();
			}
			
			if(!$settings->parser_ext_allow_anon && !$env->is_logged_in) {
				http_response_code(401);
				header("content-type: image/png");
				imagepng(errorimage(wordwrap("Error: You aren't logged in, that image hasn't yet been cached, and $settings->sitename does not allow anonymous users to invoke external renderers, so that image can't be generated right now. Try contacting $settings->admindetails_name, $settings->sitename's administrator (their details can be found at the bottom of every page).")));
				exit();
			}
			
			// Create the cache directory if doesn't exist already
			if(!file_exists(dirname($cache_file_location)))
				mkdir(dirname($cache_file_location), 0750, true);
			
			$cli_to_execute = $renderer->cli;
			
			$descriptors = [
				0 => null,		// stdin
				1 => null,		// stdout
				2 => tmpfile()	// stderr
			];
			
			switch ($renderer->cli_mode) {
				case "pipe":
					// Fill stdin with the input text
					$descriptors[0] = tmpfile();
					fwrite($descriptors[0], $source);
					fseek($descriptors[0], 0);
					
					// Pipe the output to be the cache file
					$descriptors[1] = fopen($cache_file_location, "wb+");
					break;
				
				case "substitution_pipe":
					// Update the command that we're going to execute
					$cli_to_execute = str_replace(
						"{input_text}",
						escapeshellarg($source),
						$cli_to_execute
					);
					
					// Set the descriptors
					$descriptors[0] = tmpfile();
					$descriptors[1] = fopen($cache_file_location, "wb+");
					break;
				
				case "file":
					$descriptors[0] = tmpfile();
					fwrite($descriptors[0], $source);
					$descriptors[1] = tmpfile();
					
					$cli_to_execute = str_replace(
						[ "{input_file}", "{output_file}" ],
						[
							escapeshellarg(stream_get_meta_data($descriptors[0])["uri"]),
							escapeshellarg($cache_file_location)
						],
						$cli_to_execute
					);
					break;
				
				default:
					http_response_code(503);
					header("cache-control: no-cache, no-store, must-revalidate");
					header("content-type: image/png");
					imagepng(errorimage("Error: Unknown external renderer mode '$renderer->cli_mode'.\nPlease contact $settings->admindetails_name, $settings->sitename's administrator."));
					exit();
					break;
			}
			
			if('\\' !== DIRECTORY_SEPARATOR) {
				// We're not on Windows, so we can use timeout to force-kill if it takes too long
				$cli_to_execute = "timeout {$settings->parser_ext_time_limit} $cli_to_execute";
			}
			
			$start_time = microtime(true);
			$process_handle = proc_open(
				$cli_to_execute,
				$descriptors,
				$pipes,
				null, // working directory
				null // environment variables
			);
			if(!is_resource($process_handle)) {
				fclose($descriptors[0]);
				fclose($descriptors[1]);
				fclose($descriptors[2]);
				
				if(file_exists($cache_file_location)) unlink($cache_file_location);
				
				http_response_code(503);
				header("cache-control: no-cache, no-store, must-revalidate");
				header("content-type: image/png");
				imagepng(errorimage("Error: Failed to start external renderer.\nIs $renderer->name installed?"));
				exit();
			}
			// Wait for it to exit
			$exit_code = proc_close($process_handle);
			
			fclose($descriptors[0]);
			fclose($descriptors[1]);
			
			$time_taken = round((microtime(true) - $start_time) * 1000, 2);
			
			if($exit_code !== 0 || !file_exists($cache_file_location)) {
				fseek($descriptors[2], 0);
				$error_details = stream_get_contents($descriptors[2]);
				// Delete the cache file, which is guaranteed to exist because
				// we pre-emptively create it above
				if(file_exists($cache_file_location)) unlink($cache_file_location);
				
				http_response_code(503);
				header("content-type: image/png");
				imagepng(errorimage(
					"Error: The external renderer ($renderer->name)\nexited with code $exit_code,\nor potentially did not create the output file.\nDetails:\n" . wordwrap($error_details)
				));
				exit();
			}
			
			header("cache-control: public, max-age=31536000, immutable");
			header("content-type: $renderer->output_format");
			header("content-length: " . filesize($cache_file_location));
			header("x-cache: render_ext/miss, renderer took {$time_taken}ms");
			readfile($cache_file_location);
		});
		
		/*
 		 * ███████ ████████  █████  ████████ ██ ███████ ████████ ██  ██████ ███████
 		 * ██         ██    ██   ██    ██    ██ ██         ██    ██ ██      ██
 		 * ███████    ██    ███████    ██    ██ ███████    ██    ██ ██      ███████
 		 *      ██    ██    ██   ██    ██    ██      ██    ██    ██ ██           ██
 		 * ███████    ██    ██   ██    ██    ██ ███████    ██    ██  ██████ ███████
 		 */
		statistic_add([
			"id" => "wanted-pages",
			"name" => "Wanted Pages",
			"type" => "page",
			"update" => function($old_stats) {
				global $pageindex, $env;
				
				$result = new stdClass(); // completed, value, state
				$pages = [];
				foreach($pageindex as $pagename => $pagedata) {
					if(!file_exists($env->storage_prefix . $pagedata->filename))
						continue;
					$page_content = file_get_contents($env->storage_prefix . $pagedata->filename);
					
					$page_links = PeppermintParsedown::extract_page_names($page_content);
					
					foreach($page_links as $linked_page) {
						// We're only interested in pages that don't exist
						if(!empty($pageindex->$linked_page)) continue;
						
						if(empty($pages[$linked_page]))
							$pages[$linked_page] = 0;
						$pages[$linked_page]++;
					}
				}
				
				arsort($pages);
				
				$result->value = $pages;
				$result->completed = true;
				return $result;
			},
			"render" => function($stats_data) {
				$result = "<h2>$stats_data->name</h2>\n";
				$result .= "<table class='wanted-pages'>\n";
				$result .= "\t<tr><th>Page Name</th><th>Linking Pages</th></tr>\n";
				foreach($stats_data->value as $pagename => $linking_pages) {
					$result .= "\t<tr><td>$pagename</td><td>$linking_pages</td></tr>\n";
				}
				$result .= "</table>\n";
				return $result;
			}
		]);
		statistic_add([
			"id" => "orphan-pages",
			"name" => "Orphan Pages",
			"type" => "page-list",
			"update" => function($old_stats) {
				global $pageindex, $env;
				
				$result = new stdClass(); // completed, value, state
				$pages = [];
				foreach($pageindex as $pagename => $pagedata) {
					if(!file_exists($env->storage_prefix . $pagedata->filename))
						continue;
					$page_content = file_get_contents($env->storage_prefix . $pagedata->filename);
					
					$page_links = PeppermintParsedown::extract_page_names($page_content);
					
					foreach($page_links as $linked_page) {
						// We're only interested in pages that exist
						if(empty($pageindex->$linked_page)) continue;
						
						$pages[$linked_page] = true;
					}
				}
				
				$orphaned_pages = [];
				foreach($pageindex as $pagename => $page_data) {
					if(empty($pages[$pagename]))
						$orphaned_pages[] = $pagename;
				}
				
				$sorter = new Collator("");
				$sorter->sort($orphaned_pages);
				
				$result->value = $orphaned_pages;
				$result->completed = true;
				return $result;
			}
		]);
		statistic_add([
			"id" => "most-linked-to-pages",
			"name" => "Most Linked-To Pages",
			"type" => "page",
			"update" => function($old_stats) {
				global $pageindex, $env;
				
				$result = new stdClass(); // completed, value, state
				$pages = [];
				foreach($pageindex as $pagename => $pagedata) {
					if(!file_exists($env->storage_prefix . $pagedata->filename))
						continue;
					$page_content = file_get_contents($env->storage_prefix . $pagedata->filename);
					
					$page_links = PeppermintParsedown::extract_page_names($page_content);
					
					foreach($page_links as $linked_page) {
						// We're only interested in pages that exist
						if(empty($pageindex->$linked_page)) continue;
						
						if(empty($pages[$linked_page]))
							$pages[$linked_page] = 0;
						$pages[$linked_page]++;
					}
				}
				
				arsort($pages);
				
				$result->value = $pages;
				$result->completed = true;
				return $result;
			},
			"render" => function($stats_data) {
				global $pageindex;
				$result = "<h2>$stats_data->name</h2>\n";
				$result .= "<table class='most-linked-to-pages'>\n";
				$result .= "\t<tr><th>Page Name</th><th>Linking Pages</th></tr>\n";
				foreach($stats_data->value as $pagename => $link_count) {
					$pagename_display = !empty($pageindex->$pagename->redirect) && $pageindex->$pagename->redirect ? "<em>$pagename</em>" : $pagename;
					$result .= "\t<tr><td><a href='?page=" . rawurlencode($pagename) . "'>$pagename_display</a></td><td>$link_count</td></tr>\n";
				}
				$result .= "</table>\n";
				return $result;
			}
		]);
		
		add_help_section("20-parser-default", "Editor Syntax",
		"<p>$settings->sitename's editor uses an extended version of <a href='http://parsedown.org/'>Parsedown</a> to render pages, which is a fantastic open source Github flavoured markdown parser. You can find a quick reference guide on Github flavoured markdown <a href='https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet'>here</a> by <a href='https://github.com/adam-p/'>adam-p</a>, or if you prefer a book <a href='https://www.gitbook.com/book/roachhd/master-markdown/details'>Mastering Markdown</a> by KB is a good read, and free too!</p>
		<h3>Tips</h3>
		<ul>
			<li>Put 2 spaces at the end of a line to add a soft line break. Leave a blank line to add a head line break (i.e. a new paragraph).</li>
			<li>You can add an id to a header that you can link to. Put it in curly braces after the heading name like this: <code># Heading Name {#HeadingId}</code>. Then you can link to like like this: <code>[[Page name#HeadingId}]]</code>. You can also link to a heading id on the current page by omitting the page name: <code>[[#HeadingId]]</code>.</li>
		</ul>
		<h3>Extra Syntax</h3>
		<p>$settings->sitename's editor also supports some extra custom syntax, some of which is inspired by <a href='https://mediawiki.org/'>Mediawiki</a>.
		<table>
			<tr><th style='width: 40%'>Type this</th><th style='width: 20%'>To get this</th><th>Comments</th></th>
			<tr><td><code>[[Internal link]]</code></td><td><a href='?page=Internal%20link'>Internal Link</a></td><td>An internal link.</td></tr>
			<tr><td><code>[[Internal link|Display Text]]</code></td><td><a href='?page=Internal%20link'>Display Text</a></td><td>An internal link with some display text.</td></tr>
			<tr><td><code>![Alt text](http://example.com/path/to/image.png | 256x256 | right)</code></td><td><img src='http://example.com/path/to/image.png' alt='Alt text' style='float: right; max-width: 256px; max-height: 256px;' /></td><td>An image floating to the right of the page that fits inside a 256px x 256px box, preserving aspect ratio.</td></tr>
			<tr><td><code>![Alt text](http://example.com/path/to/image.png | 256x256 | caption)</code></td><td><figure><img src='http://example.com/path/to/image.png' alt='Alt text' style='max-width: 256px; max-height: 256px;' /><figcaption>Alt text</figcaption></figure></td><td>An image with a caption that fits inside a 256px x 256px box, preserving aspect ratio. The presence of the word <code>caption</code> in the regular braces causes the alt text to be taken and displayed below the image itself.</td></tr>
			<tr><td><code>![Alt text](Files/Cheese.png)</code></td><td><img src='index.php?action=preview&page=Files/Cheese.png' alt='Alt text' style='' /></td><td>An example of the short url syntax for images. Simply enter the page name of an image (or video / audio file), and Pepperminty Wiki will sort out the url for you.</td></tr>
			<tr><td><code>Some text ==marked text== more text</code></td><td>Some text <mark>marked text</mark> more text</td><td>Marked / highlighted text</td></tr>
			<tr><td><code>Some text^superscript^ more text</code></td><td>Some text<sup>superscript</sup> more text</td><td>Superscript</td></tr>
			<tr><td><code>Some text~subscript~ more text</code></td><td>Some text<sub>subscript</sub> more text</td><td>Subscript (note that we use a single tilda <code>~</code> here - a double results in strikethrough text instead)</td></tr>
			<tr><td><code>[ ] Unticked checkbox</code></td><td><input type='checkbox' disabled /> Unticked checkbox</td><td>An unticked checkbox. Must be at the beginning of a line or directly after a list item (e.g. <code> - </code> or <code>1. </code>).</td></tr>
			<tr><td><code>[x] Ticked checkbox</code></td><td><input type='checkbox' checked='checked' disabled /> Ticked checkbox</td><td>An ticked checkbox. The same rules as unticked checkboxes apply here too.</td></tr>
			<tr><td><code>some text &gt;!spoiler text!&lt; more text</code></td><td>some text <a class='spoiler' href='#spoiler-example' id='spoiler-example'>spoiler text</a> more text</td><td>A spoiler. Users must click it to reveal the content hidden beneath.</td></tr>
			<tr><td><code>some text ||spoiler text|| more text</code></td><td>some text <a class='spoiler' href='#spoiler-example-2' id='spoiler-example-2'>spoiler text</a> more text</td><td>Alternative spoiler syntax inspired by <a href='https://support.discord.com/hc/en-us/articles/360022320632-Spoiler-Tags-'>Discord</a>.</td></tr>
			<tr><td><code>[__TOC__]</code></td><td></td><td>An automatic table of contents. Note that this must be on a line by itself with no text before or after it on that line for it to work.</td></tr>
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
			<tr><td><code>{{{~}}}</code></td><td>Outputs the requested page's name.</td></tr>
			<tr><td><code>{{{*}}}</code></td><td>Outputs a comma separated list of all the subpages of the current page.</td></tr>
			<tr><td><code>{{{+}}}</code></td><td>Shows a gallery containing all the files that are sub pages of the current page.</td></tr>
		</table>");
		if($settings->parser_ext_renderers_enabled) {
			$doc_help = "<p>$settings->sitename supports external renderers. External renderers take the content of a code fence block, like this:</p>
			<pre><code>```language_code
Insert text here
```</code></pre>
<p>...and render it to an image. This is based on the <code>language_code</code> specified, as is done in the above example. Precisely what the output of a external renderer is depends on the external renderers defined, but $settings->sitename currently has the following external renderers registered:</p>
<table>
<tr><th>Name</th><th>Language code</th><th>Description</th><th>Reference Link</th></tr>
";
			
			foreach($settings->parser_ext_renderers as $code => $renderer) {
				$row = array_map("htmlentities", [
					$renderer->name,
					$code,
					$renderer->description,
					$renderer->url
				]);
				$row[3] = "<a href='$row[3]'>&#x1f517;</a>";
				$doc_help .= "<tr><td>".implode("</td><td>", $row)."</td></tr>\n";
			}
			$doc_help .= "</table>
			$settings->admindetails_name can register more external renderers - see the <a href='https://starbeamrainbowlabs.com/labs/peppermint/__nightdocs/06.8-External-Renderers.html'>documentation</a> for more information.</p>";
			
			add_help_section("24-external-renderers", "External Renderers", $doc_help);
		}
	}
]);

require_once("$paths->extra_data_directory/parser-parsedown/Parsedown.php");
require_once("$paths->extra_data_directory/parser-parsedown/ParsedownExtra.php");
// require_once("$paths->extra_data_directory/parser-parsedown/ParsedownExtended.php");

/**
 * Attempts to 'auto-correct' a page name by trying different capitalisation
 * combinations.
 * @param	string	$pagename	The page name to auto-correct.
 * @return	string	The auto-corrected page name.
 */
function parsedown_pagename_resolve($pagename) {
	global $pageindex;
	
	// If the page doesn't exist, check varying different
	// capitalisations to see if it exists under some variant.
	if(!empty($pageindex->$pagename))
		return $pagename;
	
	$pagename = ucfirst($pagename);
	if(!empty($pageindex->$pagename))
		return $pagename;
	
	$pagename = ucwords($pagename);
	return $pagename;
}

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
/**
 * The Peppermint-flavoured Parsedown parser.
 */
class PeppermintParsedown extends ParsedownExtra
{
	/**
	 * A long random and extremely unlikely string to identify where we want
	 * to put a table of contents.
	 * Hopefully nobody is unlucky enough to include this in a page...!
	 * @var string
	 */
	private const TOC_ID = "█yyZiy9c9oHVExhVummYZId_dO9-fvaGFvgQirEapxOtaL-s7WnK34lF9ObBoQ0EH2kvtd6VKcAL2█";
	
	/**
	 * The base directory with which internal links will be resolved.
	 * @var string
	 */
	private $internalLinkBase = "./%s";
	
	/**
	 * The parameter stack. Used for recursive templating.
	 * @var array
	 */
	protected $paramStack = [];
	
	/**
	 * Creates a new Peppermint Parsedown instance.
	 */
	function __construct()
	{
        parent::__construct();
		
		array_unshift($this->BlockTypes["["], "TableOfContents");
		
		// Prioritise our internal link parsing over the regular link parsing
		$this->addInlineType("[", "InternalLink", true);
		// Prioritise the checkbox handling - this is fine 'cause it doesn't step on InternalLink's toes
		$this->addInlineType("[", "Checkbox", true);
		// Prioritise our image parser over the regular image parser
		$this->addInlineType("!", "ExtendedImage", true);
		
		$this->addInlineType("{", "Template");
		$this->addInlineType("=", "Mark");
		$this->addInlineType("^", "Superscript");
		$this->addInlineType("~", "Subscript");
		
		$this->addInlineType(">", "Spoiler", true);
		$this->addInlineType("|", "Spoiler", true);
	}
	
	/**
	 * Helper method to add an inline type.
	 * @param string  $char          The char to match against.
	 * @param string  $function_id   The name bit of the function to call.
	 * @param boolean $before_others Whether to prioritise this function over other existing ones (default: false).
	 */
	protected function addInlineType(string $char, string $function_id, bool $before_others = false) {
		if(mb_strlen($char) > 1)
			throw new Exception("Error: '$char' is longer than a single character.");
		if(!isset($this->InlineTypes[$char]) or !is_array($this->InlineTypes[$char]))
			$this->InlineTypes[$char] = [];
		
		if(strpos($this->inlineMarkerList, $char) === false)
			$this->inlineMarkerList .= $char;
		
		if(!$before_others)
			$this->InlineTypes[$char][] = $function_id;
		else
			array_unshift($this->InlineTypes[$char], $function_id);
	}
	
	/*
	 * Override the text method here to insert the table of contents after
	 * rendering has been completed
	 */
	public function text($text) {
		$result = parent::text($text);
		$toc_html = $this->generateTableOfContents();
		$result = str_replace(self::TOC_ID, $toc_html, $result);
		
		return $result;
	}
	
	
	/*
	 * ████████ ███████ ███    ███ ██████  ██       █████  ████████ ██ ███    ██  ██████
	 *    ██    ██      ████  ████ ██   ██ ██      ██   ██    ██    ██ ████   ██ ██
	 *    ██    █████   ██ ████ ██ ██████  ██      ███████    ██    ██ ██ ██  ██ ██   ███
	 *    ██    ██      ██  ██  ██ ██      ██      ██   ██    ██    ██ ██  ██ ██ ██    ██
	 *    ██    ███████ ██      ██ ██      ███████ ██   ██    ██    ██ ██   ████  ██████
	 */
	/**
	 * Parses templating definitions.
	 * @param string $fragment The fragment to parse it out from.
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
					if(!empty($params)) {
						$variableValue = "<table>
	<tr><th>Key</th><th>Value</th></tr>\n";
						foreach($params as $key => $value)
						{
							$variableValue .= "\t<tr><td>" . $this->escapeText($key) . "</td><td>" . $this->escapeText($value) . "</td></tr>\n";
						}
						$variableValue .= "</table>";
					}
					else {
						$variableValue = "<em>(no parameters have been specified)</em>";
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
	
	/**
	 * Handles parsing out templates - recursively - and the parameter stack associated with it.
	 * @param	string	$source		The source string to process.
	 * @return	array	The parsed result
	 */
	protected function templateHandler($source)
	{
		global $pageindex, $env;
		
		
		$parts = preg_split("/\\||¦/", trim($source, "{}"));
		$parts = array_map("trim", $parts);
		
		// Extract the name of the template page
		$templatePagename = array_shift($parts);
		// If the page that we are supposed to use as the tempalte doesn't
		// exist, then there's no point in continuing.
		if(empty($pageindex->$templatePagename))
			return false;
		
		// Parse the parameters
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
			"rawHtml" => $parsedTemplateSource,
			"attributes" => [
				"class" => "template"
			]
		];
	}
	
	
	/*
	 *  ██████ ██   ██ ███████  ██████ ██   ██ ██████   ██████  ██   ██
	 * ██      ██   ██ ██      ██      ██  ██  ██   ██ ██    ██  ██ ██
	 * ██      ███████ █████   ██      █████   ██████  ██    ██   ███
	 * ██      ██   ██ ██      ██      ██  ██  ██   ██ ██    ██  ██ ██
	 *  ██████ ██   ██ ███████  ██████ ██   ██ ██████   ██████  ██   ██
	 */
	protected function inlineCheckbox($fragment) {
		// We're not interested if it's not at the beginning of a line
		if(strpos($fragment["context"], $fragment["text"]) !== 0)
			return;
		
		// If it doesn't match, then we're not interested
		if(preg_match('/\[([ x])\]/u', $fragment["text"], $matches) !== 1)
			return;
		
		$checkbox_content = $matches[1];
		
		$result = [
			"extent" => 3,
			"element" => [
				"name" => "input",
				"attributes" => [
					"type" => "checkbox",
					"disabled" => "disabled"
				]
			]
		];
		
		if($checkbox_content === "x")
			$result["element"]["attributes"]["checked"] = "checked";
		
		return $result;
	}
	
	
	/*
 	 * ███    ███  █████  ██████  ██   ██ ███████ ██████
 	 * ████  ████ ██   ██ ██   ██ ██  ██  ██      ██   ██
 	 * ██ ████ ██ ███████ ██████  █████   █████   ██   ██
 	 * ██  ██  ██ ██   ██ ██   ██ ██  ██  ██      ██   ██
 	 * ██      ██ ██   ██ ██   ██ ██   ██ ███████ ██████
 	 * 
 	 * ████████ ███████ ██   ██ ████████
 	 *    ██    ██       ██ ██     ██
 	 *    ██    █████     ███      ██
 	 *    ██    ██       ██ ██     ██
 	 *    ██    ███████ ██   ██    ██
 	 */
	protected function inlineMark($fragment) {
		// A question mark makes the PCRE 1-or-more + there lazy instead of greedy.
		// Ref https://www.rexegg.com/regex-quantifiers.html#greedytrap
		if(preg_match('/==([^=]+?)==/', $fragment["text"], $matches) !== 1)
			return;
		
		$marked_text = $matches[1];
		
		$result = [
			"extent" => strlen($matches[0]),
			"element" => [
				"name" => "mark",
				"handler" => [
					"function" => "lineElements",
					"argument" => $marked_text,
					"destination" => "elements"
				]
			]
		];
		return $result;
	}
	
	
	/*
	 *  ██████ ██    ██ ██████      ██  ██████ ██    ██ ██████  ███████ ██████
	 * ██      ██    ██ ██   ██    ██  ██      ██    ██ ██   ██ ██      ██   ██
	 * ███████ ██    ██ ██████    ██   ███████ ██    ██ ██████  █████   ██████
	 *      ██ ██    ██ ██   ██  ██         ██ ██    ██ ██      ██      ██   ██
	 * ██████   ██████  ██████  ██     ██████   ██████  ██      ███████ ██   ██
	 * 
	 *  ██████  ██████ ██████  ██ ██████  ████████
	 * ██      ██      ██   ██ ██ ██   ██    ██
	 * ███████ ██      ██████  ██ ██████     ██
	 *      ██ ██      ██   ██ ██ ██         ██
	 * ██████   ██████ ██   ██ ██ ██         ██
	 */
	protected function inlineSuperscript($fragment) {
		if(preg_match('/\^([^^]+?)\^/', $fragment["text"], $matches) !== 1)
			return;
		
		$superscript_text = $matches[1];
		
		$result = [
			"extent" => strlen($matches[0]),
			"element" => [
				"name" => "sup",
				"handler" => [
					"function" => "lineElements",
					"argument" => $superscript_text,
					"destination" => "elements"
				]
			]
		];
		return $result;
	}
	protected function inlineSubscript($fragment) {
		if(preg_match('/~([^~]+?)~/', $fragment["text"], $matches) !== 1)
			return;
		
		$subscript_text = $matches[1];
		
		$result = [
			"extent" => strlen($matches[0]),
			"element" => [
				"name" => "sub",
				"handler" => [
					"function" => "lineElements",
					"argument" => $subscript_text,
					"destination" => "elements"
				]
			]
		];
		return $result;
	}
	
	
	/*
	 * ███████ ██████   ██████  ██ ██      ███████ ██████
	 * ██      ██   ██ ██    ██ ██ ██      ██      ██   ██
	 * ███████ ██████  ██    ██ ██ ██      █████   ██████
	 *      ██ ██      ██    ██ ██ ██      ██      ██   ██
	 * ███████ ██       ██████  ██ ███████ ███████ ██   ██
	 */
	protected function inlineSpoiler($fragment) {
		if(preg_match('/(?:\|\||>!)([^|]+?)(?:\|\||!<)/', $fragment["text"], $matches) !== 1)
			return;
		
		$spoiler_text = $matches[1];
		$id = "spoiler-".crypto_id(24);
		
		$result = [
			"extent" => strlen($matches[0]),
			"element" => [
				"name" => "a",
				"attributes" => [
					"id" => $id,
					"class" => "spoiler",
					"href" => "#$id"
				],
				"handler" => [
					"function" => "lineElements",
					"argument" => $spoiler_text,
					"destination" => "elements"
				]
			]
		];
		return $result;
	}
	
	
	/*
	 * ██ ███    ██ ████████ ███████ ██████  ███    ██  █████  ██
	 * ██ ████   ██    ██    ██      ██   ██ ████   ██ ██   ██ ██
	 * ██ ██ ██  ██    ██    █████   ██████  ██ ██  ██ ███████ ██
	 * ██ ██  ██ ██    ██    ██      ██   ██ ██  ██ ██ ██   ██ ██
	 * ██ ██   ████    ██    ███████ ██   ██ ██   ████ ██   ██ ███████
	 * 
	 * ██      ██ ███    ██ ██   ██ ███████
	 * ██      ██ ████   ██ ██  ██  ██
	 * ██      ██ ██ ██  ██ █████   ███████
	 * ██      ██ ██  ██ ██ ██  ██       ██
	 * ███████ ██ ██   ████ ██   ██ ███████
	 */
	/**
	 * Parses internal links
	 * @param  string $fragment The fragment to parse.
	 */
	protected function inlineInternalLink($fragment)
	{
		global $pageindex, $env;
		
		if(preg_match('/^\[\[([^\]]*?)\]\]([^\s!?",;.()\[\]{}*=+\/]*)/u', $fragment["text"], $matches) === 1) {
			// 1: Parse parameters out
			// -------------------------------
			$link_page = str_replace(["\r", "\n"], [" ", " "], trim($matches[1]));
			$display = $link_page . trim($matches[2]);
			if(strpos($matches[1], "|") !== false || strpos($matches[1], "¦") !== false)
			{
				// We have a bar character
				$parts = preg_split("/\\||¦/", $matches[1], 2);
				$link_page = trim($parts[0]); // The page to link to
				$display = trim($parts[1]); // The text to display
			}
			
			
			// 2: Parse the hash out
			// -------------------------------
			$hash_code = "";
			if(strpos($link_page, "#") !== false)
			{
				// We want to link to a subsection of a page
				$hash_code = substr($link_page, strpos($link_page, "#") + 1);
				$link_page = substr($link_page, 0, strpos($link_page, "#"));
				
				// If $link_page is empty then we want to link to the current page
				if(strlen($link_page) === 0)
					$link_page = $env->page;
			}
			
			
			// 3: Page name auto-correction
			// -------------------------------
			$is_interwiki_link = module_exists("feature-interwiki-links") && is_interwiki_link($link_page);
			// Try different variants on the pagename to try and get it to 
			// match something automagically
			if(!$is_interwiki_link && empty($pageindex->$link_page))
				$link_page = parsedown_pagename_resolve($link_page);
			
			
			// 4: Construct the full url
			// -------------------------------
			$link_url = null;
			// If it's an interwiki link, then handle it as such
			if($is_interwiki_link)
				$link_url = interwiki_get_pagename_url($link_page);
			
			// If it isn't (or it failed), then try it as a normal link instead
			if(empty($link_url)) {
				$link_url = str_replace(
					"%s", rawurlencode($link_page),
					$this->internalLinkBase
				);
				// We failed to handle it as an interwiki link, so we should 
				// tell everyone that
				$is_interwiki_link = false;
			}
			
			// 5: Construct the title
			// -------------------------------
			$title = $link_page;
			if($is_interwiki_link)
				$title = interwiki_pagename_resolve($link_page)->name . ": " . interwiki_pagename_parse($link_page)[1] . " (Interwiki)";
			
			if(strlen($hash_code) > 0)
				$link_url .= "#$hash_code";
			
			
			// 6: Result encoding
			// -------------------------------
			$result = [
				"extent" => strlen($matches[0]),
				"element" => [
					"name" => "a",
					"text" => $display,
					
					"attributes" => [
						"href" => $link_url,
						"title" => $title
					]
				]
			];
			
			// Attach some useful classes based on how we handled it
			$class_list = [];
			// Interwiki links can never be redlinks
			if(!$is_interwiki_link && empty($pageindex->{makepathsafe($link_page)}))
				$class_list[] = "redlink";
			if($is_interwiki_link)
				$class_list[] = "interwiki_link";
			
			$result["element"]["attributes"]["class"] = implode(" ", $class_list);
			
			return $result;
		}
	}
	
	/*
	 * ███████ ██   ██ ████████ ███████ ███    ██ ██████  ███████ ██████
	 * ██       ██ ██     ██    ██      ████   ██ ██   ██ ██      ██   ██
	 * █████     ███      ██    █████   ██ ██  ██ ██   ██ █████   ██   ██
	 * ██       ██ ██     ██    ██      ██  ██ ██ ██   ██ ██      ██   ██
	 * ███████ ██   ██    ██    ███████ ██   ████ ██████  ███████ ██████
	 * 
	 * ██ ███    ███  █████   ██████  ███████ ███████
	 * ██ ████  ████ ██   ██ ██       ██      ██
	 * ██ ██ ████ ██ ███████ ██   ███ █████   ███████
	 * ██ ██  ██  ██ ██   ██ ██    ██ ██           ██
	 * ██ ██      ██ ██   ██  ██████  ███████ ███████
 	 */
 	/**
 	 * Parses the extended image syntax.
 	 * @param  string $fragment The source fragment to parse.
 	 */
	protected function inlineExtendedImage($fragment)
	{
		global $pageindex;
		
		if(preg_match('/^!\[(.*)\]\(([^|¦)]+)\s*(?:(?:\||¦)([^|¦)]*))?(?:(?:\||¦)([^|¦)]*))?(?:(?:\||¦)([^)]*))?\)/', $fragment["text"], $matches))
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
			$imageUrl = trim(str_replace("&amp;", "&", $matches[2])); // Decode & to allow it in preview urls
			$param1 = empty($matches[3]) ? false : strtolower(trim($matches[3]));
			$param2 = empty($matches[4]) ? false : strtolower(trim($matches[4]));
			$param3 = empty($matches[5]) ? false : strtolower(trim($matches[5]));
			$floatDirection = false;
			$imageSize = false;
			$imageCaption = false;
			$shortImageUrl = false;
			
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
			
			//echo("Image url: $imageUrl, Pageindex entry: " . var_export(isset($pageindex->$imageUrl), true) . "\n");
			
			if(isset($pageindex->$imageUrl) and $pageindex->$imageUrl->uploadedfile)
			{
				// We have a short url! Expand it.
				$shortImageUrl = $imageUrl;
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
			
			// ~ Image linker ~
			
			$imageHref = $shortImageUrl !== false ? "?page=" . rawurlencode($shortImageUrl) : $imageUrl;
			$result["element"] = [
				"name" => "a",
				"attributes" => [
					"href" => $imageHref
				],
				"text" => [$result["element"]],
				"handler" => "elements"
			];
			
			// ~
			
			if($imageCaption) {
				$rawStyle = $result["element"]["text"][0]["attributes"]["style"];
				$containerStyle = preg_replace('/^.*float/', "float", $rawStyle);
				$mediaStyle = preg_replace('/\s*float.*;/', "", $rawStyle);
				$result["element"] = [
					"name" => "figure",
					"attributes" => [
						"style" => $containerStyle
					],
					"text" => [
						$result["element"],
						[
							"name" => "figcaption",
							// rawHtml is fine here 'cause we're using the output of $this->text(), which is safe
							"rawHtml" => $this->text($altText),
						],
					],
					"handler" => "elements"
				];
				$result["element"]["text"][0]["attributes"]["style"] = $mediaStyle;
			}
			return $result;
		}
	}
	
	
	/*
	 *  ██████  ██████  ██████  ███████     ██████  ██       ██████   ██████ ██   ██
	 * ██      ██    ██ ██   ██ ██          ██   ██ ██      ██    ██ ██      ██  ██
	 * ██      ██    ██ ██   ██ █████       ██████  ██      ██    ██ ██      █████
	 * ██      ██    ██ ██   ██ ██          ██   ██ ██      ██    ██ ██      ██  ██
	 *  ██████  ██████  ██████  ███████     ██████  ███████  ██████   ██████ ██   ██
	 * 
	 * ██    ██ ██████   ██████  ██████   █████  ██████  ███████
	 * ██    ██ ██   ██ ██       ██   ██ ██   ██ ██   ██ ██
	 * ██    ██ ██████  ██   ███ ██████  ███████ ██   ██ █████
	 * ██    ██ ██      ██    ██ ██   ██ ██   ██ ██   ██ ██
	 *  ██████  ██       ██████  ██   ██ ██   ██ ██████  ███████
	 */
	
	protected function blockFencedCodeComplete($block) {
		global $settings;
		$result = parent::blockFencedCodeComplete($block);
		
		$language = preg_replace("/^language-/", "", $block["element"]["element"]["attributes"]["class"]);
		
		if(!isset($settings->parser_ext_renderers->$language))
			return $result;
		
		$text = $result["element"]["element"]["text"];
		$renderer = $settings->parser_ext_renderers->$language;
		
		$result["element"] = [
			"name" => "p",
			"element" => [
				"name" => "img",
				"attributes" => [
					"alt" => "Diagram rendered by {$renderer->name}",
					"src" => "?action=parsedown-render-ext&language=".rawurlencode($language)."&immutable_key=".hash("crc32b", json_encode($renderer))."&source=".rawurlencode($text)
				]
			]
		];
		if(!empty($renderer->output_classes))
			$result["element"]["element"]["attributes"]["class"] = implode(" ", $renderer->output_classes);
		
		return $result;
	}
	
	/*
	 * ██   ██ ███████  █████  ██████  ███████ ██████
	 * ██   ██ ██      ██   ██ ██   ██ ██      ██   ██
	 * ███████ █████   ███████ ██   ██ █████   ██████
	 * ██   ██ ██      ██   ██ ██   ██ ██      ██   ██
	 * ██   ██ ███████ ██   ██ ██████  ███████ ██   ██
	 */
	
	private $headingIdsUsed = [];
	private $tableOfContents = [];
	
	/**
	 * Inserts an item into the table of contents.
	 * @param int    $level The level to insert it at (valid values: 1 - 6)
	 * @param string $id    The id of the item.
	 * @param string $text  The text to display.
	 */
	protected function addTableOfContentsEntry(int $level, string $id, string $text) : void {
		$new_obj = (object) [
			"level" => $level,
			"id" => $id,
			"text" => $text,
			"children" => []
		];
		
		if(count($this->tableOfContents) == 0) {
			$this->tableOfContents[] = $new_obj;
			return;
		}
		
		$lastEntry = end($this->tableOfContents);
		if($level > $lastEntry->level) {
			$this->insertTableOfContentsObject($new_obj, $lastEntry);
			return;
		}
		$this->tableOfContents[] = $new_obj;
		return;
	}
	private function insertTableOfContentsObject(object $obj, object $target) {
		if($obj->level - 1 > $target->level && !empty($target->children)) {
			$this->insertTableOfContentsObject($obj, end($target->children));
		}
		$target->children[] = $obj;
	}
	
	protected function generateTableOfContents() : string {
		global $settings;
		$elements = [ $this->generateTableOfContentsElement($this->tableOfContents) ];
		if($settings->parser_toc_heading_level > 1)
			array_unshift(
				$elements,
				[ "name" => "h$settings->parser_toc_heading_level", "text" => "Table of Contents" ]
			);
		
		return trim($this->elements($elements), "\n");
	}
	private function generateTableOfContentsElement($toc) : array {
		$elements = [];
		foreach($toc as $entry) {
			$next = [
				"name" => "li",
				"attributes" => [
					"data-level" => $entry->level
				],
				"elements" => [ [
					"name" => "a",
					"attributes" => [
						"href" => "#$entry->id"
					],
					"handler" => [
						"function" => "lineElements",
						"argument" => $entry->text,
						"destination" => "elements"
					]
				] ]
			];
			if(isset($entry->children))
				$next["elements"][] = $this->generateTableOfContentsElement($entry->children);
			
			$elements[] = $next;
		}
		
		return [
			"name" => "ul",
			"elements" => $elements
		];
	}
	
	protected function blockHeader($line) {
		// This function overrides the header function defined in ParsedownExtra
		$result = parent::blockHeader($line);
		
		// If this heading doesn't have an id already, add an automatic one
		if(!isset($result["element"]["attributes"]["id"])) {
			$heading_id = str_replace(" ", "-",
				mb_strtolower(makepathsafe(
					$result["element"]["handler"]["argument"]
				))
			);
			$suffix = "";
			while(in_array($heading_id . $suffix, $this->headingIdsUsed)) {
				$heading_number = intval(str_replace("_", "", $suffix));
				if($heading_number == 0) $heading_number++;
				$suffix = "_" . ($heading_number + 1);
			}
			$result["element"]["attributes"]["id"] = $heading_id . $suffix;
			$this->headingIdsUsed[] = $result["element"]["attributes"]["id"];
		}
		
		$this->addTableOfContentsEntry(
			intval(strtr($result["element"]["name"], [ "h" => "" ])),
			$result["element"]["attributes"]["id"],
			$result["element"]["handler"]["argument"]
		);
		
		return $result;
	}
	
	/*
	 * Inserts a special string to identify where we need to put the table of contents later
	 */
	protected function blockTableOfContents($fragment) {
		// Indent? Don't even want to know
		if($fragment["indent"] > 0) return;
		// If it doesn't match, then we're not interested
		if(preg_match('/^\[_*(?:TOC|toc)_*\]$/u', $fragment["text"], $matches) !== 1)
			return;
		
		$result = [
			"element" => [ "text" => self::TOC_ID ]
		];
		return $result;
	}
	
	# ~
	# Static Methods
	# ~
	
	/**
	 * Extracts the page names from internal links in a given markdown source.
	 * Does not actually _parse_ the source - only extracts via a regex.
	 * @param	string	$page_text	The source text to extract a list of page names from.
	 * @return	array	A list of page names that the given source text links to.
	 */
	public static function extract_page_names($page_text) {
		global $pageindex;
		preg_match_all("/\[\[([^\]]+)\]\]/", $page_text, $linked_pages);
		if(count($linked_pages[1]) === 0)
			return []; // No linked pages here
		
		$result = [];
		foreach($linked_pages[1] as $linked_page) {
			// Strip everything after the | and the #
			if(strpos($linked_page, "|") !== false)
				$linked_page = substr($linked_page, 0, strpos($linked_page, "|"));
			if(strpos($linked_page, "#") !== false)
				$linked_page = substr($linked_page, 0, strpos($linked_page, "#"));
			if(strlen($linked_page) === 0)
				continue;
			// Make sure we try really hard to find this page in the
			// pageindex
			$altered_linked_page = $linked_page;
			if(!empty($pageindex->{ucfirst($linked_page)}))
				$altered_linked_page = ucfirst($linked_page);
			else if(!empty($pageindex->{ucwords($linked_page)}))
				$altered_linked_page = ucwords($linked_page);
			else // Our efforts were in vain, so reset to the original
				$altered_linked_page = $linked_page;
			
			$result[] = $altered_linked_page;
		}
		
		return $result;
	}
	
	
	/*
	 * ██    ██ ████████ ██ ██      ██ ████████ ██ ███████ ███████
	 * ██    ██    ██    ██ ██      ██    ██    ██ ██      ██
	 * ██    ██    ██    ██ ██      ██    ██    ██ █████   ███████
	 * ██    ██    ██    ██ ██      ██    ██    ██ ██           ██
	 *  ██████     ██    ██ ███████ ██    ██    ██ ███████ ███████
	 */
	
	/**
	 * Returns whether a string is a valid float: XXXXXX; value.
	 * Used in parsing the extended image syntax.
	 * @param	string	$value The value check.
	 * @return	bool	Whether it's valid or not.
	 */
	private function isFloatValue(string $value)
	{
		return in_array(strtolower($value), [ "left", "right" ]);
	}
	
	/**
	 * Parses a size specifier into an array.
	 * @param	string	$text	The source text to parse. e.g. "256x128"
	 * @return	array|bool	The parsed size specifier. Example: ["x" => 256, "y" => 128]. Returns false if parsing failed.
	 */
	private function parseSizeSpec(string $text)
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
	
	/**
	 * Escapes the source text via htmlentities.
	 * @param	string	$text	The text to escape.
	 * @return	string	The escaped string.
	 */
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

?>
