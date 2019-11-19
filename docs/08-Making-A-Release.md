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
 - Write & publish the release
