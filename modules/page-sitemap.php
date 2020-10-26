<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Sitemap",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds XML sitemap generation. Additional manual setup is required to notify search engines about the sitemap generated. See the Features FAQ in the documentation (or your wiki's credits page) for more information.",
	"id" => "page-sitemap",
	"code" => function() {
		global $settings;
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
		
		add_help_section("800-raw-page-content", "Viewing Raw Page Content", "<p>Although you can use the edit page to view a page's source, you can also ask $settings->sitename to send you the raw page source and nothing else. This feature is intented for those who want to automate their interaction with $settings->sitename.</p>
		<p>To use this feature, navigate to the page for which you want to see the source, and then alter the <code>action</code> parameter in the url's query string to be <code>raw</code>. If the <code>action</code> parameter doesn't exist, add it. Note that when used on an file's page this action will return the source of the description and not the file itself.</p>");
	}
]);

?>
