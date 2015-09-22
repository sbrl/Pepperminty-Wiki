<?php
register_module([
	"name" => "Login",
	"version" => "0.6",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a pair of actions (login and checklogin) that allow users to login. You need this one if you want your users to be able to login.",
	"id" => "page-login",
	"code" => function() {
		/*
		 *  _             _
		 * | | ___   __ _(_)_ __
		 * | |/ _ \ / _` | | '_ \
		 * | | (_) | (_| | | | | |
		 * |_|\___/ \__, |_|_| |_|
		 *          |___/  %login%
		 */
		add_action("login", function() {
			global $settings;
			$title = "Login to $settings->sitename";
			$content = "<h1>Login to $settings->sitename</h1>\n";
			if(isset($_GET["failed"]))
				$content .= "\t\t<p><em>Login failed.</em></p>\n";
			$content .= "\t\t<form method='post' action='index.php?action=checklogin&returnto=" . rawurlencode($_SERVER['REQUEST_URI']) . "'>
				<label for='user'>Username:</label>
				<input type='text' name='user' id='user' />
				<br />
				<label for='pass'>Password:</label>
				<input type='password' name='pass' id='pass' />
				<br />
				<input type='submit' value='Login' />
			</form>";
			exit(page_renderer::render_main($title, $content));
		});
		
		/*
		 *       _               _    _             _
		 *   ___| |__   ___  ___| | _| | ___   __ _(_)_ __
		 *  / __| '_ \ / _ \/ __| |/ / |/ _ \ / _` | | '_ \
		 * | (__| | | |  __/ (__|   <| | (_) | (_| | | | | |
		 *  \___|_| |_|\___|\___|_|\_\_|\___/ \__, |_|_| |_|
		 *     %checklogin%                   |___/
		 */
		add_action("checklogin", function() {
			global $settings, $env;
			
			//actually do the login
			if(isset($_POST["user"]) and isset($_POST["pass"]))
			{
				//the user wants to log in
				$user = $_POST["user"];
				$pass = $_POST["pass"];
				if($settings->users[$user] == hash("sha256", $pass))
				{
					$env->is_logged_in = true;
					$expiretime = time() + 60*60*24*30; //30 days from now
					$_SESSION["$settings->sessionprefix-user"] = $user;
					$_SESSION["$settings->sessionprefix-pass"] = hash("sha256", $pass);
					$_SESSION["$settings->sessionprefix-expiretime"] = $expiretime;
					//redirect to wherever the user was going
					http_response_code(302);
					if(isset($_POST["goto"]))
						header("location: " . $_POST["returnto"]);
					else
						header("location: index.php");
					exit();
				}
				else
				{
					http_response_code(302);
					header("location: index.php?action=login&failed=yes");
					exit();
				}
			}
			else
			{
				http_response_code(302);
				header("location: index.php?action=login&failed=yes&badrequest=yes");
				exit();
			}
		});
	}
]);
?>
