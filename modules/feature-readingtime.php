<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Reading time estimator",
	"version" => "0.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Displays the approximate reading time for a page beneath it's title.",
	"id" => "feature-readingtime",
	"code" => function() {
		
		page_renderer::register_part_preprocessor(function(&$parts) {
			global $env, $settings;
			// Only insert for the view action
			if($env->action !== $settings->readingtime_action || !$settings->readingtime_enabled)
				return;
			
			$reading_time = estimate_reading_time(
				file_get_contents($env->page_filename),
				$settings->readingtime_language
			);
			
			$insert = "<small><em>{$reading_time[0]} - {$reading_time[1]} minute read</em></small>";
			if($reading_time[0] === $reading_time[1])
				$insert = "<small><em>{$reading_time[0]} minute read</em></small>";
			
			// TODO: Create a canonical way to insert something just below the header - this might be tough though 'cause the that isn't handled by the page_renderer though
			$insert = "\n\t\t\t<p class='system-text-insert readingtime-estimate'>$insert</p>";
			$parts["{content}"] = substr_replace(
				$parts["{content}"],
				"</h1>$insert",
				strpos($parts["{content}"], "</h1>"),
				5
			);
		});
	}
]);

/**
 * Estimates the reading time for a given lump of text.
 * Ref https://github.com/sbrl/Pepperminty-Wiki/issues/172 (has snippet of
 * original code from Firefox & link to study from which the numbers are
 * taken).
 * @param	string	$text	The text to estimate for.
 * @param	string	$lang	The language code of the text - defaults to "en"
 * @return	array	An array in the form [ low_time, high_time ] in minutes
 */
function estimate_reading_time(string $text, string $lang = "en") : array {
	$chars_count = mb_strlen(preg_replace("/\s+?/", "", strtr($text, [
		"[" => "", "]" => "", "(" => "", ")" => "",
		"|" => "", "#" => "", "*" => ""
	])));
	$langs = [
		"en" => (object) [ "cpm" => 987, "variance" => 118 ],
		"ar" => (object) [ "cpm" => 612, "variance" => 88 ],
		"de" => (object) [ "cpm" => 920, "variance" => 86 ],
		"es" => (object) [ "cpm" => 1025, "variance" => 127 ],
		"fi" => (object) [ "cpm" => 1078, "variance" => 121 ],
		"fr" => (object) [ "cpm" => 998, "variance" => 126 ],
		"he" => (object) [ "cpm" => 833, "variance" => 130 ],
		"it" => (object) [ "cpm" => 950, "variance" => 140 ],
		"jw" => (object) [ "cpm" => 357, "variance" => 56 ],
		"nl" => (object) [ "cpm" => 978, "variance" => 143 ],
		"pl" => (object) [ "cpm" => 916, "variance" => 126 ],
		"pt" => (object) [ "cpm" => 913, "variance" => 145 ],
		"ru" => (object) [ "cpm" => 986, "variance" => 175 ],
		"sk" => (object) [ "cpm" => 885, "variance" => 145 ],
		"sv" => (object) [ "cpm" => 917, "variance" => 156 ],
		"tr" => (object) [ "cpm" => 1054, "variance" => 156 ],
		"zh" => (object) [ "cpm" => 255, "variance" => 29 ],
	];
	if(!isset($langs[$lang]))
		return null;
	
	return [
		ceil($chars_count / ($langs[$lang]->cpm + $langs[$lang]->variance)),
		ceil($chars_count / ($langs[$lang]->cpm - $langs[$lang]->variance))
	];
}
