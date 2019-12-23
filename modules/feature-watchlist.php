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
		 * @apiError	NotLoggedIn			You aren't logged in, so you can't edit your watchlist (only logged in users have a watchlist).
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
				header("x-problem: watchlists-disabled");
				exit(page_renderer::render_main("Watchlists disabled - $settings->sitename", "<p>Sorry, but watchlists are currently disabled on $settings->sitename. Contact your moderators to learn - their details are at the bottom of every page.</p>"));
			}
			
			if(!$env->is_logged_in) {
				http_response_code(401);
				header("x-problem: not-logged-in");
				exit(page_renderer::render_main("Not logged in - $settings->sitename", "<p>Only logged in users can have watchlists. Try <a href='?action=login&amp;returnto=".rawurlencode("?action=watchlist")."'>logging in</a>."));
			}
			
			if(empty($env->user_data->emailAddress)) {
				http_response_code(422);
				header("x-problem: no-email-address-in-user-preferences");
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
							$content .= "<li><a href='?action=watchlist-edit&amp;page=".rawurlencode($pagename)."&amp;do=remove&amp;returnto=".rawurlencode("?action=watchlist&success=yes")."'>&#x274c;</a> <a href='?page=".rawurlencode($pagename)."'>".htmlentities($pagename)."</a></li>";
						}
						$content .= "</ul>";
						$content .= "<p>You can also <a href='?action=watchlist-edit&amp;do=clear&amp;returnto=".rawurlencode("?action=watchlist")."'>clear your entire list</a> and start again.</p>";
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
		 * @api {get} ?action=watchlist-edit&do={do_verb}[&page={pagename}][&returnto=url] Edit your watchlist
		 * @apiName WatchlistEdit
		 * @apiGroup Settings
		 * @apiPermission User
		 *
		 * TODO: Finish filling this out
		 * @apiParam {string}	pagename	The name of the page to operate on.
		 * @apiParam {string}	do			The thing to do. Supported verbs: add, remove, clear. The first 2 require the page GET parameter to be specified, but the clear verb doesn't (as it clears the entire list).
		 * @apiParam {string}	returnto	Optional. Specifies a URL to redirect to (with the http status code 302) upon success.
		 *
		 * @apiError	WatchlistsDisabled	Watchlists are disabled because the watchlists_enable setting is set to false.
		 * @apiError	NotLoggedIn			You aren't logged in, so you can't edit your watchlist (only logged in users have a watchlist).
		 * @apiError	NoEmailAddress		The currently logged in user doesn't have an email address specified in their account.
		 * @apiError	DoVerbNotRecognised	The specified do verb was not recognised. Supported verbs: add, remove, clear (a canonical list is returned with this error).
		 *
		 * @apiError	PageNotFoundOnWiki		The page name specified was not found on the wiki, so it can't be watched.
		 * @apiError	PageNotFoundOnWatchlist	The page name was not found in your watchlist.
		 */
		
		/*
		 * ███████ ██████  ██ ████████
		 * ██      ██   ██ ██    ██
		 * █████   ██   ██ ██    ██
		 * ██      ██   ██ ██    ██
		 * ███████ ██████  ██    ██
		 */
		add_action("watchlist-edit", function () {
			global $settings, $env, $pageindex;
			
			// The thing we should do.
			$do = $_GET["do"] ?? "null";
			// The location we should redirect to after doing it successfully, if anywhere
			$returnto = empty($_GET["returnto"]) ? null : $_GET["returnto"];
			
			if(!$settings->watchlists_enable) {
				http_response_code(403);
				header("x-status: failed");
				header("x-problem: watchlists-disabled");
				exit(page_renderer::render_main("Watchlists disabled - $settings->sitename", "<p>Sorry, but watchlists are currently disabled on $settings->sitename. Contact your moderators to learn - their details are at the bottom of every page.</p>"));
			}
			
			if(!$env->is_logged_in) {
				http_response_code(401);
				header("x-status: failed");
				header("x-problem: not-logged-in");
				exit(page_renderer::render_main("Not logged in - $settings->sitename", "<p>Only logged in users can have watchlists. Try <a href='?action=login&amp;returnto=".rawurlencode("?action=watchlist-edit&do=$do&returnto=$returnto")."'>logging in</a>.</p>"));
			}
			
			if(empty($env->user_data->emailAddress)) {
				http_response_code(422);
				header("x-status: failed");
				header("x-problem: no-email-address-in-user-preferences");
				exit(page_renderer::render_main("No email address specified -$settings->sitename", "<p>You are logged in, but have not specified an email address to send notifications to. Try specifying one in your <a href='?action=user-preferences'>user preferences</a> and then coming back here.</p>"));
			}
			
			// If the watchlist doesn't exist, create it
			// Note that saving this isn't essential - so we don't bother unless we perform some other action too.
			if(!isset($env->user_data->watchlist) || !is_array($env->user_data->watchlist))
				$env->user_data->watchlist = [];
			
			switch($do) {
				case "add":
					if(empty($pageindex->{$env->page})) {
						http_response_code(404);
						header("x-status: failed");
						header("x-problem: page-not-found-on-wiki");
						exit(page_renderer::render_main("Page not found - Error - $settings->sitename", "<p>Oops! The page name <em>".htmlentities($env->page)."</em> couldn't be found on $settings->sitename. Try <a href='?action=edit&page=".rawurlencode($env->page)."'>creating it</a> and trying to add it to your watchlist again!</p>"));
					}
					if(in_array($env->page, $env->user_data->watchlist)) {
						http_response_code(422);
						header("x-status: failed");
						header("x-problem: watchlist-page-already-present");
						exit(page_renderer::render_main("Already on watchlist - Error - $settings->sitename", "<p>The page with the name <em>".htmlentities($env->page)."</em> is already on your watchlist, so it can't be added again.</p>"));
					}
					// Add the new page to the watchlist
					$env->user_data->watchlist[] = $env->page;
					// Sort the list
					$collator = new Collator("");
					$collator->sort($env->user_data->watchlist, SORT_NATURAL | SORT_FLAG_CASE);
					// Save back to disk
					save_settings();
					break;
				case "remove":
					$index = array_search($env->page, $env->user_data->watchlist);
					if($index === false) {
						http_response_code(400);
						header("x-status: failed");
						header("x-problem: watchlist-item-not-found");
						exit(page_renderer::render_main("Watchlist item not found - Error - $settings->sitename", "<p>Oops! The page with the name <em>".htmlentities($env->page)."</em> isn't currently on your watchlist, so it couldn't be removed. Perhaps you already removed it?</p>
						<p>Try going <a href='?action=watchlist'>back to your watchlist</a>.</p>"));
					}
					array_splice($env->user_data->watchlist, $index, 1);
					save_settings();
					break;
				case "clear":
					$env->user_data->watchlist = [];
					save_settings();
					break;
				default:
					http_response_code(400);
					header("x-status: failed");
					header("x-problem: watchlist-do-verb-not-recognised");
					header("content-type: text/plain");
					exit("Error: The do verb '$do' wasn't recognised. Current verbs supported: add, remove, clear");
			}
			
			$message = "Your watchlist was updated successfully.";
			if(!empty($returnto)) {
				http_response_code(302);
				header("x-status: success");
				header("location: $returnto");
				$message .= " <a href='".htmlentities($returnto)."'>Click here</a> to return to your previous page.";
			}
			else
				$message .= " <a href='javascript:history.back();'>Go back</a> to your previous page, or <a href='?action=watchlist'>review your watchlist</a>.</a>";
			exit(page_renderer::render_main("Watchlist update successful", "<p>$message</p>"));
		});
		
		if(!module_exists("page-edit")) {
			error_log("[module/feature-watchlist] Note: Without the page-edit module, the feature-watchlist module doesn't make much sense. If you don't want anonymous people to edit your wiki, try the 'anonedits' setting.");
			return false;
		}
		
		register_save_preprocessor(function($indexentry, $new_data, $old_data) {
			global $version, $commit, $env, $settings;
			
			$usernames = [];
			foreach($settings->users as $username => $user_data) {
				if(empty($user_data->watchlist))
					continue;
				
				if(!in_array($env->page, $user_data->watchlist))
					continue;
				
				$usernames[] = $username;
			}
			
			$chars_changed = strlen($new_data) - strlen($old_data);
			$chars_changed_text = ($chars_changed < 0 ? "removes " : "adds ") . "$chars_changed characters";
			
			// Calculate the stem from the current full URL by stripping everything after the question mark ('?')
			$url_stem = full_url();
			if(mb_strrpos($url_stem, "?") !== false) $url_steam = mb_substr($url_stem, mb_strrpos($url_stem, "?"));
			
			email_users(
				$usernames,
				"{$env->page} was updated by {$env->user} - $settings->sitename",
				"Hey there!

{$env->page} was updated by {$env->user} at ".render_timestamp(time(), true, false).", which $chars_changed_text.

View the latest revision here: {$url_stem}?page=".rawurlencode($env->page)."

---------- New page text ----------
$new_data
-----------------------------------

--$settings->sitename, powered by Pepperminty Wiki $version-".substr($commit, 0, 7)."

(P.S. Don't reply to this email, because it may not recieve a reply. Instead try contacting $settings->admindetails_name at $settings->admindetails_email, who is $settings->sitename's administrator if you have any issues.)
"
			);
		});
	}
]);

?>
