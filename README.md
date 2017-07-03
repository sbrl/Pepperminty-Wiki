# Pepperminty Wiki [![Build Status](https://travis-ci.org/sbrl/Pepperminty-Wiki.svg?branch=master)](https://travis-ci.org/sbrl/Pepperminty-Wiki) [![Join the chat at https://gitter.im/Pepperminty-Wiki/Lobby](https://badges.gitter.im/Pepperminty-Wiki/Lobby.svg)](https://gitter.im/Pepperminty-Wiki/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![License: MPL-2.0](https://img.shields.io/badge/License-MPL--2.0-blue.svg)](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/LICENSE)

A Wiki in a box

Pepperminty Wiki is a complete wiki contained in a single file, inspired by @am2064's [Minty Wiki](https://github.com/am2064/Minty-Wiki). It's open source too (under MPL-2.0), so contributions are welcome!

Developed by Starbeamrainbowlabs (though contributions from others are welcome!), Pepperminty Wiki has a variety of useful (and cool!) features - such as file upload, a dynamic help page, page revision history, page tags, and more! Other amazing features are in the works too (like threaded page commenting, autoupdate, and user settings), so check the release notes to see what's been added recently.

**Latest Version:** v0.13 (stable) v0.14-dev (development) ([Changelog](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Changelog.md))

