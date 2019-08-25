<?php
register_module([
	"name" => "Theme Gallery",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a theme gallery page and optional automatic theme updates.",
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
			global $settings;
			
			$themes_available = [];
			$gallery_urls = explode(" ", $settings->css_theme_gallery_index_url);
			foreach($gallery_urls as $url) {
				if(empty($url)) continue;
				$next_obj = json_decode(file_get_contents($url));
				if($next_obj === null) {
					http_response_code(503);
					exit(page_renderer::render_main("Error - Theme Gallery - $settings->sitename", "<p>Error: Failed to download theme index file from <code>" . htmlentities($url) . "</code>."));
				}
				
				$themes_available = array_merge(
					$themes_available,
					$next_obj
				);
			}
			
			$content = "<h1>Theme Gallery</h1>
			<div class='grid theme-list'>\n";
			foreach($themes_available as $theme) {
				$content .= "<div class='theme-item'>
					<input type='radio' id='" . htmlentities($theme["id"]) . "' name='theme-selector' value='" . htmlentities($theme["id"]) . "'  /><br />
					<a href='" . htmlentities($theme["preview_large"]) . "'><img src='" . htmlentities($theme["preview_small"]) . "' title='Click to enlarge.' /></a>
					<label for='" . htmlentities($theme["id"]) . "'>" . htmlentities($theme["name"]) . "</label>
					<p>" . str_replace("\n", "</p>\n<p>", htmlentities($theme["description"])) . "</p>
					<p>By <a href='" . htmlentities($theme["author_link"]) . "'>" . htmlentities($theme["author"]) . "</a> (<a href='" . htmlentities($theme["url"]) . "'>View CSS</a>)
				</div>";
			}
			$content .= "</div>";
			
			exit(page_renderer::render_main("Theme Gallery - $settings->sitename", ""));
			
		});
		
		add_help_section("26-random-redirect", "Jumping to a random page", "<p>$settings->sitename has a function that can send you to a random page. To use it, click <a href='?action=random'>here</a>. $settings->admindetails_name ($settings->sitename's adminstrator) may have added it to one of the menus.</p>");
	}
]);

?>
