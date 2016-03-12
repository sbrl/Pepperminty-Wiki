# Changelog

## v0.10
## Added
 - This changelog. It's long overdue I think!
 - Added the all tags page to the "More..." menu by default.
 - Added recent changes page under the action `recent-changes`.
 - Changed the cursor when hovering over a time to indicate that the tooltip contains more information.
 - Added icons to the "More..." menu
 - Help section to parsedown parser.

## Changed
 - Improved appearance of the all pages list.
 - Improved apparence of the tag list page.
 - Added a link back to the list of tags on the list of pages with a particular tag.
 - Upgraded help page. Modules can now register their own sections on a wiki's help page.
 - Optimised search queries a bit.
 - Save preprocessors now get passed an extra parameter, which contains the old page source.
 - Changed the default parser to parsedown.
 - Removed parsedown from the `parser-parsedown` module and replaced it with code that automatically downloads parsedown and parsedown extra on the first run.
 - Removed Slimdown add from the parsedown parser and replaced it with a custom extension of parsedown extra.
 - Moved printable button to bottom bar and changed display text to "Printable version".

## Fixed
 - Removed debug statement from the redirect page module.
