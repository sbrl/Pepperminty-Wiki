# Changelog

## v0.13-dev

### Added
 - Added history support to the `raw` action.

### Changed
 - Overhauled internal history logic - history logic is now done in core.
 - Added `$env->page_filename`, which points to the current page on disk.
 - Changed the way different display modes are accessed. You can now use the new `mode` parameter to the `view` action. It supports 4 different modes at present: `normal`, `printable`, `contentonly`, and `parsedsourceonly`.

### Fixed
 - Fxed huge issue with `contentonly` display mode.

## v0.12.1-dev

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
