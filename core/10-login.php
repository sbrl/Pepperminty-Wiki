<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


if(!is_cli()) session_start();
// Make sure that the login cookie lasts beyond the end of the user's session
send_cookie(session_name(), session_id(), time() + $settings->sessionlifetime);
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
if($env->is_logged_in) {
	foreach($settings->admins as $admin_username){
		if($admin_username == $env->user) {
			$env->is_admin = true;
			break;
		}
	}
}
/////// Login System End ///////
