# Features
Pepperminty Wiki has actually acquired a rather extensive feature set.

 - Configurable settings
	 - Via `peppermint.json`
	 - GUI available for moderators
 - First-run wizard to help with basic configuration (since v0.19)
 - User login system
	 - Graphical user management table for moderators
 - Page creation
	 - Subpages fully supported
 - Markdown-powered syntax
	 - Powered by [Parsedown Extra](https://github.com/erusev/parsedown-extra/) (with [Parsedown Extreme](https://github.com/BenjaminHoegh/parsedown-extreme)), with additional extras
	 - Short syntax for:
		 - Referencing uploaded files
		 - Internal Links - links to non-existent pages show up in red
	 - Templating support
	 - Additional syntax for resizing and floating images (see inbuilt help page)
	 - File galleries
	 - Client-side mathematical expression parsing, courtesy of [MathJax](https://www.mathjax.org/) [optional]
 - Full page revision history (comparison / manipulation coming soon)
 - Optional time-delayed search indexing
 - Simple edit conflict detection
 - Edit previewing (since v0.14, thanks to @ikisler)
 - Printable page view
 - Customisable theme + theme gallery (new in v0.20!)
 - ~~Basic 'search' bar~~ A full-text search engine (since v0.13), with high-performance advanced query syntax (since v0.20)!
	 - Dynamic server-side suggestions (since v0.13)
 - Sidebar with a tree of all the current pages [optional]
 - Page tags
 - Page lists
	 - List of all pages
	 - List of all tags
	 - List of pages with a given tag
	 - List of recent changes
 - Inbuilt help page
	 - Dynamic - modules can add their own sections to it
 - File upload and preview
	 - Simple syntax for including media in a page (explanation on help page)
 - Page protection
 - Simple user settings page
	 - Set email address
	 - Change password
 - Threaded page comments (since v0.14)
 - Statistics system - can be extended by modules (since v0.15)
 - Should be fully accessible (screen readers etc) - [open an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/new) if you encounter any problems
 - Customisable module based system
	 - Allows you to add or remove features at will


## Compatibility
Some users have imported existing wikis from other software. This is made possible because Pepperminty Wiki will automatically rebuild the page index from the existing files in a directory if the page index (`pageindex.json`) doesn't exist or is deleted.

Notes about the import process are detailed below.

 - Pepperminty Wiki's internal and external link syntax is [compatible with vimwiki](https://github.com/sbrl/Pepperminty-Wiki/issues/new) (thanks @RyanGreenup!).

If you encounter any issues importing data from another wiki, please [open an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/new). I'd love to know about it - it may be possible to automate any conversion steps to ease the import process.
