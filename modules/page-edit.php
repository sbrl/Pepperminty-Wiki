<?php
register_module([
	"name" => "Page editor",
	"version" => "0.9.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Allows you to edit pages by adding the edit and save actions. You should probably include this one.",
	"id" => "page-edit",
	
	"code" => function() {
		
		/*
		 *           _ _ _
		 *   ___  __| (_) |_
		 *  / _ \/ _` | | __|
		 * |  __/ (_| | | |_
		 *  \___|\__,_|_|\__|
		 *             %edit%
		 */
		add_action("edit", function() {
			global $pageindex, $settings, $env;
			
			$filename = "$env->page.md";
			$page = $env->page;
			$creatingpage = !isset($pageindex->$page);
			if((isset($_GET["newpage"]) and $_GET["newpage"] == "true") or $creatingpage)
			{
				$title = "Creating $env->page";
			}
			else
			{
				$title = "Editing $env->page";
			}
			
			$pagetext = "";
			if(isset($pageindex->$page))
			{
				$pagetext = file_get_contents($filename);
			}
			
			if((!$isloggedin and !$settings->anonedits) or !$settings->editing)
			{
				if(!$creatingpage)
				{
					// The page already exists - let the user view the page source
					exit(page_renderer::render_main("Viewing source for $env->page", "<p>$settings->sitename does not allow anonymous users to make edits. You can view the source of $env->page below, but you can't edit it.</p><textarea name='content' readonly>$pagetext</textarea>"));
				}
				else
				{
					http_response_code(404);
					exit(page_renderer::render_main("404 - $env->page", "<p>The page <code>$env->page</code> does not exist, but you do not have permission to create it.</p><p>If you haven't already, perhaps you should try <a href='index.php?action=login'>logging in</a>.</p>"));
				}
			}
			
			$content = "<h1>$title</h1>";
			if(!$env->is_logged_in and $settings->anonedits)
			{
				$content .= "<p><strong>Warning: You are not logged in! Your IP address <em>may</em> be recorded.</strong></p>";
			}
			$content .= "<form method='post' action='index.php?action=save&page=" . rawurlencode($page) . "&action=save'>
			<textarea name='content'>$pagetext</textarea>
			<input type='submit' value='Save Page' />
		</form>";
			exit(page_renderer::render_main("$title - $settings->sitename", $content));
		});
		
		/*
		 *
		 *  ___  __ ___   _____
		 * / __|/ _` \ \ / / _ \
		 * \__ \ (_| |\ V /  __/
		 * |___/\__,_| \_/ \___|
		 *                %save%
		 */
		add_action("save", function() {
			global $pageindex, $settings, $env; 
			if(!$settings->editing)
			{
				header("location: index.php?page=$env->page");
				exit(page_renderer::render_main("Error saving edit", "<p>Editing is currently disabled on this wiki.</p>"));
			}
			if(!$env->is_logged_in and !$settings->anonedits)
			{
				http_response_code(403);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("You are not logged in, so you are not allowed to save pages on $settings->sitename. Redirecting in 5 seconds....");
			}
			if(!isset($_POST["content"]))
			{
				http_response_code(400);
				header("refresh: 5; url=index.php?page=$env->page");
				exit("Bad request: No content specified.");
			}
			
			// Make sure that the directory in which the page needs to be saved exists
			if(!is_dir(dirname("$env->page.md")))
			{
				// Recursively create the directory if needed
				mkdir(dirname("$env->page.md"), null, true);
			}
			
			
			if(file_put_contents("$env->page.md", htmlentities($_POST["content"]), ENT_QUOTES) !== false)
			{
				$page = $env->page;
				// Make sure that this page's parents exist
				check_subpage_parents($page);
				
				//update the page index
				if(!isset($pageindex->$page))
				{
					$pageindex->$page = new stdClass();
					$pageindex->$page->filename = "$env->page.md";
				}
				$pageindex->$page->size = strlen($_POST["content"]);
				$pageindex->$page->lastmodified = time();
				if($isloggedin)
					$pageindex->$page->lasteditor = utf8_encode($env->user);
				else
					$pageindex->$page->lasteditor = utf8_encode("anonymous");
				
				file_put_contents("./pageindex.json", json_encode($pageindex, JSON_PRETTY_PRINT));
				
				if(isset($_GET["newpage"]))
					http_response_code(201);
				else
					http_response_code(200);
				
				header("location: index.php?page=$env->page&edit_status=success");
				exit();
			}
			else
			{
				http_response_code(507);
				exit(page_renderer::render_main("Error saving page - $settings->sitename", "<p>$settings->sitename failed to write your changes to the disk. Your changes have not been saved, but you might be able to recover your edit by pressing the back button in your browser.</p>
				<p>Please tell the administrator of this wiki (" . $settings->admindetails["name"] . ") about this problem.</p>"));
			}
		});
	}
]);

?>
