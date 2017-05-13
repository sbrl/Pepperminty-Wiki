<?php
register_module([
	"name" => "Page Comments",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds threaded comments to the bottom of every page.",
	"id" => "feature-comments",
	"code" => function() {
		global $env;
		
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
					exit(page_renderer::render_main("Error posting comment - $settings->sitename", "<p>$settings->sitename couldn't post your comment because it couldn't find the parent comment you replied to. It's possible that $settings->adamindetails_name, $settings->sitename's administrator, deleted the comment. Here's the comment you tried to post:</p>
					<textarea readonly>$message</textarea>"));
				}
				
				$parent_comment->replies[] = $new_comment;
				
				$comment_thread = fetch_comment_thread($comment_data, $new_comment->id);
				
				$email_subject = "[Notification] $env->user replied to your comment on $env->page - $settings->sitename";
				
				foreach($comment_thread as $thread_comment) {
					// Don't notify the comment poster of their own comment :P
					if($thread_comment->id = $new_comment->id)
						continue;
						
					$email_body = "Hello, {username}!\n" +
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
			
			http_response_code(307);
			header("location: ?action=view&page=" . rawurlencode($env->page) . "&commentsuccess=yes#comment-$new_comment->id");
			exit(page_renderer::render_main("Comment posted successfully - $settings->sitename", "<p>Your comment on $env->page was posted successfully. If your browser doesn't redirect you automagically, please <a href='?action=view&page=" . rawurlencode($env->page) . "commentsuccess=yes#comment-$new_comment->id'>click here</a> to go to the comment you posted on the page you were viewing.</p>"));
		});
		
		if($env->action == "view") {
			page_renderer::register_part_preprocessor(function(&$parts) {
				global $env;
				$comments_filename = get_comment_filename($env->page);
				$comments_data = file_exists($comments_filename) ? json_decode(file_get_contents($comment_filename)) : [];
				
				
				$comments_html = "<aside class='comments'>" . 
					"<h2>Comments</h2>\n" . 
				
				if($env->is_logged_in) {
					$comments_html .= "<form class='comment-reply-form' method='post' action='?action=comment&page=" . rawurlencode($env->page) . "'>\n" . 
						"<h3>Post a Comment</h3>\n" . 
						"\t<textarea name='message' placeholder='Type your comment here. You can use the same syntax you use when writing pages.'></textarea>\n" . 
						"\t<input type='hidden' name='reply-to' />\n" . 
						"\t<input type='submit' value='Post Comment' />\n" . 
						"</form>\n";
					
					page_renderer::AddJSSnippet(<<<'REPLYJS'
///////////////////////////////////
///////// Commenting Form /////////
///////////////////////////////////
document.addEventListener("load", function(event) {
	var replyButtons = document.querySelectorAll(".reply-button");
	for(let i = 0; i < replyButtons.length; i++) {
		replyButtons
	}
});

function display_reply_form(event)
{
	// Deep-clone the comment form
	var replyForm = document.querySelector(".comment-reply-form").cloneNode(true);
	// Set the comment we're replying to
	replyForm.querySelector("[name=reply-to]").value = event.target.parentNode.dataset.commentId;
	// Display the newly-cloned commenting form
	event.target.parentElement.querySelector(".reply-box-container").appendChild(replyForm);
}
REPLYJS;
)
				}
				else {
					$comments_html .= "<form class='comment-reply-form disabled no-login'>\n" . 
					"\t<textarea disabled name='message' placeholder='Type your comment here. You can use the same syntax you use when writing pages.'></textarea>\n" . 
					"\t<p><a href='?action=login&returnto=" . rawurlencode("?action=view&page=" . rawurlencode($env->page)) . "'>Login</a> to post a comment.</p>\n" . 
					"\t<input disabled type='submit' value='Post Comment' />\n" . 
					"</form>\n";
				}
				$comments_html .= render_comments($comments_data);
				
				$comments_html .= "</aside>\n";
				
				$parts["{extra}"] = $comments_html . $parts["{extra}"];
			});
		}
	}
]);

/**
 * Given a page name, returns the absolute file path in which that page's
 * comments are stored.
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
 * @return string A new random comment id.
 */
function generate_comment_id()
{
	$result = base64_encode(random_bytes(16));
	$result = str_replace(["+", "/"], ["-", "_"], $result);
	return $result;
}

/**
 * Finds the comment with specified id by way of an almost-breadth-first search.
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
			$subtrees = $comment->replies;
		}
	}
	
	foreach($subtrees as $subtree)
	{
		$subtree_result = find_comment($subtree);
		if($subtree_result !== false)
			return $subtree;
	}
	
	return false;
}

/**
 * Fetches all the parent comments of the specified comment id, including the
 * comment itself at the end.
 * Useful for figuring out who needs notifying when a new comment is posted.
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
 * @param	object[]	$comments_data	The comments tree to render.
 * @param	integer		$depth			For internal use only. Specifies the depth
 * 										at which the comments are being rendered.
 * @return	string		The given comments tree as html.
 */
function render_comments($comments_data, $depth = 0)
{
	if(count($comments_data) == 0) {
		if($depth == 0)
			return "<p><em>No comments here! Start the conversation above.</em></p>";
		else
			return "";
	}
	
	$result = "<div class='comments-list" . ($depth > 0 ? " nested" : "") . "' data-depth='$depth'>";
	
	foreach($comments_data as $comment) {
		$result .= "\t<div class='comment' id='comment-$comment->id' data-comment-id='$comment->id'>\n";
		$result .= "\t<p class='comment-header'>$comment->username said:</p>";
		$result .= "\t<div class='comment-body'>\n";
		$result .= "\t\t" . parse_page_source($comment->message);
		$result .= "\t</div>\n";
		$result .= "\t<div class='reply-box-container'></div>\n";
		$result .= "\t<p class='comment-footer'>";
		$result .= "\t\t<button class='reply-button'>Reply</button>\n";
		$result .= "\t\t<a class='permalink-button' href='#comment-$comment->id'>&#x1f517;</a>\n";
		$result .= "\t\t&#x1f557; <time datetime='" . date("c", $comment->timestamp) . "'>" . date("l jS \of F Y \a\\t h:ia T", $comment->timestamp) . "</time>\n";
		$result .= "\t</p>\n";
		$result .= "\t" . render_comments($comment->replies) . "\n";
		$ersult .= "\t</div>";
	}
	$result .= "</div>";
	
	return $result;
}

?>
