<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Theme Gallery",
	"version" => "0.4.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a theme gallery page and optional automatic theme updates. Contacts a remote server, where IP addresses are stored in automatic server logs for security and attack mitigation purposes.",
	"id" => "feature-theme-gallery",
	"code" => function() {
		global $settings;
		/**
		 * @api {get} ?action=theme-gallery Display the theme gallery
		 * @apiName ThemeGallery
		 * @apiGroup Utility
		 * @apiPermission Moderator
		 */
		
		add_action("theme-gallery", function() {
			global $settings, $env;
			
			if(!$env->is_admin) {
				$errorMessage = "<p>You don't have permission to change $settings->sitename's theme.</p>\n";
				if(!$env->is_logged_in)
					$errorMessage .= "<p>You could try <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a>.</p>";
				else
					$errorMessage .= "<p>You could try <a href='?action=logout&returnto=%3Faction%3Dconfigure'>logging out</a> and then <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a> again with a different account that has the appropriate privileges.</a>.</p>";
				exit(page_renderer::render_main("Error - $settings->sitename", $errorMessage));
			}
			
			$gallery_urls = explode(" ", $settings->css_theme_gallery_index_url);
			
			if(!isset($_GET["load"]) || $_GET["load"] !== "yes") {
				$result = "<h1>Theme Gallery</h1>
				<p>Load the theme gallery? A HTTP request will be made to the following endpoints:</p>
				<ul>";
				foreach($gallery_urls as $url) {
					$result .= "<li><a href='".htmlentities($url)."'>".htmlentities($url)."</a></li>\n";
				}
				$result .= "</ul>
				<p>...with the following user agent string: <code>".ini_get("user_agent")."</code></p>
				<p>No external HTTP requests will be made without your consent.</p>
				<p><a href='?action=theme-gallery&load=yes'>Ok, load the gallery</a>.</p>
				<p> <a href='javascript:history.back();'>Actually, take me back</a>.</p>";
				exit(page_renderer::render_main("Theme Gallery - $settings->sitename", $result));
			}
			
			$themes_available = [];
			
			foreach($gallery_urls as $url) {
				if(empty($url)) continue;
				$next_obj = json_decode(@file_get_contents($url));
				if(empty($next_obj)) {
					http_response_code(503);
					exit(page_renderer::render_main("Error - Theme Gallery - $settings->sitename", "<p>Error: Failed to download theme index file from <code>" . htmlentities($url) . "</code>."));
				}
				
				foreach($next_obj as $theme) {
					$theme->index_url = $url;
					$theme->root = dirname($url) . "/{$theme->id}";
					$theme->url = "{$theme->root}/theme.css";
					$theme->preview_large = "{$theme->root}/preview_large.png";
					$theme->preview_small = "{$theme->root}/preview_small.png";
					$themes_available[] = $theme;
				}
			}
			
			$sorter = new Collator("");
			usort($themes_available, function($a, $b) use ($sorter) : int {
				return $sorter->compare($a->name, $b->name);
			});
			
			
			
			$content = "<h1>Theme Gallery</h1>
			<p>$settings->sitename is currently using ".(strlen($settings->css_theme_autoupdate_url) > 0 ? "an external" : "the internal")." theme".(strlen($settings->css_theme_autoupdate_url) > 0 ? " (<a href='?action=theme-gallery-select&amp;theme-selector=default-internal'>reset to the internal default theme</a>)" : "").".</p>
			<form method='get' action='index.php'>
			<input type='hidden' name='action' value='theme-gallery-select' />
			<div class='grid-large theme-list'>\n";
			foreach($themes_available as $theme) {
				$selected = $theme->id == $settings->css_theme_gallery_selected_id ? " selected" : "";
				$content .= "<div class='theme-item'>
					<a href='" . htmlentities($theme->preview_large) . "'><img src='" . htmlentities($theme->preview_small) . "' title='Click to enlarge' /></a><br />
					<input type='radio' id='" . htmlentities($theme->id) . "' name='theme-selector' value='" . htmlentities($theme->id) . "' required$selected />
					<label class='link-display-label' for='" . htmlentities($theme->id) . "'>" . htmlentities($theme->name) . "</label>
					<p>" . str_replace("\n", "</p>\n<p>", htmlentities($theme->description)) . "</p>
					<p>By <a href='" . htmlentities($theme->author_link) . "'>" . htmlentities($theme->author) . "</a> (<a href='" . htmlentities($theme->url) . "'>View CSS</a>, <a href='" . htmlentities($theme->index_url) . "'>View Index</a>)
				</div>";
			}
			$content .= "</div>
			<p><strong>Warning: If you've altered $settings->sitename's CSS by changing the value of the <code>css</code> setting, then your changes will be overwritten by clicking the button below! If necessary, move your changes to the <code>css_custom</code> setting first before continuing here.</strong></p>
			<input type='submit' class='large' value='Change Theme' />
			</form>";
			
			exit(page_renderer::render_main("Theme Gallery - $settings->sitename", "$content"));
			
		});
		
		/**
		 * @api {get} ?action=theme-gallery-select&theme-selector=theme-id	Set the site theme
		 * @apiName ThemeGallerySelect
		 * @apiGroup Utility
		 * @apiPermission Moderator
		 * 
		 * @apiParam	{string}	theme-selector	The id of the theme to switch into, or 'default-internal' to switch back to the internal theme.
		 */
		add_action("theme-gallery-select", function() {
			global $env, $settings, $guiConfig;
			
			if(!$env->is_admin) {
				$errorMessage = "<p>You don't have permission to change $settings->sitename's theme.</p>\n";
				if(!$env->is_logged_in)
					$errorMessage .= "<p>You could try <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a>.</p>";
				else
					$errorMessage .= "<p>You could try <a href='?action=logout&returnto=%3Faction%3Dconfigure'>logging out</a> and then <a href='?action=login&returnto=%3Faction%3Dconfigure'>logging in</a> again with a different account that has the appropriate privileges.</a>.</p>";
				exit(page_renderer::render_main("Error - $settings->sitename", $errorMessage));
			}
			
			if(!isset($_GET["theme-selector"])) {
				http_response_code(400);
				exit(page_renderer::render_main("No theme selected - Error - $settings->sitename", "<p>Oops! Looks like you didn't select a theme. Try <a href='?action=theme-gallery'>going back</a> and selecting one.</p>"));
			}
			
			if($_GET["theme-selector"] === "default-internal") {
				$settings->css_theme_gallery_selected_id = $guiConfig->css_theme_gallery_selected_id->default;
				$settings->css_theme_autoupdate_url = $guiConfig->css_theme_autoupdate_url->default;
				$settings->css = $guiConfig->css->default;
				
				if(!save_settings()) {
					http_response_code(503);
					exit(page_renderer::render_main("Server error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to save the <code>peppermint.json</code> settings file back to disk. If you're the administrator, try checking the permissions on disk. If not, try contacting $settings->sitename's administrator, who's contact details can be found at the bottom of every page.</p>"));
				}
				
				exit(page_renderer::render_main("Theme reset - Theme Gallery - $settings->sitename", "<p>$settings->sitename's theme has been reset  to the internal theme.</p>
				<p>Go to the <a href='?action=$settings->defaultaction'>homepage</a>.</p>"));
			}
			
			// Set the new theme's id
			$settings->css_theme_gallery_selected_id = $_GET["theme-selector"];
			$gallery_urls = explode(" ", $settings->css_theme_gallery_index_url);
			
			// Find the URL of the selected theme
			// FUTURE: Figure out a way to pass this information through the UI interface instead to avoid a re-download?
			$theme_autoupdate_url = null;
			foreach($gallery_urls as $url) {
				$next_index = json_decode(@file_get_contents($url));
				if(empty($next_index)) {
					error_log("[PeppermintyWiki/$settings->sitename/theme_gallery] Error: Failed to download theme index file from '$url' when setting the wiki theme.");
					continue;
				}
				foreach($next_index as $next_theme) {
					if($next_theme->id == $settings->css_theme_gallery_selected_id) {
						$theme_autoupdate_url = dirname($url) . "/{$next_theme->id}/theme.css";
						break;
					}
				}
				if($theme_autoupdate_url !== null) break;
			}
			if($theme_autoupdate_url === null) {
				http_response_code(503);
				exit(page_renderer::render_main("[PeppermintyWiki/$settings->sitename/theme_gallery] Failed to set theme - Error - $settings->sitename)", "<p>Oops! $settings->sitename couldn't find the theme you selected. Perhaps it has been changed or deleted, or perhaps there was an error during the download process.</p>
				<p>Try <a href='?action=theme-gallery'>heading back to the theme gallery</a> and trying again.</p>"));
			}
			$settings->css_theme_autoupdate_url = $theme_autoupdate_url;
			
			if(!theme_update(true)) {
				http_response_code(503);
				exit(page_renderer::render_main("Failed to download theme - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to download the theme you selected. If you're the administrator, try checking the PHP server logs. If not, try contacting $settings->sitename's administrator, who's contact details can be found at the bottom of every page.</p>"));
			}
			
			// TODO: Add option to disable theme updates
			
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Server error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to save the <code>peppermint.json</code> settings file back to disk. If you're the administrator, try checking the permissions on disk. If not, try contacting $settings->sitename's administrator, who's contact details can be found at the bottom of every page.</p>"));
			}
			
			http_response_code(200);
			exit(page_renderer::render_main("Theme Changed - $settings->sitename", "<p>$settings->sitename's theme was changed successfully to $settings->css_theme_gallery_selected_id.</p>
			<p>Go to the <a href='?action=$settings->defaultaction'>homepage</a>.</p>"));
		});
		
		// TODO: Fill this in properly
		add_help_section("26-random-redirect", "Jumping to a random page", "<p>$settings->sitename has a function that can send you to a random page. To use it, click <a href='?action=random'>here</a>. $settings->admindetails_name ($settings->sitename's adminstrator) may have added it to one of the menus.</p>");
	}
]);

/**
 * Updates the currently selected theme by fetching it from a remote url.
 * @param	bool	$force_update Whether to force an update - even if we've already updated recently.
 * @return	bool	Whether the update was sucessful. It might fail because of network issues, or the theme update requires a newer version of Pepperminty Wiki than is currently installed.
 */
function theme_update($force_update = false) : bool {
	global $version, $settings;
	
	// If there's no url to update from or updates are disabled, then we're done here
	if(empty($settings->css_theme_autoupdate_url) || $settings->css_theme_autoupdate_interval < 0)
		return true;
	
	// If it's not time for an update, then end here
	// ...unless we're supposed to force an update
	if(time() - $settings->css_theme_autoupdate_lastcheck < $settings->css_theme_autoupdate_interval || !$force_update)
		return true;
	
	// Fetch the new css
	$new_css = @file_get_contents($settings->css_theme_autoupdate_url);
	// Make sure it's valid
	if(empty($new_css)) {
		error_log("[PeppermintyWiki/$settings->sitename/theme_gallery] Error: Failed to update theme: Got an error while trying to download theme update from $settings->css_theme_autoupdate_url");
		return false;
	}
	
	// TODO: Check the hash against themeindex.json?
	
	$min_version_loc = strpos($new_css, "@minversion") + strlen("@minversion");
	$min_version = substr($new_css, $min_version_loc, strpos($new_css, "\n", $min_version_loc));
	if(version_compare($version, $min_version) == -1) {
		error_log("[PeppermintyWiki/$settings->sitename/theme_gallery] Error: Failed to update theme: $settings->css_theme_gallery_selected_id requires Pepperminty Wiki $min_version, but $version is installed.");
		return false;
	}
	
	// If the css is identical to the string we've got stored already, then no point in updating
	if($new_css == $settings->css)
		return true;
	
	$settings->css = $new_css;
	
	return save_settings();
}

?>
