<?php
register_module([
	"name" => "User watchlists",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds per-user watchlists. When a page on a user's watchlist is edited, a notification email is sent.",
	"id" => "feature-watchlist",
	"code" => function() {
		/**
		 * @api {get} ?action=watchlist&foormat=format		Get your watchlist
		 * @apiName Watchlist
		 * @apiGroup Settings
		 * @apiPermission User
		 * 
		 * @apiParam {string}	format	The format to return the watchlist in.
		 * 
		 * @apiError	WatchlistsDisabled	Watchlists are disabled because the watchlists_enable setting is set to false.
		 * @apiError	NoEmailAddress		The currently logged in user doesn't have an email address specified in their account.
		 */
		
		/*
		 * ██     ██  █████  ████████  ██████ ██   ██ ██      ██ ███████ ████████
		 * ██     ██ ██   ██    ██    ██      ██   ██ ██      ██ ██         ██
		 * ██  █  ██ ███████    ██    ██      ███████ ██      ██ ███████    ██
		 * ██ ███ ██ ██   ██    ██    ██      ██   ██ ██      ██      ██    ██
		 *  ███ ███  ██   ██    ██     ██████ ██   ██ ███████ ██ ███████    ██
		 */
		add_action("watchlist", function() {
			global $settings, $env;
			
			if(!$settings->watchlists_enable) {
				http_response_code(403);
				exit(page_renderer::render_main("Watchlists disabled - $settings->sitename", "<p>Sorry, but watchlists are currently disabled on $settings->sitename. Contact your moderators to learn - their details are at the bottom of every page.</p>"));
			}
			
			if(!$env->is_logged_in) {
				http_response_code(401);
				exit(page_renderer::render_main("Not logged in - $settings->sitename", "<p>Only logged in users can have watchlists. Try <a href='?action=login&amp;returnto=".rawurlencode("?action=watchlist")."'>logging in</a>."));
			}
			
			if(empty($env->user_data->emailAddress)) {
				http_response_code(422);
				exit(page_renderer::render_main("No email address specified -$settings->sitename", "<p>You are logged in, but have not specified an email address to send notifications to. Try specifying one in your <a href='?action=user-preferences'>user preferences</a> and then coming back here.</p>"));
			}
			
			$format = $_GET["format"] ?? "html";
			
			$watchlist = [];
			if(!empty($env->user_data->watchlist))
				$watchlist = $env->user_data->watchlist;
			
			$mime_type = "text/html";
			$content = "";
			switch ($format) {
				case "html":
					$content .= "<h1>Watchlist</h1>";
					if(!empty($watchlist)) {
						$content .= "<ul class='page-list watchlist'>\n";
						foreach($watchlist as $pagename) {
							$content .= "<li><a href='?action=watchlist-edit&page=".rawurlencode($pagename)."&do=remove'>&#x274c;</a> <a href='?page=".rawurlencode($pagename)."'>".htmlentities($pagename)."</a></li>";
						}
						$content .= "</ul>";
						$content .= "<p>You can also <a href='?action=watchlist&do=clear'>clear your entire list</a> and start again.</p>";
					}
					else {
						$content .= "<p><em>You don't have any pages on your watchlist. Try visiting some pages and adding them to your watchlist and then coming back here.</em></p>";
					}
					$content = page_renderer::render_main("Watchlist - $settings->sitename", $content);
					break;
				
				case "text":
					$mime_type = "text/plain";
					foreach($watchlist as $pagename)
						$content .= "$pagename\n";
					break;
				
				case "json":
					$mime_type = "application/json";
					$content = json_encode($watchlist);
					break;
					
				default:
					http_response_code(400);
					header("content-type: text/plain");
					exit("Sorry, the format '$format' wasn't recognised. This action currently supports these formats: html, json, text");
					break;
			}
			
			header("content-type: $mime_type");
			header("content-length: " . strlen($content));
			exit($content);
		});
		
		
		/**
		 * @api {get} ?action=watchlist-edit&do={do_verb} Edit your watchlist
		 * @apiName WatchlistEdit
		 * @apiGroup Settings
		 * @apiPermission User
		 *
		 * TODO: Finish filling this out
		 * @apiParam {string}	string	The string to hash.
		 * @apiParam {boolean}	raw		Whether to return the hashed password as a raw string instead of as part of an HTML page.
		 *
		 * @apiError	ParamNotFound	The string parameter was not specified.
		 */
		
		/*
		 * ███████ ██████  ██ ████████
		 * ██      ██   ██ ██    ██
		 * █████   ██   ██ ██    ██
		 * ██      ██   ██ ██    ██
		 * ███████ ██████  ██    ██
		 */
		add_action("watchlist-edit", function () {
			// TODO: Fill this in
		});
	}
]);

?>
