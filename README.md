# Pepperminty-Wiki
A wiki in a box

Pepperminty wiki is a complete wiki in a box inspired by @am2064's Minty wiki, which can be found here: https://github.com/am2064/Minty-Wiki

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

### Quickstart
All you need to do is download `index.php` in this repository and put it in a folder on your web server. You need to make sure that PHP can write to that folder though. However, you can deny write access to the file itself since there is no automatic updating function built in yet.

If you prefer, you can clone this repository or use the "Download Zip" button to the right.

### Building
Pepperminty Wiki uses a simple PHP based build script. If you want to run this script yourself (for whatever reason), follow these instructions:

1. Clone this repository
2. Delete `index.php`.
3. Run `php build.php`.

## Configuring
To configure it, open your downloaded copy of `index.php` in your favourite editor - the settings can be configured at the top of the file. There are extensive comments that explain what each option does. Make sure that you change the allowed usernames and passwords! If you need more help, don't hesitate to open an issue on this repository or contact me.

## Themes (aka strings of CSS)
Wanted: Themes! If you have a cool theme, simply open an issue on the bug tracker in this repository to share your theme. If you don't have a github account, no problem! Simply email me with your code instead.

## Todo
 * Add page history somehow
 * Allow users to change their passwords
 * Add auto updating system that doesn't wipe your settings
 * Add page deletion mechanism
 * .... (open an issue if you have any suggestions)

--Starbeamrainbowlabs
