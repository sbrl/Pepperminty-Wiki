<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Credits",
	"version" => "0.8.2",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds the credits page. You *must* have this module :D",
	"id" => "page-credits",
	"code" => function() {
		/**
		 * @api {get} ?action=credits Get the credits page
		 * @apiName Credits
		 * @apiGroup Utility
		 * @apiPermission Anonymous
		 */
		
		/*
		 *  ██████ ██████  ███████ ██████  ██ ████████ ███████ 
		 * ██      ██   ██ ██      ██   ██ ██    ██    ██      
		 * ██      ██████  █████   ██   ██ ██    ██    ███████ 
		 * ██      ██   ██ ██      ██   ██ ██    ██         ██ 
		 *  ██████ ██   ██ ███████ ██████  ██    ██    ███████ 
		 */
		add_action("credits", function() {
			global $settings, $version, $env, $pageindex, $modules;
			
			$credits = [
				"Code" => [
					"author" => "Starbeamrainbowlabs",
					"author_url" => "https://starbeamrainbowlabs.com/",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/",
					"icon" => "https://avatars0.githubusercontent.com/u/9929737?v=3&s=24"
				],
				"Mime type to file extension mapper" => [
					"author" => "Chaos",
					"author_url" => "https://stackoverflow.com/users/47529/chaos",
					"thing_url" => "https://stackoverflow.com/a/1147952/1460422",
					"icon" => "https://www.gravatar.com/avatar/aaee40db39ad6b164cfb89cb6ad4d176?s=328&d=identicon&s=24"
				],
				"Parsedown" => [
					"author" => "Emanuil Rusev and others",
					"author_url" => "https://github.com/erusev/",
					"thing_url" => "https://github.com/erusev/parsedown/",
					"icon" => "https://avatars1.githubusercontent.com/u/184170?v=3&s=24"
				],
				"CSS Minification Code" => [
					"author" => "Jean",
					"author_url" => "http://www.catswhocode.com/",
					"thing_url" => "http://www.catswhocode.com/blog/3-ways-to-compress-css-files-using-php"
				],
				"Slightly modified version of Slimdown" => [
					"author" => "Johnny Broadway",
					"author_url" => "https://github.com/jbroadway",
					"thing_url" => "https://gist.github.com/jbroadway/2836900",
					"icon" => "https://avatars2.githubusercontent.com/u/87886?v=3&s=24"
				],
				"Insert tab characters into textareas" => [
					"author" => "Unknown",
					"author_url" => "http://stackoverflow.com/q/6140632/1460422",
					"thing_url" => "https://jsfiddle.net/2wAzx/13/",
				],
				"Default Favicon" => [
					"author" => "bluefrog23",
					"author_url" => "https://openclipart.org/user-detail/bluefrog23/",
					"thing_url" => "https://openclipart.org/detail/19571/peppermint-candy-by-bluefrog23"
				],
				"Bug Reports" => [
					"author" => "nibreh",
					"author_url" => "https://github.com/nibreh/",
					"thing_url" => "",
					"icon" => "https://avatars2.githubusercontent.com/u/7314006?v=3&s=24"
				],
				"More Bug Reports (default credentials + downloader; via Gitter)" => [
					"author" => "Tyler Spivey",
					"author_url" => "https://github.com/tspivey/",
					"thing_url" => "",
					"icon" => "https://avatars2.githubusercontent.com/u/709407?v=4&s=24"
				],
				"PR #135: Fix repeated page names on sidebar" => [
					"author" => "ikisler",
					"author_url" => "https://github.com/ikisler",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/pull/135",
					"icon" => "https://avatars3.githubusercontent.com/u/12506147?v=3&s=24"
				],
				"PR #136: Fix issue where bottom nav is cut off" => [
					"author" => "ikisler",
					"author_url" => "https://github.com/ikisler",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/pull/136",
					"icon" => "https://avatars3.githubusercontent.com/u/12506147?v=3&s=24"
				],
				"PR #140: Edit Previewing" => [
					"author" => "ikisler",
					"author_url" => "https://github.com/ikisler",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/pull/140",
					"icon" => "https://avatars3.githubusercontent.com/u/12506147?v=3&s=24"
				],
				"Issue #153: Authenticated DOS attack (XXD billion laughs attack)" => [
					"author" => "ProDigySML",
					"author_url" => "https://github.com/ProDigySML",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/issues/152",
					"icon" => "https://avatars3.githubusercontent.com/u/16996819?s=24&v=4"
				],
				"Many miscellaneus bug reports and PRs to fix things" => [
					"author" => "Sean Feeney",
					"author_url" => "https://github.com/SeanFromIT",
					"thing_url" => "https://gitter.im/Pepperminty-Wiki/Lobby?at=5d786927460a6f5a1600f1c1",
					"icon" => "https://avatars3.githubusercontent.com/u/10387753?s=24&v=4"
				],
				"Inverted logic fix in the peppermint.json access detector (#179)" => [
					"author" => "Kevin Otte",
					"author_url" => "https://www.nivex.net/",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/pull/179",
					"icon" => "https://avatars3.githubusercontent.com/u/3833404?s=24&v=4"
				],
				"IIS web server documentation" => [
					"author" => "Nathan Nance",
					"author_url" => "https://github.com/npnance",
					"thing_url" => "https://github.com/sbrl/Pepperminty-Wiki/pull/229",
					"icon" => "https://avatars.githubusercontent.com/u/975340?s=24"
				]
			];
			
			//// Credits html renderer ////
			$credits_html = "<ul>\n";
			foreach($credits as $thing => $author_details)
			{
				$credits_html .= "	<li>";
				$credits_html .= "<a href='" . htmlentities($author_details["thing_url"]) . "'>".htmlentities($thing)."</a> by ";
				if(isset($author_details["icon"]))
				$credits_html .= "<img class='logo small' style='vertical-align: middle;' src='" . htmlentities($author_details["icon"]) . "' /> ";
				$credits_html .= "<a href='" . htmlentities($author_details["author_url"]) . "'>" . $author_details["author"] . "</a>";
				$credits_html .= "</li>\n";
			}
			$credits_html .= "</ul>";
			///////////////////////////////
			
			//// Module html renderer ////
			$modules_html = "<table>
	<tr>
		<th>Name</th>
		<th>Version</th>
		<th>Author</th>
		<th>Description</th>
	</tr>";
			foreach($modules as $module)
			{
				$modules_html .= "	<tr>
		<td title='" . $module["id"] . "'>" . $module["name"] . "</td>
		<td>" . $module["version"] . "</td>
		<td>" . $module["author"] . "</td>
		<td>" . $module["description"] . "</td>
	</tr>\n";
			}
			$modules_html .= "</table>";
			//////////////////////////////
			
			$title = "Credits - $settings->sitename";
			$content = "<h1>$settings->sitename credits</h1>
	<p>$settings->sitename is powered by Pepperminty Wiki - an entire wiki packed inside a single file, which was built by <a href='//starbeamrainbowlabs.com'>Starbeamrainbowlabs</a>, and can be found <a href='//github.com/sbrl/Pepperminty-Wiki/'>on GitHub</a> (contributors will also be listed here in the future). Pepperminty Wiki is licensed under the <a target='_blank' href='https://www.mozilla.org/en-US/MPL/2.0/'>Mozilla Public License 2.0</a> (<a target='_blank' href='https://tldrlegal.com/license/mozilla-public-license-2.0-(mpl-2)'>simple version</a>).</p>
	<h2>Main Credits</h2>
	$credits_html
	<h2>Site status</h2>
	<table>
		<tr><th>Site name:</th><td>$settings->sitename (<a href='?action=update'>{$settings->admindisplaychar}Update</a>, <a href='?action=configure'>{$settings->admindisplaychar} &#x1f527; Edit master settings</a>, <a href='?action=user-table'>{$settings->admindisplaychar} &#x1f465; Edit user table</a>, <a href='?action=export'>&#x1f3db; Export as zip - Check for permission first</a>)</td></tr>
		<tr><th>Pepperminty Wiki version:</th><td>$version</td></tr>
		<tr><th>Number of pages:</th><td>" . count(get_object_vars($pageindex)) . "</td></tr>
		<tr><th>Number of modules:</th><td>" . count($modules) . "</td></tr>\n";
			if(module_exists("page-sitemap")) {
				$content .= "<tr><th>Sitemap:</th><td><a href='?action=sitemap'>View</a>";
				if($env->is_admin)
					$content .= " | Don't forget to add <code>Sitemap: http://example.com/path/to/index.php?action=sitemap</code> to your <code>robots.txt</code>";
				$content .= "</td></tr>";
			}
			$content .= "\t</table>
		<h2>Installed Modules</h2>
	$modules_html";
			exit(page_renderer::render_main($title, $content));
		});
	}
]);

?>
