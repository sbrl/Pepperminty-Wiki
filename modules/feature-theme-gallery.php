<?php
register_module([
	"name" => "Theme Gallery",
	"version" => "0.1",
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
				$next_obj = json_decode(file_get_contents($url));
				if($next_obj === null) {
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
			
			usort($themes_available, function($a, $b) {
				return strcmp($a->name, $b->name);
			});
			
			$content = "<h1>Theme Gallery</h1>
			
			<form method='get' action='theme-gallery-select'>
			<div class='grid-large theme-list'>\n";
			foreach($themes_available as $theme) {
				$selected = $theme->id == $settings->css_theme_gallery_selected_id ? " selected" : "";
				$content .= "<div class='theme-item'>
					<a href='" . htmlentities($theme->preview_large) . "'><img src='" . htmlentities($theme->preview_small) . "' title='Click to enlarge' /></a><br />
					<input type='radio' id='" . htmlentities($theme->id) . "' name='theme-selector' value='" . htmlentities($theme->id) . "'$selected />
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
		
		add_help_section("26-random-redirect", "Jumping to a random page", "<p>$settings->sitename has a function that can send you to a random page. To use it, click <a href='?action=random'>here</a>. $settings->admindetails_name ($settings->sitename's adminstrator) may have added it to one of the menus.</p>");
	}
]);

?>
