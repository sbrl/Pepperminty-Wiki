<?php
register_module([
	"name" => "Parsedown",
	"version" => "0.3",
	"author" => "Johnny Broadway, Emanuil Rusev & Starbeamrainbowlabs",
	"description" => "An upgraded parser based on Emanuil Rusev's Parsedown Extra PHP library (https://github.com/erusev/parsedown-extra), which is licensed MIT. Please be careful, as this module adds a some weight to your installation, and also requires write access to the disk on first load.",
	"id" => "parser-parsedown",
	"code" => function() {
		global $settings;
		
		$parser = new PeppermintParsedown();
		$parser->setInternalLinkBase("?page=%s");
		add_parser("parsedown", function($source) use ($parser) {
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
	
	function __construct()
	{
		// Prioritise our internal link parsing over the regular link parsing
		array_unshift($this->InlineTypes["["], "InternalLink");
		// Prioritise our image parser over the regular image parser
		array_unshift($this->InlineTypes["!"], "ExtendedImage");
		
		//$this->inlineMarkerList .= "{";
	}
	
	protected function inlineInternalLink($fragment)
	{
		if(preg_match('/^\[\[(.*)\]\]/', $fragment["text"], $matches))
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
			
			return [
				"extent" => strlen($matches[0]),
				"element" => [
					"name" => "a",
					"text" => $display,
					"attributes" => [
						"href" => $linkUrl
					]
				]
			];
		}
		return;
	}
	
	protected function inlineExtendedImage($fragment)
	{
		if(preg_match('/^!\[(.*)\]\(([^ |)]+)\s*\|([^|)]*)(?:\|([^)]*))?\)/', $fragment["text"], $matches))
		{
			/*
			 * 0 - Everything
			 * 1 - Alt text
			 * 2 - Url
			 * 3 - First param
			 * 4 - Second Param (optional)
			 */
			
			var_dump($matches);
			
			$altText = $matches[1];
			$imageUrl = $matches[2];
			$param1 = strtolower(trim($matches[3]));
			$param2 = empty($matches[4]) ? false : strtolower(trim($matches[4]));
			$floatDirection = false;
			$imageSize = false;
			
			if($this->isFloatValue($param1))
			{
				$floatDirection = $param1;
				$imageSize = $this->parseSizeSpec($param2);
			}
			else if($this->isFloatValue($param2))
			{
				$floatDirection = $param2;
				$imageSize = $this->parseSizeSpec($param1);
			}
			else
			{
				$imageSize = $this->parseSizeSpec($param1);
			}
			
			// If they are both invalid then something very strange is going on
			// Let the built in parsedown image handler deal with it
			if($imageSize === false && $floatDirection === false)
				return;
			
			$style = "";
			if($imageSize !== false)
				$style .= " max-width: " . $imageSize["x"] . "; max-height: " . $imageSize["y"] . ";";
			if($floatDirection)
				$style .= " float: $floatDirection;";
			
			return [
				"extent" => strlen($matches[0]),
				"element" => [
					"name" => "img",
					"attributes" => [
						"src" => $imageUrl,
						"alt" => $altText,
						"style" => trim($style)
					]
				]
			];
		}
	}
	
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
