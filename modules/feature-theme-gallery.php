<?php
register_module([
	"name" => "Theme Gallery",
	"version" => "0.3",
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
			
			$themes_available = [];
			$gallery_urls = explode(" ", $settings->css_theme_gallery_index_url);
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
			<input type='submit' class='large' value='Change Theme' />
			</form>";
			
			exit(page_renderer::render_main("Theme Gallery - $settings->sitename", "$content"));
			
		});
		
		add_action("theme-gallery-select", function() {
			global $env, $settings;
			
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
			
			// Set the new theme's id
			$settings->css_theme_gallery_selected_id = $_GET["theme-selector"];
			
			// TODO: Set the autoupdate_url here
			// TODO: Add option to disable theme updates
			
			if(!save_settings()) {
				http_response_code(503);
				exit(page_renderer::render_main("Server error - $settings->sitename", "<p>Oops! $settings->sitename wasn't able to save the <code>peppermint.json</code> settings file back to disk. If you're the administrator, try checking the permissions on disk. If not, try contacting $settings->sitename's administrator, who's contact details can be found at the bottom of every page.</p>"));
			}
			
			
		});
		
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
		error_log("[Pepperminty Wiki/$settings->sitename] Error: Failed to update theme: Got an error while trying to download theme update from $settings->css_theme_autoupdate_url");
		return false;
	}
	
	// TODO: Check the hash against themeindex.json?
	
	$min_version_loc = strpos($new_css, "@minversion") + strlen("@minversion");
	$min_version = substr($new_css, $min_version_loc, strpos($new_css, "\n", $min_version_loc));
	if(version_compare($version, $min_version) == -1) {
		error_log("[Pepperminty Wiki/$settings->sitename] Error: Failed to update theme: $settings->css_theme_gallery_selected_id requires Pepperminty Wiki $min_version, but $version is installed.");
		return false;
	}
	
	// If the css is identical to the string we've got stored already, then no point in updating
	if($new_css == $settings->css)
		return true;
	
	
}

?>
