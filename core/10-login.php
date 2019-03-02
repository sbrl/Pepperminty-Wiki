<?php

session_start();
// Make sure that the login cookie lasts beyond the end of the user's session
setcookie(session_name(), session_id(), time() + $settings->sessionlifetime, "", "", false, true);
///////// Login System /////////
// Clear expired sessions
if(isset($_SESSION[$settings->sessionprefix . "-expiretime"]) and
   $_SESSION[$settings->sessionprefix . "-expiretime"] < time())
{
	// Clear the session variables
	$_SESSION = [];
	session_destroy();
}

if(isset($_SESSION[$settings->sessionprefix . "-user"]) and
  isset($_SESSION[$settings->sessionprefix . "-pass"]))
{
	// Grab the session variables
	$env->user = $_SESSION[$settings->sessionprefix . "-user"];
	
	// The user is logged in
	$env->is_logged_in = true;
	$env->user_data = $settings->users->{$env->user};
	
}

// Check to see if the currently logged in user is an admin
$env->is_admin = false;
if($env->is_logged_in)
{
	foreach($settings->admins as $admin_username)
	{
		if($admin_username == $env->user)
		{
			$env->is_admin = true;
			break;
		}
	}
}
/////// Login System End ///////
