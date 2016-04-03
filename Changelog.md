# Changelog
# v0.11-dev
# Added
 - Unlocked the uploading of any file type. Note that only the file types specified in the settings are allowed to be uploaded.
 - Enhanced the recent changes page.
    - New pages show up with an 'N' next to them (as they do in a MediaWiki installation)
    - Page deletions show up in red with a line though them
    - Uploads show with an arrow next to them along with the size of the uploaded file

# Changed
 - Enhanced the dev help page some more

# Fixed
 - Fixed the downloader
 - Fixed an issue with the recent changes page and redirects causing a large number of warnings

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
