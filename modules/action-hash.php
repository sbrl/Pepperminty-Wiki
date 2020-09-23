<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Password hashing action",
	"version" => "0.7",
	"author" => "Starbeamrainbowlabs",
	"description" => "Adds a utility action (that anyone can use) called hash that hashes a given string. Useful when changing a user's password.",
	"id" => "action-hash",
	"code" => function() {
		/**
		 * @api {get} ?action=hash&string={text} Hash a password
		 * @apiName Hash
		 * @apiGroup Utility
		 * @apiPermission Anonymous
		 * 
		 * @apiParam {string}	string	The string to hash.
		 * @apiParam {bool}	raw		Whether to return the hashed password as a raw string instead of as part of an HTML page.
		 *
		 * @apiError	ParamNotFound	The string parameter was not specified.
		 */
		
		/*
		 * ██   ██  █████  ███████ ██   ██ 
		 * ██   ██ ██   ██ ██      ██   ██ 
		 * ███████ ███████ ███████ ███████ 
		 * ██   ██ ██   ██      ██ ██   ██ 
		 * ██   ██ ██   ██ ███████ ██   ██
		 */
		add_action("hash", function() {
			global $settings;
			
			if(!isset($_GET["string"])) {
				http_response_code(422);
				exit(page_renderer::render_main("Missing parameter", "<p>The <code>GET</code> parameter <code>string</code> must be specified.</p>
		<p>It is strongly recommended that you utilise this page via a private or incognito window in order to prevent your password from appearing in your browser history.</p>"));
			}
			else if(!empty($_GET["raw"])) {
				header("content-type: text/plain");
				exit(hash_password($_GET["string"]));
			}
			else {
				exit(page_renderer::render_main("Hashed string", "<p>Algorithm: <code>$settings->password_algorithm</code></p>\n<p><code>" . $_GET["string"] . "</code> → <code>" . hash_password($_GET["string"]) . "</code></p>"));
			}
		});
	}
]);

?>
