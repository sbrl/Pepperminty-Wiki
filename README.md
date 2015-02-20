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
Any PHP enabled webserver will do. You need to make sure that it has **session support** though, as they are used to allow users to log in. If your php server does not have session support, you will know about it quite quickly since it won't remember you logging in.

Information about configuring PHP sessions can be found here: https://php.net/manual/en/session.installation.php

More detailed information about installing Pepperminty Wiki can be found on [this repository's wiki](https://github.com/sbrl/Pepperminty-Wiki/wiki/Installing).

### Quick Installation
All you need to do is download `index.php` in this repository and put it in a folder on your web server. You need to make sure that PHP can write to that folder though. However, you can deny write access to the file itself since there is no automatic updating function built in yet.

If you prefer, you can clone this repository or use the "Download Zip" button to the right.


### Updating
1. Rename your old `index.php` to `index.old.php` temporarily
2. Download the new version
3. Open both files for editing
4. Copy your settings over the new settings (making sure that you don't delete any new settings - it will be obvious if you do this if you have error reporting enabled)


### Building
Pepperminty Wiki uses a simple PHP based build script. If you want to run this script yourself (for whatever reason), follow these instructions:

1. Clone this repository
2. Delete `index.php`.
3. Run `php build.php`.

## Configuring
To configure it, open your downloaded copy of `index.php` in your favourite editor - the settings can be configured at the top of the file. There are extensive comments that explain what each option does. Make sure that you change the allowed usernames and passwords! If you need more help, don't hesitate to open an issue on this repository or contact me.

## Themes (aka strings of CSS)
Wanted: Themes! If you have a cool theme, simply open an issue on the bug tracker in this repository to share your theme. If you don't have a github account, no problem! Simply email me with your code instead.

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

### Microsoft-esque
Another quick theme based on the microsoft website.

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
 * Add auto updating system that doesn't wipe your settings
 * Make links to non existant pages red
 * .... (open an issue if you have any suggestions!)

--Starbeamrainbowlabs
