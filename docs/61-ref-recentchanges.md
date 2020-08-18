# `recentchanges.json` Reference

 - **Filename:** `recentchanges.json`
 - **Index Type:** Primary
 - **Location:** Data directory root
 - **Purpose:** Storing a list of recent changes

Like the page index stored in [`pageindex.json`](./60-ref-pageindex.md), `recentchanges.json` contains a list of all the recent changes on a wiki. It is located in the root of the data directory (along with the page index), and is formatted as JSON (as you might expect). It consists of an array of objects in reverse-chronological order - i.e. the most recent change is at the top of the file. Not _every_ change will be documented here however - as wikis may limit the number of recent changes stored (the default is currently `512` - this  is controlled by the `[max_recent_changes`](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php#config_max_recent_changes) setting).

Note that if you are writing a module, you should **NOT** write to the recent changes list directly. Instead, you should use the `add_recent_change()` method provided by the `feature-recent-changes` module.

Each object in the array contains a number of common keys:

Property		| Type		| Purpose
----------------|-----------|----------------------------
`type`			| `string`	| The type of change that was made. The currently recognised values are detailed in the table below.
`timestamp`		| `int`		| The UNIX timestamp at which the change was made. Note that this is probably going to change to be a datetime string instead soon.
`page`			| `string`	| The name of the page that changed.
`user`			| `string`	| The username of the user that made the change.
`newsize`		| `int`		| Optional. If `type` is `edit` or `revert`, this is the new size of the page after the edit was made.
`sizediff`		| `int`		| Optional. If `type` is `edit` or `revert`, this is the size difference from old page to the new page.
`filesize`		| `int`		| Optional. If `type` is `upload`, then this is the size of the file that was uploaded.
`oldpage`		| `string`	| Optional. If `type` is `move`, then this is the name of the old page that was moved to the name present in the `page` property.
`comment_id`	| `string`	| Optional. If `type` is `comment`, then this is the ID of the comment that was made. Useful for linking directly to a comment (e.g. `#comment-COMMENT_ID_HERE`).

The following values for the `type` property are currently recognised:

Type		| Extra properties		| Meaning
------------|-----------------------|-----------------------
edit		| `newsize`, `sizediff`	| A page was edited.
revert		| `newsize`, `sizediff`	| A page was reverted to a previous history revision (this generates a new history revision with a copy of the data from the old revision).
deletion	| 						| A page was deleted.
move		| `oldpage`				| A page was moved to a new name.
upload		| `filesize`			| A file was uploaded.
comment		| `comment_id`			| A comment was made on a page.

It is not recommended that `type` is set to any other value than those in the above table, as this will result in undefined behaviour.
