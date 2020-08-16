# `pageindex.json` Reference
When working with Pepperminty Wiki, inevitably there will come a point when you'll be interested in the data structures that Pepperminty Wiki uses to store it's primary (i.e. doesn't exist elsewhere) data. Here, the structure of arguably the most important index is discussed - `pageindex.json`.

As the name suggests, `pageindex.json` (henceforth called simply "the page index") contains an index of all the pages present in a wiki and their metadata. It's structure is 1 big JSON object, like this:

```json
{
	"Page Name": { ... },
	"Another Page Name": { ... },
	....
}
```

This structure allows for easy and rapid lookups of metadata relating to a specific page. Each page has it's own metadata object - the structure of which is explained below.

## Page Metadata
As mentioned above, each page has it's own metadata object. This object has a large number of potential different keys from different modules. It is expected that should any module (whether written by @sbrl or otherwise) that needs to store small amounts of page-specific metadata will put it in that page's metadata object.

All the currently known keys in a page's metadata object are listed below. Note that only the core keys may exist. All of the others may or may not exist depending on the specific circumstances.

Key					| Module			| Type		| Purpose 
--------------------|-------------------|-----------|-------------
`filename`			| _core_			| `string`	| The filename where the page's data is stored, relative to the data dir (use `$env->page_filename` instead, not this directly unless you're moving it on disk)
`size`				| _core_			| `int`		| The size of the page in bytes
`lastmodified`		| _core_			| `string`	| The UNIX timestamp when the page was last modified (this will change to a datetime string sometime soon)
`lasteditor`		| _core_			| `string`	| The username of the last user to edit the page.
`tags`				| _core_			| `string[]`| List of tags attached to the page
`history`			| `feature-history`	| `object[]`| List of history object revisions (see below)
`protect`			| `action-protect`	| `bool`	| Whether the page is protected from editing or not
`uploadedfile`		| `feature-upload`	| `bool`	| Whether the page has an associated uploaded file or not
`uploadedfilepath`	| `feature-upload`	| `string`	| Path (relative to the data storage dir) to the uploaded file
`uploadedfilemime`	| `feature-upload`	| `string`	| The MIME type of the uploaded file
`redirect`			| `feature-redirect`| `bool`	| Whether the page is a redirect page or not
`redirect_target`	| `feature-redirect`| `string`	| The page name that the redirect page redirects to
`redirect_absolute`	| `feature-redirect`| `bool`	| Whether the redirect is absolute or not. Absolute redirects are a raw URL that is put into the `location` header.

If a bool doesn't exist, then it is assumed that it's value is `false`.

### History Revision
History revisions in `history` array of objects also have a particular structure. Additionally, order matters: They _must_ be in ascending order of revision id.

Key			| Type		| Purpose
------------|-----------|-------------------
`type`		| `string`	| The type of edit. Current known values: `edit`, `revert`
`rid`		| `int`		| The revision id.
`timestamp`	| `int`		| The UNIX timestamp of when the edit was made (again, this will be changed to a datetime string soon).
`filename`	| `string`	| The filepath (relative to the data storage dir) to this specific revision's content on disk (see `$env->page_filename`, as it's sensitive to the history revision being requested. See also `$env->history`).
`newsize`	| `int`		| The new size of the page, in bytes
`sizediff`	| `int`		| The difference in size between the previous version and this version
`editor`	| `string`	| The username of the user who saved this revision.
