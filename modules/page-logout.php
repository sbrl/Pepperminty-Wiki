<?php
register_module([
	"name" => "Logout",
	"version" => "0.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds an action to let users user out. For security reasons it is wise to add this module since logging in automatically opens a session that is valid for 30 days.",
	"id" => "page-logout",
	"code" => function() {
		add_action("logout", function() {
			global $user, $pass, $isloggedin;
			$isloggedin = false;
			unset($user);
			unset($pass);
			//clear the session variables
			$_SESSION = [];
			session_destroy();
			
			exit(page_renderer::render_main("Logout Successful", "<h1>Logout Successful</h1>
		<p>Logout Successful. You can login again <a href='index.php?action=login'>here</a>.</p>"));
		});
	}
]);

?>
