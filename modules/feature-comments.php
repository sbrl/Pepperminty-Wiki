<?php
register_module([
	"name" => "Page Comments",
	"version" => "0.3.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds threaded comments to the bottom of every page.",
	"id" => "feature-comments",
	"code" => function() {
		global $env, $settings;
		
		/**
		 * @api {post} ?action=comment	Comment on a page
		 * @apiName Comment
		 * @apiGroup Comment
		 * @apiPermission User
		 * @apiDescription	Posts a comment on a page, optionally in reply to another comment. Currently, comments must be made by a logged-in user.
		 * 
		 * @apiParam {string}	message	The comment text. Supports the same syntax that the renderer of the main page supports. The default is extended markdown - see the help page of the specific wiki for more information.
		 * @apiParam {string}	replyto	Optional. If specified the comment will be posted in reply to the comment with the specified id.
		 * 
		 *
		 * @apiError	CommentNotFound	The comment to reply to was not found.
		 */
		
		/*
		 *   ██████  ██████  ███    ███ ███    ███ ███████ ███    ██ ████████
		 *  ██      ██    ██ ████  ████ ████  ████ ██      ████   ██    ██
		 *  ██      ██    ██ ██ ████ ██ ██ ████ ██ █████   ██ ██  ██    ██
		 *  ██      ██    ██ ██  ██  ██ ██  ██  ██ ██      ██  ██ ██    ██
		 *   ██████  ██████  ██      ██ ██      ██ ███████ ██   ████    ██
		 */
		add_action("comment", function() {
			global $settings, $env;
			
			$reply_to = $_POST["replyto"] ?? null;
			$message = $_POST["message"] ?? "";
			
			if(!$env->is_logged_in) {
				http_response_code(401);
				exit(page_renderer::render_main("Error posting comment - $settings->sitename", "<p>Your comment couldn't be posted because you're not logged in. You can login <a href='?action=index'>here</a>. Here's the comment you tried to post:</p>
				<textarea readonly>$message</textarea>"));
			}
			
			$message_length = strlen($message);
			if($message_length < $settings->comment_min_length) {
				http_response_code(422);
				exit(page_renderer::render_main("Error posting comment - $settings->sitename", "<p>Your comment couldn't be posted because it was too short. $settings->sitename needs at $settings->comment_min_length characters in a comment in order to post it.</p>"));
			}
			if($message_length > $settings->comment_max_length) {
				http_response_code(422);
				exit(page_renderer::renderer_main("Error posting comment - $settings->sitename", "<p>Your comment couldn't be posted because it was too long. $settings->sitenamae can only post comments that are up to $settings->comment_max_length characters in length, and yours was $message_length characters. Try splitting it up into multiple comments! Here's the comment you tried to post:</p>
				<textarea readonly>$message</textarea>"));
			}
			
			// Figure out where the comments are stored
			$comment_filename = get_comment_filename($env->page);
			if(!file_exists($comment_filename)) {
				if(file_put_contents($comment_filename, "[]\n") === false) {
					http_response_code(503);
					exit(page_renderer::renderer_main("Error posting comment - $settings->sitename", "<p>$settings->sitename ran into a problem whilst creating a file to save your comment to! Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>$settings->admindetails_name</a>, $settings->sitename's administrator and tell them about this problem.</p>"));
				}
			}
			
			$comment_data = json_decode(file_get_contents($comment_filename));
			
			$new_comment = new stdClass();
			$new_comment->id = generate_comment_id();
			$new_comment->timestamp = date("c");
			$new_comment->username = $env->user;
			$new_comment->logged_in = $env->is_logged_in;
			$new_comment->message = $message;
			$new_comment->replies = [];
			
			if($reply_to == null)
				$comment_data[] = $new_comment;
			else {
				$parent_comment = find_comment($comment_data, $reply_to);
				if($parent_comment === false) {
					http_response_code(422);
					exit(page_renderer::render_main("Error posting comment - $settings->sitename", "<p>$settings->sitename couldn't post your comment because it couldn't find the parent comment you replied to. It's possible that $settings->admindetails_name, $settings->sitename's administrator, deleted the comment. Here's the comment you tried to post:</p>
					<textarea readonly>$message</textarea>"));
				}
				
				$parent_comment->replies[] = $new_comment;
				
				// Get an array of all the parent comments we need to notify
				$comment_thread = fetch_comment_thread($comment_data, $parent_comment->id);
				
				$email_subject = "[Notification] $env->user replied to your comment on $env->page - $settings->sitename";
				
				foreach($comment_thread as $thread_comment) {
					$email_body = "Hello, {username}!\n" . 
						"It's $settings->sitename here, letting you know that " . 
						"someone replied to your comment (or a reply to your comment) on $env->page.\n" . 
						"\n" . 
						"They said:\n" . 
						"\n" . 
						"$new_comment->message" . 
						"\n" . 
						"You said on " . date("c", strtotime($thread_comment->timestamp)) . ":\n" . 
						"\n" . 
						"$thread_comment->message\n" . 
						"\n";
					
					email_user($thread_comment->username, $email_subject, $email_body);
				}
			}
			
			// Save the comments back to disk
			if(file_put_contents($comment_filename, json_encode($comment_data, JSON_PRETTY_PRINT)) === false) {
				http_response_code(503);
				exit(page_renderer::renderer_main("Error posting comment - $settings->sitename", "<p>$settings->sitename ran into a problem whilst saving your comment to disk! Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>$settings->admindetails_name</a>, $settings->sitename's administrator and tell them about this problem.</p>"));
			}
			
			// Add a recent change if the recent changes module is installed
			if(module_exists("feature-recent-changes")) {
				add_recent_change([
					"type" => "comment",
					"timestamp" => time(),
					"page" => $env->page,
					"user" => $env->user,
					"reply_depth" => count($comment_thread),
					"comment_id" => $new_comment->id
				]);
			}
			
			http_response_code(307);
			header("location: ?action=view&page=" . rawurlencode($env->page) . "&commentsuccess=yes#comment-$new_comment->id");
			exit(page_renderer::render_main("Comment posted successfully - $settings->sitename", "<p>Your comment on $env->page was posted successfully. If your browser doesn't redirect you automagically, please <a href='?action=view&page=" . rawurlencode($env->page) . "commentsuccess=yes#comment-$new_comment->id'>click here</a> to go to the comment you posted on the page you were viewing.</p>"));
		});
		
		
		/**
		 * @api {post} ?action=comment-delete&page={page_name}&delete_id={id_to_delete}	Delete a comment
		 * @apiName CommentDelete
		 * @apiGroup Comment
		 * @apiPermission User
		 * @apiDescription	Deletes a comment with the specified id. If you aren't the one who made the comment in the first place, then you must be a moderator or better to delete it.
		 * 
		 * @apiUse		PageParameter
		 * @apiParam	{string}	delete_id	The id of the comment to delete.
		 * 
		 * @apiError	CommentNotFound	The comment to delete was not found.
		 */
		
		/*
		 *  ██████  ██████  ███    ███ ███    ███ ███████ ███    ██ ████████
		 * ██      ██    ██ ████  ████ ████  ████ ██      ████   ██    ██
		 * ██      ██    ██ ██ ████ ██ ██ ████ ██ █████   ██ ██  ██    ██ █████
		 * ██      ██    ██ ██  ██  ██ ██  ██  ██ ██      ██  ██ ██    ██
		 *  ██████  ██████  ██      ██ ██      ██ ███████ ██   ████    ██
		 * ██████  ███████ ██      ███████ ████████ ███████
		 * ██   ██ ██      ██      ██         ██    ██
		 * ██   ██ █████   ██      █████      ██    █████
		 * ██   ██ ██      ██      ██         ██    ██
		 * ██████  ███████ ███████ ███████    ██    ███████
		 */
		add_action("comment-delete", function () {
			global $env, $settings;
			
			
			if(!isset($_GET["delete_id"])) {
				http_response_code(400);
				exit(page_renderer::render_main("Error - Deleting Comment - $settings->sitename", "<p>You didn't specify the id of a comment to delete.</p>"));
			}
			
			// Make sure that the user is logged in before deleting a comment
			if(!$env->is_logged_in) {
				http_response_code(307);
				header("location: ?action=login&returnto=" . rawurlencode("?action=comment-delete&page=" . rawurlencode($env->page) . "&id=" . rawurlencode($_GET["delete_id"])));
			}
			
			$comment_filename = get_comment_filename($env->page);
			$comments = json_decode(file_get_contents($comment_filename));
			$target_id = $_GET["delete_id"];
			
			$comment_to_delete = find_comment($comments, $target_id);
			if($comment_to_delete->username !== $env->user && !$env->is_admin) {
				http_response_code(401);
				exit(page_renderer::render_main("Error - Deleting Comment - $settings->sitename", "<p>You can't delete the comment with the id <code>" . htmlentities($target_id) . "</code> on the page <em>$env->page</em> because you're logged in as " . page_renderer::render_username($env->user) . ", and " . page_renderer::render_username($comment_to_delete->username) . " made that comment. Try <a href='?action=logout'>Logging out</a> and then logging in again as " . page_renderer::render_username($comment_to_delete->username) . ", or as a moderator or better."));
			}
			
			if(!delete_comment($comments, $_GET["delete_id"])) {
				http_response_code(404);
				exit(page_renderer::render_main("Comment not found - Deleting Comment - $settings->sitename", "<p>The comment with the id <code>" . htmlentities($_GET["delete_id"]) . "</code> on the page <em>$env->page</em> wasn't found. Perhaps it was already deleted?</p>"));
			}
			
			if(!file_put_contents($comment_filename, json_encode($comments))) {
				http_response_code(503);
				exit(page_renderer::render_main("Server Error - Deleting Comment - $settings->sitename", "<p>While $settings->sitename was able to delete the comment with the id <code>" . htmlentities($target_id) . "</code> on the page <em>$env->page</em>, it couldn't save the changes back to disk. Please contact <a href='mailto:" . hide_email($settings->admindetails_email) . "'>$settings->admindetails_name</a>, $settings->sitename's local friendly administrator about this issue.</p>"));
			}
			
			exit(page_renderer::render_main("Comment Deleted - $settings->sitename", "<p>The comment with the id <code>" . htmlentities($target_id) . "</code> on the page <em>$env->page</em> has been deleted successfully. <a href='?page=" . rawurlencode($env->page) . "&redirect=no'>Go back</a> to " . htmlentities($env->page) . ".</p>"));
		});
		
		if($env->action == "view") {
			page_renderer::register_part_preprocessor(function(&$parts) {
				global $env;
				$comments_filename = get_comment_filename($env->page);
				$comments_data = file_exists($comments_filename) ? json_decode(file_get_contents($comments_filename)) : [];
				
				
				$comments_html = "<aside class='comments'>" . 
					"<h2 id='comments'>Comments</h2>\n";
				
				if($env->is_logged_in) {
					$comments_html .= "<form class='comment-reply-form' method='post' action='?action=comment&page=" . rawurlencode($env->page) . "'>\n" . 
						"<h3>Post a Comment</h3>\n" . 
						"\t<textarea name='message' placeholder='Type your comment here. You can use the same syntax you use when writing pages.'></textarea>\n" . 
						"\t<input type='hidden' name='replyto' />\n" . 
						"\t<input type='submit' value='Post Comment' />\n" . 
						"</form>\n";
				}
				else {
					$comments_html .= "<form class='comment-reply-form disabled no-login'>\n" . 
					"\t<textarea disabled name='message' placeholder='Type your comment here. You can use the same syntax you use when writing pages.'></textarea>\n" . 
					"\t<p class='not-logged-in'><a href='?action=login&returnto=" . rawurlencode("?action=view&page=" . rawurlencode($env->page) . "#comments") . "'>Login</a> to post a comment.</p>\n" . 
					"\t<input type='hidden' name='replyto' />\n" . 
					"\t<input disabled type='submit' value='Post Comment' title='Login to post a comment.' />\n" . 
					"</form>\n";
				}
				
				$comments_html .= render_comments($comments_data);
				
				$comments_html .= "</aside>\n";
				
				$to_comments_link = "<div class='jump-to-comments'><a href='#comments'>Jump to comments</a></div>";
				
				$parts["{extra}"] = $comments_html . $parts["{extra}"];
				
				$parts["{content}"] = str_replace_once("</h1>", "</h1>\n$to_comments_link", $parts["{content}"]);
			});
			
			$reply_js_snippet = <<<'REPLYJS'
///////////////////////////////////
///////// Commenting Form /////////
///////////////////////////////////
window.addEventListener("load", function(event) {
	var replyButtons = document.querySelectorAll(".reply-button");
	for(let i = 0; i < replyButtons.length; i++) {
		replyButtons[i].addEventListener("click", display_reply_form);
		replyButtons[i].addEventListener("touchend", display_reply_form);
	}
});

function display_reply_form(event)
{
	// Deep-clone the comment form
	var replyForm = document.querySelector(".comment-reply-form").cloneNode(true);
	replyForm.classList.add("nested");
	// Set the comment we're replying to
	replyForm.querySelector("[name=replyto]").value = event.target.parentElement.parentElement.parentElement.dataset.commentId;
	// Display the newly-cloned commenting form
	var replyBoxContiner = event.target.parentElement.parentElement.parentElement.querySelector(".reply-box-container");
	replyBoxContiner.classList.add("active");
	replyBoxContiner.appendChild(replyForm);
	// Hide the reply button so it can't be pressed more than once - that could
	// be awkward :P
	event.target.parentElement.removeChild(event.target);
}

REPLYJS;
			page_renderer::AddJSSnippet($reply_js_snippet);
			
		}
		
		add_help_section("29-commenting", "Commenting", "<p>$settings->sitename has a threaded commenting system on every page. You can find it below each page's content, and can either leave a new comment, or reply to an existing one. If you reply to an existing one, then the authors of all the comments above yours will get notified by email of your reply - so long as they have an email address registered in their preferences.</p>");
	}
]);

/**
 * Given a page name, returns the absolute file path in which that page's
 * comments are stored.
 * @package feature-comments
 * @param  string $pagename The name pf the page to fetch the comments filename for.
 * @return string           The path to the file that the 
 */
function get_comment_filename($pagename)
{
	global $env;
	$pagename = makepathsafe($pagename);
	return "$env->storage_prefix$pagename.comments.json";
}

/**
 * Generates a new random comment id.
 * @package feature-comments
 * @return string A new random comment id.
 */
function generate_comment_id()
{
	$result = base64_encode(random_bytes(16));
	$result = str_replace(["+", "/", "="], ["-", "_"], $result);
	return $result;
}

/**
 * Finds the comment with specified id by way of an almost-breadth-first search.
 * @package feature-comments
 * @param  array $comment_data	The comment data to search.
 * @param  string $comment_id	The id of the comment to  find.
 * @return object				The comment data with the specified id, or
 *                       		false if it wasn't found.
 */
function find_comment($comment_data, $comment_id)
{
	$subtrees = [];
	foreach($comment_data as $comment)
	{
		if($comment->id === $comment_id)
			return $comment;
		
		if(count($comment->replies) > 0) {
			$subtrees[] = $comment->replies;
		}
	}
	
	foreach($subtrees as $subtree)
	{
		$subtree_result = find_comment($subtree, $comment_id);
		if($subtree_result !== false)
			return $subtree_result;
	}
	
	return false;
}

/**
 * Deletes the first comment found with the specified id.
 * @param	array	$comment_data	An array of threaded comments to delete the comment from.
 * @param	string	$target_id		The id of the comment to delete.
 * @return	bool					Whether the comment was found and deleted or not.
 */
function delete_comment(&$comment_data, $target_id)
{
	$comment_count = count($comment_data);
	if($comment_count === 0) return false;
	
	for($i = 0; $i < $comment_count; $i++) {
		if($comment_data[$i]->id == $target_id) {
			if(count($comment_data[$i]->replies) == 0) {
				unset($comment_data[$i]);
				// Reindex the comment list before returning
				$comment_data = array_values($comment_data);
			}
			else {
				unset($comment_data[$i]->username);
				$comment_data[$i]->message = "_[Deleted]_";
			}
			return true;
		}
		if(count($comment_data[$i]->replies) > 0 &&
			delete_comment($comment_data[$i]->replies, $target_id))
			return true;
	}
	
	
	return false;
}

/**
 * Fetches all the parent comments of the specified comment id, including the
 * comment itself at the end.
 * Useful for figuring out who needs notifying when a new comment is posted.
 * @package feature-comments
 * @param	array		$comment_data	The comment data to search.
 * @param	string		$comment_id		The comment id to fetch the thread for.
 * @return	object[]	A list of the comments in the thread, with the deepest
 * 						one at the end.
 */
function fetch_comment_thread($comment_data, $comment_id)
{
	foreach($comment_data as $comment)
	{
		// If we're the comment they're looking for, then return ourselves as
		// the beginning of a thread
		if($comment->id === $comment_id)
			return [ $comment ];
		
		if(count($comment->replies) > 0) {
			$subtree_result = fetch_comment_thread($comment->replies, $comment_id);
			if($subtree_result !== false) {
				// Prepend ourselves to the result
				array_unshift($subtree_result, $comment);
				return $subtree_result; // Return the comment thread
			}
		}
	}
	
	return false;
}

/**
 * Renders a given comments tree to html.
 * @package feature-comments
 * @param	object[]	$comments_data	The comments tree to render.
 * @param	integer		$depth			For internal use only. Specifies the depth
 * 										at which the comments are being rendered.
 * @return	string		The given comments tree as html.
 */
function render_comments($comments_data, $depth = 0)
{
	global $settings, $env;
	
	if(count($comments_data) == 0) {
		if($depth == 0)
			return "<p><em>No comments here! Start the conversation above.</em></p>";
		else
			return "";
	}
	
	$result = "<div class='comments-list" . ($depth > 0 ? " nested" : "") . "' data-depth='$depth'>";
	
	//$comments_data = array_reverse($comments_data);
	for($i = count($comments_data) - 1; $i >= 0; $i--) {
		$comment = $comments_data[$i];
		
		$result .= "\t<div class='comment' id='comment-$comment->id' data-comment-id='$comment->id'>\n";
		$result .= "\t<p class='comment-header'><span class='name'>" . page_renderer::render_username($comment->username ?? "<em>Unknown</em>") . "</span> said:</p>";
		$result .= "\t<div class='comment-body'>\n";
		$result .= "\t\t" . parse_page_source($comment->message);
		$result .= "\t</div>\n";
		$result .= "\t<div class='reply-box-container'></div>\n";
		$result .= "\t<p class='comment-footer'>";
		$result .= "\t\t<span class='comment-footer-item'><button class='reply-button'>Reply</button></span>\n";
		if($env->user == $comment->username || $env->is_admin)
			$result .= "<span class='comment-footer-item'><a href='?action=comment-delete&page=" . rawurlencode($env->page) . "&delete_id=" . rawurlencode($comment->id) . "' class='delete-button' title='Permanently delete this comment'>Delete</a></span>\n";
		$result .= "\t\t<span class='comment-footer-item'><a class='permalink-button' href='#comment-$comment->id' title='Permalink to this comment'>&#x1f517;</a></span>\n";
		$result .= "\t\t<span class='comment-footer-item'><time datetime='" . date("c", strtotime($comment->timestamp)) . "' title='The time this comment was posted'>$settings->comment_time_icon " . date("l jS \of F Y \a\\t h:ia T", strtotime($comment->timestamp)) . "</time></span>\n";
		$result .= "\t</p>\n";
		$result .= "\t" . render_comments($comment->replies, $depth + 1) . "\n";
		$result .= "\t</div>";
	}
	$result .= "</div>";
	
	return $result;
}

?>
