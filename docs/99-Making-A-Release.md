# Making a Release
This page contains a few notes about making a release of Pepperminty Wiki. These notes are intended to remind me of things I need to do, but you may find them useful.

The following things need to be done to prepare for a release:

 - Check for outstanding issues
 - Check the changelog
 - Make sure that the README is up to date
 - Make sure that Pepperminty Wiki actually works
 - Make sure that the [downloader actually works](https://github.com/sbrl/Pepperminty-Wiki/releases/tag/v0.19.1-hotfix1)
 - Bump the version:
	 - In the `version` file
	 - In the changelog
	 - In `apidoc.json` (TODO: Automate this?)
     - In `package.json` (TODO: Automate this?)
 - (Stable releases only):
     - Pull down changes to update [online downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php)
     - Ensure docs are up-to-date
 - Update wikimatrix
 - Generate the hashes & signature (see the `sign` build action)
 - Write & publish the release

## Release text template
The following template text can be used for releases:

```markdown
INTRODUCTION_HERE

(DELETE_IF_APPROPRIATE)
Note that this is a maintenance release that backports some urgent bugfixes to LATEST_STABLE_VERSION. Current development efforts are focused on NEXT_VERSION. The work-in-progress changelog for NEXT_VERSION can be found [here](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Changelog.md).

Have you updated to this release? [Click this link to say hi](https://starbeamrainbowlabs.com/blog/viewtracker.php?action=record&format=text&post-id=pepperminty-wiki/PEPPERMINTY_WIKI_VERSION)!

This release also has an experimental GPG and SHA256 hashes file attached. My GPG key is `C2F7843F9ADF9FEE264ACB9CC1C6C0BB001E1725` - please [open an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/) if you encounter any issues :slightly_smiling_face:

## Updating
You can update to this release simply by grabbing an updated copy of `index.php` and replacing the version in your current wiki (don't forget to take backups! I make every effort to squash as many bugs as possible, but you can never be too certain). You can get an updated copy of `index.php` in a number of ways:

 - By downloading the `index.php` file attached to this release
 - Using the [online downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php) (always has the latest stable version)
 - Using the online downloader offline
 - Building your own from source

For more information on the last 2 methods, please see [the documentation](https://starbeamrainbowlabs.com/labs/peppermint/__nightdocs/05-Getting-A-Copy.html) for more information.

## Since VERSION_NUMBER_HERE
FULL_CHANGELOG_HERE
```
