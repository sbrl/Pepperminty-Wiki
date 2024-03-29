<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */


/** The version of Pepperminty Wiki currently running. */
$version = "{version}";
$commit = "{commit}";
/// Environment ///
/** Holds information about the current request environment. */
$env = new stdClass();
/** The action requested by the user. @var string */
$env->action = $settings->defaultaction;
/** The page name requested by the remote client. @var string */
$env->page = "";
/** The page name, but run through htmlentities(), thus making it safe to display in an output document. @var string */
$env->page_safe = "";
/** The filename that the page is stored in. @var string */
$env->page_filename = "";
/** Whether we are looking at a history revision or not. @var boolean */
$env->is_history_revision = false;
/** An object holding history revision information for the current request */
$env->history = new stdClass();
/** The revision number requested of the current page @var int */
$env->history->revision_number = -1;
/** The revision data object from the page index for the requested revision */
$env->history->revision_data = false;
/** The user's name if they are logged in. Defaults to `$settings->anonymous_user_name` if the user isn't currently logged in. @var string */
$env->user = $settings->anonymous_user_name;
/** Whether the user is logged in @var boolean */
$env->is_logged_in = false;
/** Whether the user is an admin (moderator) @todo Refactor this to is_moderator, so that is_admin can be for the server owner. @var boolean */
$env->is_admin = false;
/** Whether the current request was made a secure connection or not. @var boolean */
$env->is_secure = !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off';
/** The currently logged in user's data. Please see $settings->users->username if you need to edit this - this is here for convenience :-) */
$env->user_data = new stdClass();
/** The data storage directory. Page filenames should be prefixed with this if you want their content. */
$env->storage_prefix = $settings->data_storage_dir . DIRECTORY_SEPARATOR;
/** Contains performance data statistics for the current request. */
$env->perfdata = new stdClass();
/// Paths ///
/**
 * Contains a bunch of useful paths to various important files.
 * None of these need to be prefixed with `$env->storage_prefix`.
 */
$paths = new stdClass();
/** The pageindex. Contains extensive information about all pages currently in this wiki. Individual entries for pages may be extended with arbitrary properties. */
$paths->pageindex = "pageindex.json";
/** The inverted index used for searching. Use the `search` class to interact with this - otherwise your brain might explode :P */
$paths->searchindex = "invindex.sqlite";
/** The didyoumean index for typo correction. Used by the search class - which also exposes an interface for interacting with it directly. */
$paths->didyoumeanindex =  "didyoumeanindex.sqlite";
/** The index that maps ids to page names. Use the `ids` class to interact with it :-) */
$paths->idindex = "idindex.json";
/** The cache of the most recently calculated statistics. */
$paths->statsindex = "statsindex.json";
/** The interwiki index cache */
$paths->interwiki_index = "interwiki_index.json";
/** The cache directory, minus the trailing slash. Contains cached rendered versions of pages. If things don't update, try deleting this folder.  */
$paths->cache_directory = "._cache";

// Prepend the storage data directory to all the defined paths.
foreach ($paths as &$path) {
	$path = $env->storage_prefix . $path;
}

/** The master settings file @var string */
$paths->settings_file = $settingsFilename;
/** The directory to which the extra bundled data is extracted to @var string */
$paths->extra_data_directory = "._extra_data";
/** The prefix to add to uploaded files */
$paths->upload_file_prefix = "Files/";

// Create the cache directory if it doesn't exist
if(!is_dir($paths->cache_directory))
	mkdir($paths->cache_directory, 0700);

// Set the user agent string
$php_version = ini_get("expose_php") == "1" ? "PHP/".phpversion() : "PHP";
ini_set("user_agent", "$php_version ($settings->sitename; ".PHP_SAPI."; ".PHP_OS." ".php_uname("m")."; ".(PHP_INT_SIZE*8)." bits; rv:$version) Pepperminty-Wiki/$version-".substr($commit, 0, 7));
unset($php_version);
