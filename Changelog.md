# Changelog
This file holds the changelog for Pepperminty Wiki. This is the master list of things that have changed (second only to the commit history!) - though the information for any particular release can also be found in the description of it's page for every release made on Github too.

## v0.15-beta2

### Added
- Added "restore locally saved content" button to page editor
- [module api] Documented the remainder of the members of the `search` class

### Fixed
 - Moving a page will now move all the comments associated with it
 - The page names on the page move success page are now encoded correctly to avoid html and url issues
 - Clicking _Login to post a comment_ will now take you back to the comments section of the page you were on upon completing the login process instead of the top of the page


## v0.15-beta1

### Added
 - Statistics! (#97)
     - Added a new statistics engine, which you can add your own statistic calculators to with `statistic_add`
     - Added action `stats` to view the calculated statisics
     - Statistics are automagically recalculated every day - this can be controlled with the `stats_update_interval` and `stats_update_processingtime` settings
 - Added new "« Parent Page" to subpages so that you can easily visit their parent pages
 - Users can now delete their own comments, and users logged in as a moderator or better can delete anyone's comments.
     - Added new `comment-delete` action
     - Comments are deleted entirely if they have no replies - otherwise the username & message are wiped
 - The `history` action now supports `format=json` and `format=csv`
 - Added tags next to the names of pages in the search results
 - Added new `random_page_exclude` setting that allows you to exclude pages from the random action with a (PHP) regular expression
 - [module api] Added new `get_page_parent($pagename)` method.
 - [module api] Added new remote file system to download additional required files. Use it with `register_remote_file`

### Fixed
 - Fixed invalid opensearch description.
 - When deleting a page, if `feature-comments` is installed, all the comments attached to the page are deleted too
 - Fixed paths when generating previews in certain environments
 - Fixed handling of pages and tags with single quotes in the name
 - Fixed weirdness on some pages rendered by the Pepperminty Wiki core
 - Fixed a few minor usability issues on the upload file page.
 - Removed some extra space at the bottom of some pages.
 - The `raw` action now sends a 404 if the request page doesn't exist on the wiki.

### Changed
 - Make `invindex-rebuild` admin-only, but allow access if the POST param `secret` is set to your secret in `peppermint.json`
 - Improved the wording of the deletion confirmation and error pages
 - Search now searches matching tags as well as pages
 - Updated the search ranking algorithm to put more weight against longer words than shorter ones.


## v0.14

### Changed
 - Improve the look of the downloader a bit. More work is needed here, apparently - I haven't touched it in _ages_!

## v0.14-beta1

### Added
 - Commenting! You'll need to update any custom themes you've created if you're including the commenting module in your build.
 - Added stacked bar to help you visualise what's taking up all the space in your wiki
 - Added link to edit master settings in the credits
 - Initial open search support!
     - After visiting your wiki once, you'll be able to press `<tab>` when typing the path to your wiki to perform a search.
     - It'll only work if your wiki is at the top-level of a domain without _anything_ after the `/` (subdomains are ok). This is a restriction of the protocol, not Pepperminty Wiki!
     - Supports sending search suggestions based on page names in the page index (based on case-insensitive leveshtein distance)
 - Avatars!
     - Gravatar is used if a user hasn't uploaded an avatar yet
     - An identicon is rendered if a user hasn't specified an email address or uploaded a gravatar yet either
     - Added `avatars_show` and `avatars_size` settings to control the displaying & size of rendered avatars.
     - Added the `avatar` action, which 307 redirects to the appropriate avatar image
 - Added `has_action` to make detecting enabled features easier (even if they move modules)
 - Edit previewing, thanks to @ikisler

### Changed
 - Updated MathJax CDN link, as cdn.mathjax.org is being retired ([source](https://www.mathjax.org/cdn-shutting-down/)).

### Fixed
 - Fixed error image generation in the image previewer under certain conditions.
 - Fixed warnings from being spewed all over the place under certain circumstances on the recent changes page
 - Fixed url encoding issue in redirects with ampersands in page names (#139)
 - Allow sending of OpenSearch description even when not logged in on wikis that require a login to work around some browser cookie issues
 - PR #135: Fix repeated page names on sidebar by @ikisler
 - PR #136: Fix issue where bottom nav is cut off by @ikisler

## v0.13
(No changes were made between the last beta release and this release)

## v0.13-beta2

### Added
 - Added new `query-searchindex` action to inspect the internals of the search engine
     - It returns the (ordered) search rankings as json, along with some additional debugging data

### Fixed
 - Clear the page id index out when rebuilding the search index from scratch.
     - This is needed to correct some issues where the id index goes all weird and assigns the same id to multiple pages
 - Don't search page titles or tags for stop words - it skews results since we don't search the page body for them


## v0.13-beta1

### Added
 - Added header to upload file page.
 - Added history support to the `raw` action.
 - Added 'smart save' to the page creator / editor. Nobody need ever loose edits ever again!
 - Added dynamic server-calculated page suggestions. Very helpful for larger wikis. Currently works best in firefox. Part of the `feature-search` module.
 - Added Alt + Enter support to the page search box. Works just like your browser's address bar - opens results in a new tab instead of the current one.
 - Initial UI for configuring settings! Some things still have to be done by editing the file, but it's certainly a start :D
 - User preferences!
     - Accessible via the (by default) small cog next to your username when you're logged in
     - The cog is customisable via the new `user_preferences_button_text` setting.
     - You can change your password
     - There's link to the master site settings from user preferences for admins.
 - [Module API] Added `$env->user_data` and `save_userdata()` to interact with the logged in users' data
 - User pages! Every page under `Users/` by default belongs to their respective users. e.g. `Users/admin` and all the pages under it belong to the `admin` user, so no other user can edit them.
     - You can access your own user page by clicking on your username in the top corner when you're logged in.
     - Added the `user_page_prefix` setting to allow customisation of where user pages should be located. Default value: `Users`
     - [Module API] Added `get_user_pagename()` and `extract_user_from_userpage()` to allow programmatic detection etc.
 - Added a `user-list` action that, well, outputs a list of users. Includes links to their respective user pages (though redlinks aren't implemented here yet).
 - Internal links like `[[Page name]]s` will now link to the correct page in include the rest of the word. Note that this functionality _can't_ be mixed with display text, like this: `[[Page name|Display text]]s` - the rest of the word will be silently ignored in the link!

### Changed
 - Overhauled internal history logic - history logic is now done in core.
 - Added `$env->page_filename`, which points to the current page on disk.
 - Changed the way different display modes are accessed. You can now use the new `mode` parameter to the `view` action. It supports 4 different modes at present: `normal`, `printable`, `contentonly`, and `parsedsourceonly`.
 - Improved recent changes links a bit.
 - Improved tabbing through the file upload form.
 - Changed the way users' data is stored to support arbitrary per-user data
 - Sorted list of registered actions on the dev help page
 - The page editor's main content box now automatically expands as you're typing! If you've got a custom theme, you may need to tweak it a bit. Help available on request.
 - Pages that are redirects how have their names appear in italics in search results.

### Fixed
 - The login session lifetime is now configurable (defaults to 24 hours), so you won't keep getting logged out all the time (in theory). (#113)
 - Recent changes made on different days are now displayed separately, as they should be (#112)
 - Always display footer message at the bottom of the page.
 - Trim the image url before short image url detection (#108)
 - Fixed huge issue with `contentonly` display mode.
 - Improved the search engine indexing algorithm. It now shouldn't choke on certain special characters (`[]{}|`) and will treat them as word boundaries.
 - Fixed tag links at the bottom of pages for tags with a single quote (`'`) in them.
 - Correct error message when attempting to move a page
 - Improved security of PHP session cookie by setting HttpOnly flag.
 - Linked pages with single quotes (`'`) in their names correctly in page lists.
 - Fixed blank descriptions in search results by defaulting to a snippet from the beginning of the page.

## v0.12.1

## Fixed
 - Added error detection to the code that loads `peppermint.json`.

## v0.12.1-beta1

### Added
 - Added a class to the search term highlighting to aid theming (#92)
 - Check for pages with various uppercased letter combinations for matching pages (#87)
 - Support hashes in internal links (#96)
 - Support hashes on redirect pages (#96)
 - Added some tips to the parsedown parser help section
 - Added some more stats to the dev help page (#97)
 - Added the time taken to search to the search results page (#97)
 - Added support for unicode characters in page names (#95)
 - Autofill the name box on the file upload page when a new file is selected (#101)
 - Redirect the user automatically from the login page on refresh if they are already logged in (#102)
 - Suggest an appropriate filename when saving an automatically generated preview (#105)
 - When using the default theme images will now not flow beyond the edge of the page.

## Changed
 - Made the background of tags slightly lighter (#91)
 - Improved the appearance of the search context below each result.
 - Tweaked display of result numbers in the search results.
 - Allowed spaces in the filenames of images in the image syntax.

### Fixed
 - Critical: Make sure that all wiki related files are stored in the data directory (#89)
 - Critical: Fixed a HTML injection attack possible through search context generation (#94)
 - Sort the list of all the tags on a wiki (#88)
 - Explicitly set permissions on parent directories created (#86)
 - Allow `<tab>` characters to be entered into the editing page textarea (#84)
 - Fixed search context generation (#30)
 - Fixed bug in page moving code.
 - Prevented the page index data for parent pages from disappearing when a child page is edited (#98)
 - Fixed file uploading when the data storage directory not the current directory (#100)
 - Fixed pressing the edit button on pages that have a single quote in their name
 - Fixed a spelling mistake on the file preview page - I'm sure I fixed that before...!
 - Fixed an issue whereby the search index wouldn't update if your pages contained special characters
 - Fixed an issue with the recent changes list not updating when the number of recently changes reached `settings.max_recent_changes` (#104)
 - Fixed changes disappearing from the recent changes page (#106)

## v0.12

(No changes were made between the last beta release and this release)

## v0.12-beta2

### Changed
 - Changed the revision display text ("Revision created by..." -> "Revision saved by...")

## v0.12-beta1

### Added
 - Page history! Currently you can't do anything with the previous revisions - that will come in a future release.
 - Implemented delayed indexing (#66)
 - Added the time a page was last modified to the footer (#67)
 - Added unified diff to edit conflict resolution pages (#64)
 - Added image captions (#65)
 - Added short syntax for images (#24)

### Changed 
 - Added text "Tag List: " to tag listing pages
 - Added checkerboard pattern behind transparent images on mouse hover on their preview pages.
 - Improved support for SVGS.
	 - SVGs are sent as-is instead of a preview image unless `$settings->render_svg_previews` is set to `true`.
	 - Added code to find the dimensions of an SVG.
 - Reduced the amount of space that the login bit in the top left takes up.

### Fixed
 - Fixed a bug in the idindex generator.
 - Fixed an issue where you wouldn't be redirected correctly if you typed your password incorrectly

## v0.11

## Changed
 - Set title of image to alt text

## v0.11-beta2

### Changed
- Redirect to audio / video in preview generator if the data storage directory is the current directory

### Fixed
 - Polyfill `getallheaders()` if it isn't present
 - Bugfix failed upload message

## v0.11-beta1
### Added
 - Unlocked the uploading of any file type. Note that only the file types specified in the settings are allowed to be uploaded.
	- Uploaded video and audio files can now be viewed on their respective pages
	- The file preview tool is now aware that not everything will be an image.
 - Enhanced the recent changes page.
	- New pages show up with an 'N' next to them (as they do in a MediaWiki installation)
	- Page deletions show up in red with a line though them
	- Uploads show with an arrow next to them along with the size of the uploaded file
 - Added mathematical expression parsing between dollar signs.
 - Generated previews now have etags. This should speed up load times of subsequent requests significantly.
 - Added some extra built-in variables to the parser.
	- `{{{~}}}`: Displays the top level page name (i.e. the page that has been requested).
	- `{{{*}}}`: Displays a comma-separated list of subpages of the requested page.
 - Links to non-existent pages are now coloured red by default.

### Changed
 - Enhanced the dev help page some more
 - Changed the uploaded file preview generation to use imagemagick. You now need to have the `imagick` php extension installed (installation on linux: `sudo apt-get install php-imagick`).
 - The uploaded file preview generation action will now return audio and video files as-is. This allows for HTML5 video / audio tags to be used to view audio and video files.
 - Made username box autofocus on login page.
 - Added tab indexes to editing form

### Fixed
 - Fixed the downloader
 - Fixed an issue with the recent changes page and redirects causing a large number of warnings
 - Fixed a number of issues with the parser
	- Image urls may now contain ampersands ('&')
	- Several warnings that were cropping up here and there due to bugs have been squashed
	- Fixed an issue with multiple links in the same paragraph
 - Fixed a number of issues with the image preview generator
	- Requests for a previews of pages that don't have an associated file won't break anymore. An error image will now be returned instead.
	- A number of things that were not compatible with PHP 7 have been updated to ensure compatibility.
 - Conflict resolution. If someone saves an edit to a page  after you started editing, you will get a conflict resolution page.

# Notes
 - Test the etag code!

## v0.10

### Added
 - Added a license. Pepperminty Wiki is now licensed under the Mozilla Public License 2.0.

### Fixed
 - Corrected a minor error in the description of the page viewer module.
 - Corrected a minor spelling mistake in the credits page.

## v0.10-beta2

## Fixed
 - Added the moderator diamond next to the link to the update page in the credits.
 - Corrected the version numbers of a large number of modules that I forgot to change.

## v0.10-beta1

### Added
 - This changelog. It's long overdue I think!
 - Added the all tags page to the "More..." menu by default.
 - Added recent changes page under the action `recent-changes`. A link can be found in the "More..." menu by default.
 - Changed the cursor when hovering over a time to indicate that the tooltip contains more information.
 - Added icons to the "More..." menu
 - Added help section to parsedown parser.
 - Added more information to the dev help page.
 - Added templating! It works the way you'd expect it to in Mediawiki.
 - Help section ids now show to the right of the help section headers by default.

### Changed
 - Improved appearance of the all pages list.
 - Improved apparence of the tag list page.
 - Added a link back to the list of tags on the list of pages with a particular tag.
 - Upgraded help page. Modules can now register their own sections on a wiki's help page.
 - Optimised search queries a bit.
 - Save preprocessors now get passed an extra parameter, which contains the old page source.
 - Changed the default parser to parsedown.
 - Removed parsedown from the `parser-parsedown` module and replaced it with code that automatically downloads parsedown and parsedown extra on the first run.
 - Removed Slimdown addition from the parsedown parser and replaced it with a custom extension of parsedown extra.
 - Moved printable button to bottom bar and changed display text to "Printable version".
 - Redirect pages now show in italics in page lists.
 - Made other minor improvements to the page lists.

### Fixed
 - Removed debug statement from the redirect page module.
 - Improved the "There isn't a page called..." message you sometimes see when searching.
 - Corrected a few minor spelling issues on the help page.
 - The `recent-changes` module now has a proper help section.
