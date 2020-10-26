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


## Features FAQ
A few features that users request that aren't obvious on how to use or enable are documented here.

### How do I add a "create new page" button?
Since version v0.23, Pepperminty Wiki has API support for creating a new page without knowing it's name - see issue [#194](https://github.com/sbrl/Pepperminty-Wiki/issues/194). While a create new page button isn'tin the user interface by default, one may be added as a navigation link to either the [`nav_links`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_nav_links), [`nav_links_extra`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_nav_links_extra), or [`nav_links_bottom`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_nav_links_bottom) configuration directives in `peppermint.json` (these must be edited directly - GUI support for these properties hasn't been implemented yet). An example navigation link to send a user to create a new page might look like this:

```json
[
	"+",
	"index.php?action=edit&unknownpagename=yes"
]
```

See the [`nav_links` documentation for more information on the wider navigation link syntax](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_nav_links).

### What's this about manual setup for the sitemap?
If the `page-sitemap` module is enabled, Pepperminty Wiki will have support for generating an XML sitemap. However, since a sitemap is typically located at the root of your domain with the filename `sitemap.xml`, crawlers must be instructed as to where they can find the sitemap for your wiki. This can be done by editing (creating if it doesn't exist) a file called `robots.txt` _at the top-level root of your domain name_, and appending something like the following:

```
Sitemap: http://wiki.example.com/path/to/index.php?action=sitemap
```

This will properly instruct crawlers on where to find the sitemap.

If you know of a way to do this via a `<meta />` tag or HTTP header instead, please [open an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/new) so that this extra manual step can be removed.
