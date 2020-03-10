<?php


//////////////////////////////////////
///// Extra consistency measures /////
//////////////////////////////////////

// CHANGED: The search redirector has now been moved to below the module registration system, as it was causing a warning here

// Redirect the user to the login page if:
//  - A login is required to view this wiki
//  - The user isn't already requesting the login page
// Note we use $_GET here because $env->action isn't populated at this point
if(
    !is_cli() &&
    $settings->require_login_view === true && // If this site requires a login in order to view pages
   !$env->is_logged_in && // And the user isn't logged in
   !in_array($_GET["action"], [ "login", "checklogin", "opensearch-description", "invindex-rebuild", "stats-update" ])) // And the user isn't trying to login, or get the opensearch description, or access actions that apply their own access rules
{
	// Redirect the user to the login page
	http_response_code(307);
	header("x-login-required: yes");
	$url = "?action=login&returnto=" . rawurlencode($_SERVER["REQUEST_URI"]) . "&required=true";
	header("location: $url");
	exit(page_renderer::render("Login required - $settings->sitename", "<p>$settings->sitename requires that you login before you are able to access it.</p>
		<p><a href='$url'>Login</a>.</p>"));
}
//////////////////////////////////////
//////////////////////////////////////
