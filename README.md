# Pepperminty-Wiki
A wiki in a box

Pepperminty wiki is a complete wiki in a box inspired by @am2064's Minty wiki, which can be found here: https://github.com/am2064/Minty-Wiki

## Screenshots
![Screenshot of Pepperminty Wiki](http://i.imgur.com/xOfCSEx.png)
Above is the main page of the demo over at https://starbeamrainbowlabs.com/labs/peppermint.

## Demo
A live demo (with editing disabled) can be found over at https://starbeamrainbowlabs.com/labs/peppermint.

## Features
 * Configurable settings
 * User login system
 * Page Creation
 * Markdown pages
 * Printable page view
 * Internal Links
 * Customisable theme
 * Basic 'search' bar
 * List of all pages and their last editors, edit times, and sizes
 * Inbuilt help page

## Downloading / Installing

### Requirements
Any PHP enabled web server will do. You need to make sure that it has **session support** though, as they are used to allow users to log in. If your PHP server does not have session support, you will know about it quite quickly since it won't remember you logging in.

Information about configuring PHP sessions can be found here: https://php.net/manual/en/session.installation.php

You must also make sure that PHP can write to the folder that you are going to install Pepperminty Wiki in.

More detailed information about installing Pepperminty Wiki can be found on [this repository's wiki](https://github.com/sbrl/Pepperminty-Wiki/wiki/Installing) (CATION: OUTDATED - SEE BELOW).

### Getting Your Copy
Pepperminty Wiki is made up of a single file: `index.php`. You can get it in 3 different ways:

#### From this repository
The fastet way is to download the `index.php` file located in the repository. This is the development release, and should _mostly_ work. It comes pre-packaged with all the latest modules, too!

#### From the latest release
The repository also has (semi) regular releases that are (mostly) guaranteed to be stable. You can get the latest one from the [releases page](https://github.com/sbrl/Pepperminty-Wiki/releases). The latest release comes with all the latest plugin releases too!

#### From the downloader
If you want to choose which modules you want in your wiki, you can use the downloader. This lets you choose the modules you want - the system will automatically build the a customised copy just for you! The downloader will be updated on each release (if it hasn't been updated please open an issue).

You can find it here: [Pepperminty Wiki Downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php)


#### From source
If you want to build Pepperminty Wiki from source, you can do this in 2 ways. Start by cloning this repository, and then follow the instructions below. If you don't have git installed, simply click the "Download Zip" button to the right.

##### From the command line
1. Delete the modules you don't want installed in the `modules/` folder
2. Run `php rebuild_module_index.php`
3. Delete `index.php` if it exists
4. Run `php build.php`

If you are on Windows, you can run the `build.bat` batch file I wrote instead of steps 2-4.

##### From the web
1. Start a local web server in the root of the cloned repository
2. Navigate to `download.php` in your web browser on the local web server
3. Tick the boxes of the modules you want
4. Click the download button

## Configuring
To configure it, open your downloaded copy of `index.php` in your favourite editor - the settings can be configured at the top of the file. There are extensive comments that explain what each option does. Make sure that you change the allowed usernames and passwords! If you need more help, don't hesitate to open an issue on this repository or contact me.

## Updating
1. Rename your old `index.php` to `index.old.php` temporarily
2. Obtain the new version (see above)
3. Open both files for editing
4. Copy your settings over the new settings (making sure that you don't delete any new settings - it will be obvious if you do this if you have error reporting enabled)

### Breaking Changes
From time to time breaking changes will be made. By this I mean additions and / or deletions to the settings that can be found at the top of your wiki's `index.php`. They will be listed here so you can manually update your settings if required.

 * Everything has been changed! Pepperminty wiki is now using a module based system.

## Real World Usage
 * (none yet! Contact me by email, [twitter](https://twitter.com/SBRLabs), or open an issue and I'll add a link here!)

## Themes (aka strings of CSS)
Wanted: Themes! If you have a cool theme, simply open an issue on the bug tracker in this repository to share your theme. If you don't have a github account, no problem! Simply email me with your code instead.

A theme gallery can be found here: [Theme Gallery](https://github.com/sbrl/Pepperminty-Wiki/wiki/Theme-Gallery)

### Default
This is the default theme Pepperminty Wiki currently comes with.

```css
body { font-family: sans-serif; color: #333333; background: #f3f3f3; }
label { display: inline-block; min-width: 10rem; }
textarea[name=content] { display: block; width: 100%; height: 35rem; }
/*input[name=page] { width: 16rem; }*/
nav { position: absolute; top: 5px; right: 5px; }
th { text-align: left; }
.sitename { text-align: center; font-size: 2.5rem; color: #222222; }
.footerdivider { margin-top: 4rem; }
```

### Simple Blue
A quick blue theme I put together to give people a choice of more than just one theme.

```css
body { font-family: sans-serif; color: #3765ff; background: #cee6ff; }
label { display: inline-block; min-width: 10rem; }
a:active { color: #95aeff; }
textarea[name=content] { display: block; width: 100%; height: 35rem; }
input:not([type=button]):not([type=submit]), textarea { padding: 5px 8px; color: #2c49c6; background: rgba(42, 146, 255, 0.57); border: 0; border-radius: 5px; }
input[type=submit], input[type=button], button { margin-top: 8px; padding: 5px 8px; }
::-webkit-input-placeholder { color: #2c49c6; }
nav { position: absolute; top: 5px; right: 5px; }
nav input { width: 15.2rem; }
th { text-align: left; }
.sitename { text-align: center; font-size: 2.5rem; color: #385fe2; }
.footerdivider { margin-top: 4rem; }
```

### Microsoft-esque
Another quick theme based on the Microsoft website.

```css
body { font-family: 'Segoe UI', sans-serif; color: black; background: white; padding: 5px; }
h1 { margin-top: 1.5em; }
label { display: inline-block; min-width: 10rem; }
textarea[name=content] { display: block; width: 100%; height: 35rem; }
nav { position: fixed; top: 0; left: 0; right: 0; padding: 10px 10px; background: #0073c6; color: white; }
nav a { color: white; font-weight: bold; transition: all 0.25s; }
nav a:active { color: #eeeeee; }
input { border: 2px solid #d2d2d2; padding: 5px; font-family: 'Segoe UI', sans-serif; }
input[type=search] { width: 18rem; }
button, input[type=submit] { margin: 10px 2px; cursor: pointer; transition: all 0.25s; }
button:active, input[type=submit]:active { background: #c2c2c2; }
th { text-align: left; }
.sitename { text-align: center; font-size: 2.5rem; }
.footerdivider { margin-top: 4rem; }
```

## Todo
 * Add page history somehow
 * Allow users to change their passwords
 * Add auto updating system that doesn't wipe your settings and modules
 * Make links to non existent pages red
 * .... (open an issue if you have any suggestions!)

--Starbeamrainbowlabs
