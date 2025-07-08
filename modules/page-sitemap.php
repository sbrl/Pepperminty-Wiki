<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Sitemap",
	"version" => "0.1.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds XML sitemap generation. Additional manual setup is required to notify search engines about the sitemap. See the Features FAQ in the documentation (or your wiki's credits page) for more information.",
	"id" => "page-sitemap",
	"code" => function() {
		global $settings, $env;
		/**
		 * @api {get} ?action=sitemap	Get an XML sitemap
		 * @apiName Sitemap
		 * @apiGroup Page
		 * @apiPermission Anonymous
		 */
		
		/*
		 * ██████   █████  ██     ██ 
		 * ██   ██ ██   ██ ██     ██ 
		 * ██████  ███████ ██  █  ██ 
		 * ██   ██ ██   ██ ██ ███ ██ 
		 * ██   ██ ██   ██  ███ ███  
		 */
		add_action("sitemap", function() {
			global $pageindex, $env;
			
			$full_url_stem = full_url();
			
			// Reference: https://www.sitemaps.org/protocol.html
			$xml = new XmlWriter();
			$xml->openMemory();
			$xml->startDocument("1.0", "utf-8");
			
			$xml->startElement("urlset");
			$xml->writeAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
			
			foreach($pageindex as $pagename => $pagedata) {
				$xml->startElement("url");
				$xml->writeElement("loc", "$full_url_stem?page=".rawurlencode($pagename));
				if(isset($pagedata->lastmodified))
					$xml->writeElement("lastmod", date("Y-m-d", $pagedata->lastmodified));
				$xml->endElement();
			}
			
			$xml->endElement();
			
			$sitemap = $xml->flush();
			
			header("content-type: application/xml");
			header("content-disposition: inline");
			header("content-length: " . strlen($sitemap));
			exit($sitemap);
		});
		
		if($env->is_admin) {
			add_help_section("947-sitemap", "Sitemap", "<p>$settings->sitename has a sitemap. You can find it here: <a href='?action=sitemap'>sitemap</a>.</p>
			<p>In order for crawlers to discover this sitemap however, you must update your <code>robots.txt</code> file for the domain $settings->sitename is hosted on to add a line like so:</p>
			<pre><code>Sitemap: http://example.com/path/to/index.php?action=sitemap</code></pre>
			<p>....replacing the relevant parts of the URL as appropriate. Note that more than one <code>Sitemap:</code> directive is allowed in a single <code>robots.txt</code> file.</p>");
		}
	}
]);

?>
