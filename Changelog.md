# Changelog
This file holds the changelog for Pepperminty Wiki. This is the master list of things that have changed (second only to the commit history!) - though the information for any particular release can also be found in the description of it's page for every release made on GitHub too.


## v0.25-dev (unreleased)
This is the next release of Pepperminty Wiki, that hasn't been released yet.


- **Added:** Added official (experimental) Docker support via [a Dockerfile](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Dockerfile)! Check out the [*Docker* section in Getting Started page of the docs](https://starbeamrainbowlabs.com/labs/peppermint/__nightdocs/04-Getting-Started.html#docker) (`docs/04-Getting-Started.md`) for more information.
	- Community assistance is requested to a) check this documentation works for you and b) add documentation for other setups - e.g. Docker Compose, Kubernetes, Docker Swarm, etc. Please [open those pull requests](https://github.com/sbrl/Pepperminty-Wiki/pulls) :-)

### Changed
- Correctly check for `pdo_sqlite3` instead of `sqlite3` in `feature-firstrun`
- Catch and deal with more unpacking issues on first run (thanks, @daveschroeter in [#249](https://github.com/sbrl/Pepperminty-Wiki/issues/249))


### Fixed
- Fixed link to the interwiki links documentation on the help page if interwiki links have not yet been setup.
- Fixed typos in system text
- Fixed handling of [`firstrun_complete`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_firstrun_complete) setting if `peppermint.json` is prefilled with a `firstrun_complete` directive but the Wiki hasn't been initialised for the first time yet - useful for installations inside Docker
- Fill in `secret` with a secrely random value inside `peppermint.json` if it doesn't exist.... even if `peppermint.json` already exists. Also useful for Docker users.
- Fixed missing / wrong help sections for `page-sitemap` (shown only to mods) and `page-user-list`
- Clarify that `peppermint.json` is NOT covered by the [`data_storage_dir` configuration directive](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_data_storage_dir)
- Fix PHP warning when posting new comments ([#247](https://github.com/sbrl/Pepperminty-Wiki/pull/247) - thanks, @neapsix!)
- Fix PHP 8.2 deprecation warnings ([#248](https://github.com/sbrl/Pepperminty-Wiki/pull/248) - thanks, @neapsix!)


## v0.24
 - **Added:** `filter` GET parameter to the `list` action, which filters the list of pages to contain only those containing the specified substring.
 - **Fixed:** [Rest API] Documented `redirect` and `redirected_from` GET params to the `view` action.
 - **Fixed:** Fixed bug where templating variables were not populated under some circumstances.
 - **Fixed:** Typo on credits page
 - **Fixed:** Typos in user table

## v0.24-beta1

### Added
 - Added support for embedding external YouTube and Vimeo videos (e.g. `![alt text](https://youtube.com/watch?v=pID0xQ2qnrQ)`)
     - If you know of a cool service that should be supported, please open an issue - YouTube and Vimeo were just the only 2 I could think of
     - Known issue: specifying the size (i.e. with ` | 500x400` inside the brackets `()` there) doesn't currently work because iframes are weird
 - Added [oneboxing](https://meta.discourse.org/t/rich-link-previews-with-onebox/98088): rich previews for internal links. If an internal link with 3 square brackets (e.g. `[[[example]]]`) is on it's own with nothing before or after it on a line, then it'll be turned into a onebox
     - 2 new settings have also been added to control it: `parser_onebox_enabled` and `parser_onebox_preview_length`
     - TODO: Update the dynamic help page for this.
 - [Rest API] Add new `x-tags` HTTP header to `raw` action (**required** for v2.2 of the android client app to edit pages!)

### Changed
 - Display returnto URL above the login form if present to further mitigate CSRF issues
 - [Rest API] Return a 409 Conflict instead of a 200 OK on an edit conflict when saving a page in the `save` action, and add `x-failure-reason` for more errors

### Fixed
 - Stats: Fix crash when loading the stats page
 - Fix crash when leaving a top-level comment
 - [security] Fixed an XSS vulnerability in the `format` GET parameter of the `stats` action (thanks, @JamieSlome)
 - [security] Ensured that the `returnto` GET parameter leads you only to another place on your Pepperminty Wiki instance (thanks, @JamieSlome)
 - [security] Ensure that Javascript in SVGs never gets executed (it's too challenging to strip it, since it could be lurking in many different places - according to [this answer](https://stackoverflow.com/a/68505306/1460422) even Inkscape doesn't strip all Javascript when asked to)
 - [security] Fixed XSS when the `action` GET param doesn't match a known action
 - [security] User pages are now only savable in the HTTP API by either a moderator or the owning user (previously only the `edit` action was protected, so if you made a request direct to the `save` action, you could bypass the check)
 - StorageBox: Create SQLite DB if it doesn't exist explicitly with `touch()`, because some systems are weird
 - StorageBox: Fix crash when `index.php` is a symbolic link
 - Fixed erroneous additional entries in complex tables of contents
 - Make `PeppermintParsedown::extract_page_names` more multibyte safe to avoid empty statistics


## v0.23

### Added
 - Added HTTP API support for creating pages that don't yet have a name (#194)
     - This allows for having a "create new page" button in your navigation links - e.g. edit `nav_links`, `nav_links_extra`, or `nav_links_bottom` in your `peppermint.json` and add something like `[ "+", "index.php?action=edit&unknownpagename=yes" ]`.
 - XML sitemap support with the new `page-sitemap` module (manual setup required for crawlers to notice it: see [the documentation](https://github.com/sbrl/Pepperminty-Wiki/blob/master/docs/02-Features.md#whats-this-about-manual-setup-for-the-sitemap))
 - Experimental support for transparent handling of `[display text](./Page Name.md)` style internal links (disabled by default: enable the `parser_mangle_external_links` setting and delete the `._cache` directory to enable)
 - Added automatic system requirements indicator to first run (checks for various PHP extensions required for various different functions) - does not block you from proceeding, but does assist in first-time system configuration

### Changed
 - Updated the [configuration guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) to include count of how many settings we have
 - Also send a `x-robots-tag: noindex, nofollow` HTTP header for the login page (Semrush Bot, you better obey this one)
 - Support `page` as either a GET parameter or a POST parameter (GET takes precedence over POST)
 - Preview generation: If php-imagick is not installed but required for a particular operation, return a proper error message
 - File upload: If fileinfo is not installed, return a proper error message when someone attempts to upload a file
 - Add `image/avif` (AVIF image), `image/jxl` (JPEG XL image), and `image/heif`/`image/heic` to `upload_allowed_file_types` (you'll need to delete your entry in `peppermint.json` to get the new updated list) 
     - Also added these and `flac` (which was already allowed as an upload by default) to the data size calculator on `?action=help&dev=yes`


### Fixed
 - [security] Fixed some potential XSS attacks in the page editor
 - [security] Fix stored XSS attack in the wiki name via the first run wizard [CVE-2021-38600](https://github.com/hmaverickadams/CVE-2021-38600); low severity since it requires the site secret to do the initial setup & said initial setup can only be performed once (#222)
 - [security] Fix reflected XSS attacks ([CVE-2021-386001](https://github.com/hmaverickadams/CVE-2021-38600); arbitrary code execution in the user's browser due to unsanitized data) via the many different GET parameters in many different modules (#222)
 - [security] Automatically run page titles through `htmlentities()` (#222)
 - Fixed a weird bug in the `stats-update` action causing warnings
 - search: Properly apply weightings of matches in page titles and tags
 - Improved error handling on first run where the PHP Zip extension is not installed
 - Also extract to `._extra_data` if the directory is empty
 - Add `sidebar_show` to the settings GUI and the [configuration guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php)
 - Fix crash when using the search bar with PHP 8.0+
 - Prefix the default value of the `logo_url` setting with `https:`
 - Fix display of subpages in the sidebar, and also wrap subpage lists in a `<details />` element to allow collapsing them
 - Fix file upload error handling logic - a proper error page is now sent to the client
 - Create theme gallery help section instead of overwriting the one entitled "Jumping to a random page".
 - Fix broken character in recent changes log entry when moving pages


## v0.22
_No changes were made since the last release_

**Make sure you have PHP 7.3+ when you update past this point!** It isn't the end of the world if you don't, but it will make you more secure if you do.


## v0.22-beta3

### Changed
 - Don't emit custom CSS unless there's something to emit

### Fixed
 - Fixed `inbody:searchterm` advanced query syntax
 - Fixed inaccessible colours in the page list when using the dark theme
 - Fixed invalid HTML generated by new `hide_email` implementation


## v0.22-beta2

### Added
 - Added dark theme via `prefers-color-scheme` to configuration guide (see the stable channel guide [here](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) - will only be updated when v0.22 is released)
 - Added link thingy you can click next to each setting to jump right to it
 - [docs] Documented the structure of `pageindex.json` and `recentchanges.json`

### Fixed
 - Obfuscate the admin email address at the bottom of every page - we missed it in v0.22-beta1 (but got every other one though :P) (#205)
 - Bugfix: Don't use `->text()` for recursion when parsing markdown - it resets `->DefinitionData`, which breaks footnotes (#209)
 - Fix name of did you mean index: `didyoumeaninddex.sqlite` → `didyoumeanindex.sqlite` (feature is disabled by default; manual renaming required)

### Changed
 - Disable parser cache by default to avoid issues because said cache isn't invalidated when it should be (and doing so would take more of a performance hit than leaving it on)


## v0.22-beta1
**Make sure you have PHP 7.3+ when you update past this point!** It isn't the end of the world if you don't, but it will make you more secure if you do.

### Added
 - [Module Api] Add new `search::invindex_term_getpageids`, and `search::invindex_term_getoffsets`, and `search::index_sort_freq` methods
 - [Module Api] Add new `ends_with` and `filepath_to_pagename` core functions
 - Added new syntax features to PeppermintParsedown, inspired by ParsedownExtreme (which we couldn't get to work, and it wasn't working before as far as I can tell)
     - Checkboxes: `[ ]` and `[x]` after a bullet point or at the start of a line
     - Marked / highlighted text: `Some text ==marked text== more text`
     - Spoiler text: `Some text >!spoiler!< more text` or `Some text ||spoiler|| more text`
     - Superscript: `Some text^superscript^ more text`
     - Subscript: `Some text~subscript~ more text`
 - Added automatic table of contents! (#155)
     - Put `[__TOC__]` on a line by itself to insert an automatic table of contents
     - Note that the level of heading generated can be controlled (or even removed) by the new `parser_toc_heading_level` setting
 - Add `<meta name="theme-color" content="value" />` support with the new `theme_colour` setting. More information: [MDN](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/meta/name/theme-color), [caniuse](https://caniuse.com/#feat=meta-theme-color). Also used by some platforms to customise embed accents when generating a rich snippet (e.g. Discord).
 - Added reading time estimate to the top of wiki pages - control it with the new `readingtime_enabled` setting (#172)
     - The algorithm used to estimate reading times is the as the one used in Firefox's reader mode
 - Added similar page suggestions between the bottom of the page content and the comments - control it with the new `similarpages_enabled` and `similarpages_count` settings.
 - Added absolute redirect support - use it like this: `# REDIRECT [display text](INSERT_REDIRECT_URL_HERE)`
     - It's disabled by default due to potential security issues with untrusted editors - enable it with the new `redirect_absolute_enable` setting (default: `false`)
 - Added new settings to control various features more precisely
     - `comment_enabled` controls whether _anyone_ is allowed to comment at all or not
     - `comment_hide_all` determines whether the commenting system displays anything at all (if disabled, it's (almost) like the `feature-comments` doesn't exist - consider using the downloader to exclude the commenting system instead of enabling this setting)
     - `avatars_gravatar_enabled` determines whether redirects to [gravatar.com](https://gravatar.com/) should be performed if a user hasn't yet uploaded an avatar (if disabled then a blank image is returned instead of a redirect).
 - PDF previews now show the browser's UI when embedded in pages with the `![alt text](File/somefile.png)` syntax
- [Rest API] Add new `typeheader` GET parameter to `raw` action (ref [Firefox bug 1319262](https://bugzilla.mozilla.org/show_bug.cgi?id=1319262))

### Changed
 - **New policy:** Only [officially supported](https://www.php.net/supported-versions.php) versions of PHP are officially supported by Pepperminty Wiki.
 - Fiddled with Parsedown & ParsedownExtra versions
 - Removed ParsedownExtreme, as it wasn't doing anything useful anyway
     - Don't worry, we've absorbed all the useful features (see above) 
     - NOTE TO SELF: Don't forget to update wikimatrix.org when we next make a stable release! (if you are reading this in the release notes for a stable release, please get in touch)
 - Enabled horizontal resize handle on sidebar (but it doesn't persist yet)
 - [security] `SameSite=Strict` is now set on all cookies in PHP 7.3+
     - This prevents session-stealing attacks from third-party origins
     - This complies with the [new samesite cookies rules](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite#SameSiteNone_requires_Secure).
     - A warning is generated in PHP 7.2 and below = [please upgrade](https://www.php.net/supported-versions.php) to PHP 7.3+! (#200)
 - [security] The `Secure` cookie flag is now automatically added when clients use HTTPS to prevent downgrade-based session stealing attacks (control this with the new `cookie_secure` setting)
 - Standardised prefixes to (most) `error_log()` calls to aid clarity in multi-wiki environments
 - Improved pageindex rebuilder algorithm to search for and import history revisions - this helps when converting data from another wiki format
 - Improved spam protection when hiding email addresses. Javascript is now required to decode email addresses - please [get in touch](https://github.com/sbrl/Pepperminty-Wiki/issues/new) if this is a problem for whatever reason. I take accessibility _very_ seriously.
 - Bump weighting of title and tag matches in search results (delete the `search_title_matches_weighting` and `search_tags_matches_weighting` settings to get the new weightings)

### Fixed
 - Squashed a warning when using the fenced code block syntax
 - If a redirect page sends you to create a page that doesn't exist, a link back to the redirect page itself is now displayed
 - Really fix bots getting into infinite loops on the login page this time by marking all login pages as `noindex, nofollow` with a robots `<meta />` tag
 - Navigating to a redirect page from a page list or the recent changes list will no longer cause you to automatically follow the redirect
 - Limited sidebar size to 20% of the screen width at most
 - Fix the [large blank space problem](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Changelog.md#fixed-3) in all themes
 - Squashed the text `\A` appearing before tags at the bottom of pages for some users ([ref](https://gitter.im/Pepperminty-Wiki/Lobby?at=5f0632068342f4627401f145))
 - Fixed an issue causing uploaded avatars not to render
 - Fixed an obscure bug in the search engine when excluding terms that appear both in a page's title and body
 - Squashed a warning at the top of search results (more insight is needed though to squash the inconsistencies in the search index that creep in though)
 - Removed annoying scrollbars when editing long pages
 - Fixed an obscure warning when previewing PDFs (#202)
 - Ensure that the parent page exists when moving a page to be a child of a non-existent parent (#201)
 - Fixed templating (#203)
 - Fixed warning from statistics engine during firstrun wizard


## v0.21.1-hotfix1
**Note: If you're updating past this point, please change the value of the `secret` property in your `peppermint.json`!**

### Fixed
 - [security] Fix security issue in the debug action


## v0.21

### Fixed
 - Make `PEPPERMINT_THEME` environment variable work again when compiling on the command line
 - Fixed invalid HTML that was causing layout issues on the master settings page


## v0.21-beta1

### Added
 - Watchlists! A new addition has been added to the more menu to add the current page to your personal watchlist
     - An email will be sent to all users watching a page when an edit is saved to it (uses the PHP `mail()` function internally, via the [`email_user()`](https://starbeamrainbowlabs.com/labs/peppermint/docs/ModuleApi/#method_email_user) internal Pepperminty Wiki utility function)
 - Email address verification
     - Enabled by default. In order to receive emails users now need to verify their email address
     - This is done via a verification email that's sent when you change your email address (even if your email address is the same when you change your preferences and you haven't yet verified it)
     - A new `email_verify_addresses` setting has been added to control the functionality
 - Added dark theme to the [downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php) (will be updated at the next stable release)
 - Added initial mobile theme support to the default theme
     - There's still a bunch of work to do in this department, but it's a bit of a challenge to do so without breaking desktop support
 - Added autocomplete for tags when editing pages, powered by [Awesomplete](https://leaverou.github.io/awesomplete/)
     - The new `editing_tags_autocomplete` setting - enabled by default - toggles it, but why would you want to turn it off? :P
     - It should be reasonably accessible, judging from all the aria tags I'm seeing
     - Get in touch if you experience performance issues with fetching tag lists from your wiki
 - A command-line interface!
     - Wiki administrators with terminal/console access can now make use of a brand-new CLI by executing `php ./index.php` (warning: strange things will happen if the current working directory is not the directory that contains index.php and peppermint.json)
 - Added new `anoncomments` setting to control whether anonymous users are allowed to make comments (disabled by default) - thanks to @SeanFromIT for suggesting it in #181
 - Added markdown support for media captions (#184)
 - Finally: *Experimental* didyoumean support. Ever made a typo in a search query? The new didyoumean engine can correct query terms that are up to 2 characters out!
     - It's disabled by default (check out the new `search_didyoumean_enabled` setting), as it enabling it comes with a significant performance impact when typos are corrected (~0.8s-ish / typo is currently observed)
     - Uses the words in the search index as a base for corrections (so if you have a typo on a page, then it will correct it to the typo)
     - The index does _not_ currently update when you edit a page - this feature is still _very_ experimental (please report any issues)
     - A typo is a search query term that is both not a stop word and not found in the search index

### Fixed
 - Fixed weighted word support on search query analysis debug page
 - Added missing apostrophes to stop words in search system. Regenerating your search index will now yield a slightly smaller index
 - Fixed link loop when logging in for crawlers
 - [security] Bugfix: Don't leak the PHP version in emails when expose_php is turned off
 - Fixed handling of Unicode characters when emailing users - added new `email_subject_utf8` and `email_body_utf8` settings to control the new behaviour
 - Add new `email_debug_dontsend` setting for debugging emails sent by Pepperminty Wiki
 - Fixed pressing alt + enter to open a search in a new tab - it should no longer fail and briefly prompt to allow pop-ups
 - Squashed a bug in the new upgraded get/set_array_simple search optimisation
 - Updated Parsedown to squash warning in PHP 7.4+
 - Trailing commas in the tags box will no longer result in empty tags being added to pages.
 - Minor UI fixes
     - Multiple tags in search results and on page lists now have a margin between them
 - Newline characters (`\r` and `\n`) are now replaced with spaces in internal links (#186, thanks @SeanFromIT!)
 - Inbuilt help documentation corrections (#185, thanks @SeanFromIT!)
 - Fixed a warning message when a file fails to upload (thanks for the test file, @SeanFromIT)
 - Really fix the dot problem from v0.20.3-hotfix3 that @SeanFromIT reported

### Changed
 - Improved the search indexing system performance - again
     - Another search index rebuild is required
 - Optimisation: Don't generate the list of pages for the datalist if it isn't going to be displayed (especially noticeable on wikis with lots of pages)
 - Optimisation: Don't load the statistics index if it's not needed (also esp. noticeable on wikis with lots of pages)
 - Optimisation: Refactor `stas_split()` to be faster (informal testing shows ~18% faster → 4% total time)
 - [Module Api] Optimisation: Remove `search::transliterate` because it has a huge overhead. Use `search::$literator->transliterate()` instead.
 - [Module Api] Add new `absolute` and `html` optional boolean arguments to `render_timestamp()`
 - [Module Api] `search::extract_context()` and `search::highlight_context()` now take in a _parsed_ query (with `search::stas_parse()`), not a raw string

### Known bugs
 - Wow, a new section! Haven't seen one of these before. Hopefully we don't see it too often.....
 - The didyoumean search query typo correction engine does not currently update it's index when you save an edit to a page (the typo correction engine is still under development).

 
## v0.20.3-hotfix3
 - Squash password-based warning (#182, thanks, @SeanFromIT!)
 - Fix double-dot issue in uploaded files (#182, thanks, @SeanFromIT!)


## v0.20.2-hotfix2
 - Update Parsedown to squash warnings in PHP 7.4
 - Update the docs about how to get a copy


## v0.20.1-hotfix1
 - Fixed logic error in peppermint.json access checker (thanks, @nivex! #179)


## v0.20
_Just 1 change since the previous beta release._

 - Add optional "say hi" button to first-run setup wizard
     - You don't _have_ to click it, but it would be cool if you did :-)


## v0.20-beta1

### Added
 - Added automatic dark mode to default theme using [`prefers-color-scheme`](https://starbeamrainbowlabs.com/blog/article.php?article=posts%2F353-prefers-color-scheme.html)
 - [Module API] Added new `minify_css` module API function by refactoring the page renderer
 - [Module API] Change `page_renderer::is_css_url()` to require an argument
 - Added theme gallery, which can be accessed through a link in the master settings (if the new `feature-theme-gallery` module is present)
     - Theme gallery URLs can be added to the `css_theme_autoupdate_url` setting
     - A graphical interface can be used to switch between available themes from the galleries
     - No external HTTP requests will be made without consent
     - Themes from galleries auto-update every week by default (adjustable/disable-able with the `css_theme_autoupdate_interval` setting)
 - Added mega-menu support to the `nav_links_extra` setting - the default value for the `nav_links_extra` setting has now changed (delete/rename it in your `peppermint.json` file to get the new version)
     - An object can now be used to define groups of items in the more menu
     - Hopefully it now looks less cluttered :P
 - Headings now have an automatic id if you don't specify one (part of #141)
 - Server-side diagramming support! See the [`parser_ext_renderers`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_parser_ext_renderers_enabled) setting for more information on how to configure it
	 - It hooks into external programs such as [nomnoml](http://nomnoml.com), [plantuml](http://plantuml.com/), and `abcm2ps` ([ABC musical notation](https://abcnotation.com/) rendering)
	 - It's generic enough to allow you to hook into any program that takes an input of some source text, and output some form of image

### Fixed
 - Fixed a bug in the search query performance metrics
 - Fill out the statistics help text
 - Added table of contents to help page
 - Squashed the large blank space that appears at the bottom of the page editor page when editing long pages
 - Accessibility improvements - thanks, Firefox developer tools :D (if you're a screen reader / accessibility tool user and have feedback or any better ideas, please [get in touch](https://github.com/sbrl/Pepperminty-Wiki/issues/new))
     - Marked the user avatar on the top navigation bar as hidden for screen readers
     - Added aria label to user preferences button
     - Hide site logo from screen readers
 - Lists of pages that have a specific tag will now be sorted alphabetically (Unicode characters should be handled correctly too)
 - Support Unicode characters when sorting. If it's a list of something, then it's now sorted correctly (e.g. includes pages, tags, etc).
 - Squashed a bunch of warnings about a non-static method in the page renderer
 - Fixed a warning message in the `peppermint.json` access checker
 - Fixed footnote rendering (thanks again, @SeanFromIT!)

### Changed
 - Made `build.sh` build script more robust, especially when generating the documentation.
 - Vastly improved search engine performance
     - A new SQLite-based index format is now used, so search indexes will need to be rebuilt (migrating would probably take longer than a rebuild :-/)
 - New search query syntax
 - When making remote requests, Pepperminty Wiki will now correctly set the user agent string
     - The server's `expose_php` setting is respected - if it's disabled, then the PHP version will not be exposed.
     - Pepperminty Wiki _shouldn't_ make remote requests without you asking it to - see above and the theme gallery
 - Improved peppermint.json.compromised error message - if it's still unclear, please let me know
 - Fiddled with the extra data extractor, as it seems that some people were experiencing strange issues with `stream_get_meta_data()`
 - [Module API] Refactored the `errorimage()` function into core, added automatic image size calculation, and multi-line support


## v0.19.4-hotfix4
 - Fixed page revision id incrementing if you don't have a page called `history` on your wiki (thanks @SeanFromIT!)


## v0.19.3-hotfix3
 - Improve error messages in the extra data unpacker
 - Change the extra data unpacker to us  `tempnam()` instead of `tmpfile()`, since some people appeared to be having issues with the other approach
 - Squash a deprecation warning caused by a typo (thanks, @SeanFromIT!)


## v0.19.2-hotfix2
 - Patched another crazy bug in the extra data system in the downloader


## v0.19.1-hotfix1
 - Patched [the downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php) which was throwing warnings when packing downloads


## v0.19

_(No changes have been made since the last beta release.)_


## v0.19-beta2

### Changed
 - Updated the theme of the new documentation
 - Revised the writing modules section of the documentation


## v0.19-beta1
> The update that changed the world! Turn everything upside-down.

### Fixed
 - Fixed double-escaping of rendered HTML when nesting templates
 - Squashed a warning if the search index doesn't exist yet
 - Fixed a crash in the stats updater if no pages in the system have tags yet
 - Consolidated `email` and `emailAddress` fields into the latter in the user table (#167)
 - Fixed a crash when trying to access the user table when not logged in as an administrator.
 - Fixed help text for the file upload module
 - Squashed a warning when uploading a file

### Added
 - [Module API] Added new extra data system. See `parser-parsedown` and `page-edit` for an example.
     - Extra data is packed into a zip archive, which is packed into `index.php` via [`__halt_compiler();`](https://devdocs.io/php/function.halt-compiler)
     - See the `parser-parsedown` and `page-edit` modules for examples on how to use it.
 - [Module API] Added new `delete_recursive()` function.
 - Added a new obvious link to the user table at the top of the master settings page.
 - Added a new first-run wizard to help new users set up the basics of their wiki.
     - It also checks to ensure that access to `peppermint.json` is blocked correctly (coming soon as a one-time check to pre-existing wikis)
     - Pre-existing wikis will not see this first-run wizard - a new `firstrun_complete` setting has been added that's automatically set to true if Pepperminty Wiki does a settings upgrade
 - Default to allowing lossless flac and ogg audio files to be uploaded
 - Added new `minify_pageindex` setting, which defaults to `true` and causes the page index to be minified when saved. Improves performance slightly (especially on larger wikis as the page index grows), but makes debugging and ninja-edits more awkward.
 - [Module API] Added new `save_pageindex()` function which respects the above setting.
 - Added PDF preview using your browser's default PDF viewer!
 - Added download button for unsupported file types

### Changed
 - Core sharding: split `core.php` into multiple files
 - Big update to the backend Markdown parser _Parsedown_
     - Use Parsedown's new untrusted feature for comments
     - Added new `all_untrusted` setting to allow treating *everything* as untrusted (default: false) - turn it on if your wiki allows anonymous edits
 - Switch to [nightdocs](https://gitlab.com/sbrl/nightdocs) instead of [docpress](https://docpress.github.io/) (the [official docs](https://starbeamrainbowlabs.com/labs/peppermint/_docpress/) will update & change URL on the next stable release)
 - Add moar [badges](https://shields.io/) to the README & docs :D

### Removed
Not often I have a removed section!

 - [Module API] Remove remote file system in favour of the new extra data system. No more first-run downloads! They are all done at compile-time now.


## v0.18

_(No changes have been made since the last beta release.)_


## v0.18-beta1

### Added
 - Added inter-wiki link support via the new `feature-interwiki-links` module
     - Added `interwiki_index_location` setting to control the location of the interwiki index (which is a CSV file that is documented in the main _Pepperminty Wiki_ documentation), which has to be specified in order for it to activate
     - Provides new module api functions: `interwiki_pagename_parse`, `interwiki_pagename_resolve`, `interwiki_get_pagename_url`, and `is_interwiki_link`
 - Added new formats to the `recent-changes` action (append `&format=XXX` to the url)
     - Added CSV support (`csv`)
     - Added Atom 1.0 feed support (`atom`)
     - All alternative formats for this action are advertised in via a `<link rel="alternate" />` in the `<head>` now
 - Added `count` and `offset` GET parameters to `recent-changes` action.
 - Added new `find_revisionid_timestamp()` function to `feature-recent-changes` module.
 - Added new parser output caching system!
     - Saves to `._cache` in the data directory (alongside the pages themselves)
     - 2 new settings have been added: `parser_cache` and `parser_cache_min_size`
     - Uses a hash of the content, the _Pepperminty Wiki_ version, and the parser name as the cache id - so it should never serve stale content (unless you're actively working on particular areas of _Pepperminty Wiki_'s codebase of course)
     - Useful for longer pages
     - `parser_cache_min_size` may need tuning for your specific installation (lower it if you regularly use features that are slow to parse; raise if it's the opposite)
 - Internal links now show the page name on hover (inter-wiki links are also supported here)

### Changed
 - Completely reworked the README to refactor out the documentation to its [own static site](https://starbeamrainbowlabs.com/labs/peppermint/_docpress/)
 - Updated the `{{{@}}}` templating variable to output a message if no parameters were specified instead of not parsing it at all
 - [Module API] Refactored the main `page_renderer` class
     - All static methods now have a consistent naming scheme
     - Added `page_renderer::add_header_html()`

### Fixed
 - Squashed a warning in the history revision system when creating new pages (thanks @tspivey for spotting this!)
 - Standardise line endings to `\n` (linux)
 - Enhanced setup instructions in README.
 - Long lines in code blocks now wrap correctly.
 - The `export` action now correctly includes uploaded files alongside their descriptions


## v0.17.1

### Fixed
 - Corrected default passwords. If you were having issues, try updating to this release, deleting `peppermint.json` and trying again (thanks for spotting this, @tspivey!)


## v0.17

### Fixed
 - Removed stray debugging output
 - Tweaked css to make new search context generation look better


## v0.17-beta2

### Fixed
 - Fixed the cost-climbing bug in the last beta release


## v0.17-beta1

### Added
 - [Module API] Added `save_settings()` convenience method
 - [Rest API] Add `user-add` and `set-password` moderator actions
 - Added `random_page_exclude_redirects` setting that prevents the `random` action from returning redirect pages.
 - Added link to user table on the credits page
 - Added history reversion via the `history-revert` action
 - Added `history_max_revisions` setting to allow control of the maximum number of revisions stored for a page
     - Takes effect every time a page revision is added
 - Added page restore system
     - A previous page revision can be restored with a single click from the page history page
     - Added a new `history_revert_require_moderator` setting to control whether moderator privileges are required to use the functionality (regardless of setting a user must be logged in)
 - [HTTP/2.0 Server Push](https://www.smashingmagazine.com/2017/04/guide-http2-server-push/) support!
     - You'll need to make sure your web server has support turned on
     - The CSS file specified in the `css` setting (url path must begin with a forward-slash) and the favicon (must not be a `data:` url) are automatically pushed when rendering pages
     - 2 new settings have been added: `http2_server_push` for turning it on and off (defaults to on), and `http2_server_push_items` for specifying custom resources to push (in case you design your own theme and want to push down the associated resources)
     - More information about `http2_server_push_items` in particular is available on the [configuration info page](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) (when this release is out, of course. Until then, check out the description in `peppermint.guiconfig.json`)
 - Added `<meta name='generator' />` tag to all pages

### Fixed
 - Updated the search system to transliterate characters to better support searching pages that are written in other languages.
     - You'll want to rebuild your search index via the button in the configuration panel, or the `invindex-rebuild` action.
 - [Security] Made the site secret generator cryptographically secure. If you created your wiki before this change, you might want to change your site secret in `peppermint.json` to something more secure with a site like [random.org](https://www.random.org/).
     - The PHP function `openssl_pseudo_random_bytes()` was being used before, but [apparently that's not cryptographically secure](https://paragonie.com/blog/2015/07/how-safely-generate-random-strings-and-integers-in-php).
 - [Module API] Fix `full_url()` logic
 - [Module API] Make `email_user()` correctly return email sending failures
 - Squashed a warning in the search redirector
 - The search redirector will now check both the specified page name and the page name in Title Case
 - Improve help text description of image captions displayed alongside images
 - Fixed the page history page - it should now display all page revisions in valid HTML
 - Fixed another bug in the search context generator
 - Display an ellipsis at the beginning of a search context if it doesn't start at the beginning of a page
 - Semicolons are no longer automatically included in greedy internal links.
 - Pressing enter in the tag box now correctly previews instead of performing a smart restore

### Changed
 - Password hashing has been overhauled! A totally new-and-different system is being used now, so you'll need to rehash all your passwords.
     - The `hash` action supports the new password hashing scheme.
     - Added `password_cost`, `password_cost_time`, and `password_cost_time_interval` settings
     - `password_cost` is recalculated automatically every week by default (it keeps track of this via the `password_cost_time_lastcheck` 'setting')
 - The `css` setting will now keep a value of auto, even when `peppermint.json` is automatically updated by _Pepperminty Wiki_.
 - Optimised the search system a lot (#157 - ~2800ms searches now take ~450ms O.o)
     - Tuned the default value for `search_characters_context` down to 75 (this won't be the case for existing wikis, so you'll need to adjust it manually)
     - Added new `search_characters_context_total` setting to control the maximum characters in a search context
 - The `index` action's output should now be formatted nicely.
 - Restyled "matching tags" in the search results in the default stylesheet
 - Added moar icons to the nav / more menus. Delete the appropriate entries in `peppermint.json` to get the updated ones!


## v0.16
_(No changes since v0.16-beta1)_


## v0.16-beta1

### Added
 - Add json support to the search action :D
 - Added page moves to the recent changes page (#151)
 - Hyperlinked image preview on file pages to the original image (#153)
 - Added the commit hash Pepperminty Wiki was built against to the master settings configuration page, and the debug action
 - Added user count statistic
 - Added redirect page count statistic
 - Add orphan pages statistic
 - Add most linked-to pages statistic
 - [Rest API] Added support for the `mode` parameter to the `random` action
 - [Rest API] Added `format` parameter to `recentchanges` action
 - [Rest API] Added `comments-fetch` action to return a page's comments as JSON
 - [Rest API] Added `acquire-edit-key` action to allow scripts and other automated services (e.g. bots and [mobile apps](https://github.com/sbrl/Pepperminty-Wiki-Client-Android)) to fetch an edit key for a specified page name.
 - [Rest API] Added extra headers to `save` action on failure to aid identification by automated services

### Fixed
 - Fixed various issues with both the module api & the rest api docs.
 - Properly escaped content of short code box on file pages
 - Display a more meaningful message to a logged in user if editing is disabled
 - Fixed fetching the size of SVGs in some cases
 - Fixed image captions in some cases (let me know if you still get warnings)
 - Squashed warnings when you do a search with a forward slash (`/`) in your query
 - Fixed that age-old warning in the search results if you have pages with special characters! I learnt a _lot_ about utf8 whilst fixing this one.... (#114)
     - You'll need to rebuild your search index for this fix to fully take effect (call the `invindex-rebuild` action as a mod or better)
 - Normalise utf8 text to avoid duplicate ids and missing search results.
 - Improved handling of mime types in some places in the API.
 - [Rest API] Added `minified` option to `status` action to reduce data usage slightly
 - Fixed floating images that have captions
 - [Rest API] Fix `checklogin` action documentation
 - Fix link on credits page
 - Made rebuild search index progress bar fill completely up when done & neatened up UI placement

### Changed
 - Disallow uploads if editing is disabled. Previously files could still be uploaded even if editing was disabled - unless `upload_enabled` was set to `false`.
 - Added `x-login-required: yes` header to responses that redirect to the login page for easy detection by machines
 - Added `x-login-success: (yes|no)` header to login responses for easier machine parsing
 - Enhance the longest pages statistic rendering

### Removed
 - [Module API] Removed `accept_contains_mime`, as it's both unstable and currently unnecessary. Contributions for a better version are welcome!


## v0.15.1

### Added
 - Added an input box with auto-generated short markdown embed code with copy button to file pages

### Changed
 - Added 1920 as a preset image size on file pages

### Fixed
 - Fix saving edits to pages with an ampersand in their name (#99)
 - [Security] Fixed an authenticated denial-of-service attack when uploading a malicious SVG (ref XXE billion laughs attack, #152)


## v0.15
_(No changes since v0.15-beta2)_


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
     - Added action `stats` to view the calculated statistics
     - Statistics are automagically recalculated every day - this can be controlled with the `stats_update_interval` and `stats_update_processingtime` settings
 - Added new "« Parent Page" to subpages so that you can easily visit their parent pages
 - Users can now delete their own comments, and users logged in as a moderator or better can delete anyone's comments.
     - Added new `comment-delete` action
     - Comments are deleted entirely if they have no replies - otherwise the username & message are wiped
 - The `history` action now supports `format=json` and `format=CSV`
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
 - Removed some extra  at the bottom of some pages.
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
 - Added dynamic server-calculated page suggestions. Very helpful for larger wikis. Currently works best in Firefox. Part of the `feature-search` module.
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
 - Improved security of PHP session cookie by setting `HttpOnly` flag.
 - Linked pages with single quotes (`'`) in their names correctly in page lists.
 - Fixed blank descriptions in search results by defaulting to a snippet from the beginning of the page.

## v0.12.1

### Fixed
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

### Changed
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

### Changed
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

### Notes
 - Test the etag code!

## v0.10

### Added
 - Added a license. Pepperminty Wiki is now licensed under the Mozilla Public License 2.0.

### Fixed
 - Corrected a minor error in the description of the page viewer module.
 - Corrected a minor spelling mistake in the credits page.

## v0.10-beta2

### Fixed
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
 - Added [`peppermint_json_perms` setting](https://github.com/sbrl/Pepperminty-Wiki/blob/c245ea44c225153c234bc6902761064a9f5221a8/peppermint.guiconfig.json#L273) to automatically `chmod` `peppermint.json` on save - please heed warnings in the description!
	- TODO update this link to online config page on release

### Changed
 - Improved appearance of the all pages list.
 - Improved appearance of the tag list page.
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