## Screenshots
![Main Page Example](https://i.imgur.com/8TokPXw.png)

Above: The Main Page used for testing purposes.

## Features
- Configurable settings
- User login system
- Page creation (Sub pages supported)
- Markdown-powered syntax
	- Templating support
	- Additional syntax for resizing and floating images (see inbuilt help page)
	- File galleries
	- Short syntax for referencing uploaded files
	- Client side mathematical expression parsing, courtesy of [MathJax](https://www.mathjax.org/)
	- Links to non-existent pages appear red
- Full page revision history (comparison / manipulation coming soon)
- Optional time-delayed google search indexing
- Simple edit conflict detection
- Edit previewing (since v0.14, thanks to @ikisler)
- Internal links - Links to non-existent pages show up in red
- Printable page view
- Customisable theme
- ~~Basic 'search' bar~~ A full text search engine!
	- _Dynamic server-side suggestions (coming in v0.13!)_
- (Optional) Sidebar with a tree of all the current pages
- Tags
- List of all pages and details
	- List of all tags and pages with any given tag
	- List of recent changes
- Inbuilt help page (modules can add their own sections to it)
- File upload and preview
	- Simple syntax for including media in a page (explanation on help page)
- Page protection
- Customisable module based system
	- Allows you to add or remove features at will

## Demo
A Live demo of the latest stable version can be found over at [my website](//starbeamrainbowlabs.com/labs/peppermint)

## Getting Started
### Requirements
- PHP-enabled webserver (must be PHP 7+)
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
- The following PHP extensions: mbstring, imagick (for preview generation), fileinfo (for proper mime type checking of uploaded files), and zip (for compress exports)
- Write access to own folder (only for editing)

### Getting your own copy
Setting up your own copy of Pepperminty Wiki is easy. Since Pepperminty Wiki works on a module based system, all you need to do is choose the modules you want installed, and then configure your new installation so that it fits your needs. There are several ways to do this:

#### Method 1: Using the latest pre-built stable release
If you want a pre-built stable version, then you can [use the latest release](https://github.com/sbrl/Pepperminty-Wiki/releases/latest). It has a changelog that tells you what has changed since the last release, along with a pre-built version with all the latest modules.

#### Method 2: Grabbing the pre-built verion from the repository
If you're feeling lazy, you  can grab the bleeding-edge version from this respository, which comes with all the latest modules. You can get it [here](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/build/index.php).

#### Method 3: Using the online downloader
Pepperminty Wiki has a downloader that you can use to select the modules you want to include in your install. The online downloader will give you the latest stable release. You can find it [here](//starbeamrainbowlabs.com/labs/peppermint/download.php).

#### Method 3.5: Using the downloader offline
You can also you the downloader offline. Simply clone this repository to your web server and then point your web browser at `your.server/path/to/perppminty/wiki/download.php`.

#### Method 4: Building your own from source
Pepperminty Wiki can also be built from source (and I do this all the time when testing). Start by cloning the repository. Then go into the `modules` folder and append `.disabled` to the names of any modules you don't want to be included (e.g. `modules/page-edit.php` would become `modules/page-edit.php.disabled`). Then follow the instructions for your platform:

##### Windows
Simply run the `build.bat` script in the root of the repository. It will handle everything for you.

##### Linux and Everyone Else
Run the following commands from the root of the repository in order, adjusting them for your specific platform (these are for a standard Ubuntu Server install):

```bash
rm build/index.php
php rebuild_module_index.php
php build.php
```

These commands are also in `build.sh`. You can run that if you want. Here's an explanation of what each command does:

1. Deletes the old `index.php` in the build folder that comes with the repository
2. Rebuilds the module index that the build scripts uses to determine what modules it should include when building
3. Actually builds Pepperminty Wiki. Outputs to `index.php`.

## Configuring
To configure your new install, make sure that you've loaded the wiki in your browser at least once. Then open `peppermint.json` in your favourite text editor. An explanation of each of the settings can be found below. If you need any help, just contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new).

Please note that configuration of Pepperminty Wiki will be done through a GUI soon.

Below are all the configuration directives that Pepperminty Wiki (and all the modules included in the repository) understand (though some extra descriptions can be found in [`peppermint.guiconfig.json`](https://github.com/sbrl/Pepperminty-Wiki/blob/master/peppermint.guiconfig.json))

Key				| Value			| Explanation
----------------|---------------|----------------------------
`sitename`		| string		| The site's name. Used all over the place. Note that by default the session cookie is prefixed with a variant of the sitename, so changing this will log everyone out!
`logo_url`		| url			| A url that points to the site's logo. Leave blank to disable. When enabled, the logo will be inserted next to the site name on every page.
`logo_postition`| left|right	| The side of the site name at which the logo should be placed. Only has an effect if the above is not set to an empty string.
`updateurl`		| url			| The url from which to fetch updates. Defaults to the master (development) branch. If there is sufficient demand, a separate stable branch will be created. Note that if you use the automatic updater currently it won't save your module choices. **MAKE SURE THAT THIS POINTS TO A *HTTPS* URL, OTHERWISE SOMEONE COULD INJECT A VIRUS INTO YOUR WIKI!**
`sitesecret`	| string		| The secret key used to perform 'dangerous' actions, like updating the wiki. It is strongly advised that you change this!
`delayed_indexing_time`	| integer | The amount of time, in seconds, that pages should be blocked from being indexed by search engines after their last edit. Set to 0 to disable. This is called delayed indexing. More information can be found [here](http://c2.com/cgi/wiki?DelayedIndexing).
`editing`		| boolean		| Determines whether editing is enabled. Set to false to disable disting for all users (anonymous or otherwise).
`maxpagesize`	| integer		| The maximum number of characters allowed on a single page. The default is 135,000 characters, which is about 50 pages.
`clean_raw_html`| boolean		| Whether page sources should be cleaned of HTML before rendering. If set to true any raw HTML will be escaped before rendering. Note that this shouldn't affect code blocks - they should alwys be escaped. It is STRONGLY recommended that you keep this option turned on, **_ESPECIALLY_** if you allow anonymous edits as no sanitizing whatsoever is performed on the HTML. If you need a feature that the markdown parser doesn't have, please open an issue. Also note that some parsers may override this setting and escape HTML sequences anyway.
`enable_math_rendering`	| boolean	| Whether to enable client side mathematical expression parsing. When enabled LaTeX mathematical expressions can be enclosed in dollar signs like so: `$x^2$`. Turn off if you don't use it.
`anonedits`		| boolean		| Determines whether users who aren't logged in are allowed to edit your wiki. Set to true to allow anonymous users to edit the wiki.
`defaultpage`	| string		| The name of the page that will act as the home page for the wiki. This page will be served if the user didn't specify a page.
`defaultaction`	| action name	| The default action. This action will be performed if no other action is specified. It is recommended you set this to "view" - that way the user automatically views the default page (see above).
`parser`		| parser name	| The parser to use when rendering pages. Defaults to 'default', which is a modified version of slimdown, originally written by [Johnny Broadway](mailto:johnny@johnnybroadway.com).
`show_subpages`	| boolean		| Whether to show a list of subpages at the bottom of the page.
`subpages_display_depth` | number | The depth to which we should display when listing subpages at the bottom of the page.
`max_recent_changes` | number	| The maximum number of recent changes to display on the recent changes page.
`optimize_pages`	|	boolean	| Whether to optimise all webpages generated. Currently this option only minifies inline css.
`users`			| array of users | An array of usernames and passwords - passwords should be hashed with sha256. Put one user / password on each line, remembering the comma at the end. The last user in the list doesn't need a comma after their details though. Tip: use the `hash` action to hash passwords appropriately for Pepperminty Wiki, but remember to use an incognito window!
`require_login_view` | boolean	| Whether to require that users login before they do anything else. If you combine this setting with the `data_storage_dir` option to move the storage directory outside your web root, this will completely hide your wiki from anyone who isn't logged in.
`data_storage_dir`	| path		| The directory in which to store all files, except this main index.php. A single dot ('.') denotes the current directory. Remember to omit the trailing slash from the directory name, as it is added automatically by Pepperminty Wiki. Combine with `require_login_view` in order to completely hide your wiki from anonymous users.
`use_sha3`			| boolean	| Whether to use the new sha3 hashing algorithm that was standardised on the 8th August 2015. Only works if you have strawbrary's sha3 extension installed. Get it here: https://github.com/strawbrary/php-sha3 Note: If you change this settings, make sure to update the password hashes above! Note that the `hash` action is aware of this option and will hash passwords appropriately based on this setting.
`admins`			| array of usernames | An array of usernames that are administrators. Administrators can delete and move pages.
`admindisplaychar`	| string	| The string that is prepended before an admin's name on the nav bar. Defaults to a diamond shape (&#9670;).
`protectedpagechar`	| string	| The string that is prepended a page's name in the page title if it is protected. Defaults to a lock symbol. (&#128274;)
`admindetails_name`	| string	| The name of the site administrator. Since users can only be added by editing the settings file, people will need a contact name and address to use to ask for an account. Displayed at the bottom of the page.
`admindetails_email`| string	| The email address of the site administrator. Used in conjunction with the above. Will be appropriately obfusticated to deter spammers when displayed.
`export_allow_only_admins` | boolean | Whether to only allow adminstrators to export the your wiki as a zip using the page-export module.
`nav_links`			| array		| An array of links and display text to display at the top of the site. See the comment in the settings file for more details.
`nav_links_extra`	| array		| An array of additional links in the above format that will be shown under "More" subsection, in the same format as the above.
`nav_links_bottom`	| array		| An array of links in the above format that will be shown at the bottom of the page, the same format as the above 2.
`footer_message`	| string( + HTML) | A message that will appear at the bottom of every page. May contain HTML.
`editing_message`	| string( + HTML) | A message that will appear just before the submit button on the editing page. May contain HTML.
`upload_enabled`	| boolean	| Whether to allow image uploads to the server.
`upload_allowed_file_types` | array of strings | An array of mime types that are allowed to be uploaded. Note that Pepperminty Wiki only does minimal checking of the actual content that is being uploaded - so please don't add any dangerous file types here on a parmanant bases for your own safety!
`preview_file_type`	| mime type	| The default file type for previews. Defaults to image/png. Also supports `image/jpeg` and `image/webp`. `image/webp` is a new image format that reduces image sizez by ~20%, but PHP still has some issues with invalid webp images.
`default_preview_size` | number	| The default size of preview images.
`mime_extension_mappings_location` | path | The location of a file that maps mime types onto file extensions and vice versa. Used to generate the file extension for an uploaded file. Set to the default location of the mime.types file on Linux. If you aren't using linux, download [this pastebin](http://pastebin.com/mjM3zKjz) and point this setting at it instead.
`mime_mappings_overrides` | array of strings | An array of override mime mappings to translate mime types into the appropriate file extension. Use if the file pointed to by the above assigns weird file extensions to any file types.
`min_preview_size`	| number	| The minimum allowed size for generated preview images in pixels.
`max_preview_size`	| number	| The maximum allowed size for generated preview images in pixels.
`search_characters_context` | number | The number of characters that should be displayed either side of a matching term in the context below each search result.
`search_title_matches_weighting` | number | The weighting to give to search term matches found in a page's title.
`search_tags_matches_weighting` | number | The weighting to give to search term matches found in a page's tags.
`css`				| string of css | A string of css to include. Will be included in every page. This may also be an absolute url that references an external stylesheet.
`favicon`			| url		| A url that points to the favicon for your wiki.
`session_prefix`	| string	| The prefix that should be used in the names of the session variables. Defaults to an all lower case version of the site name with all non alphanumeric characters removed (The special value `auto` does this). Remember that changing this will log everyone out since the session variable's name will have changed. Normally you won't have to change this - This setting is left over from when we used a cookie to store login details. By default this is set to a safe variant of your site name.

## Module API Reference
I have documented (most of) the current API that you can use to create your own modules. You can find it in the [Module_API_Docs.md](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Module_API_Docs.md) file in this repository.

I've also documented Pepperminty Wiki's entire REST API using [apiDoc](http://apidocjs.com/). You can view the docs [here](https://sbrl.github.io/Pepperminty-Wiki/RestApiDocs/).

If you do create a module, I'd love to hear about it. Even better, [send a pull request](https://github.com/sbrl/Pepperminty-Wiki/pulls/new)!

## Real World Usage
None yet! Contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) and tell me about where you are using Pepperminty Wiki and I'll add you to this section!

## Todo
Here's a list of things that I want to add at some point (please feel free to [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) and help out!).

- Better page history (revert to revision, compare revisions, etc.)
- Intelligent auto-updating system that doesn't wipe your settings / module choices
- A module registry of some sort (ideas welcome!)
- Image maps (#103)
- User management for moderators ( + moderator management for the wiki owner) (#127)
- An app for Android (Sorry, iOS is not practial at the current time. Feel free to make one yourself! I'm happy to help out if you need help with Pepperminty Wiki itself - message on gitter (see above), or open an issue on this repository.)
- (See more on the [issue tracker](https://github.com/sbrl/Pepperminty-Wiki/issues)!)
- ...?

Is the feature you want to see not on this list or not implemented yet? [Open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) or [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls)!

# License
Pepperminty Wiki is released under the Mozilla Public License 2.0. The full license text is included in the `LICENSE` file in this repository.
