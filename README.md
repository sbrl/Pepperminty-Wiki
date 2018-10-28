# ![Pepperminty Wiki Logo](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/logo.png) Pepperminty Wiki [![Build Status](https://travis-ci.org/sbrl/Pepperminty-Wiki.svg?branch=master)](https://travis-ci.org/sbrl/Pepperminty-Wiki) [![Join the chat at https://gitter.im/Pepperminty-Wiki/Lobby](https://badges.gitter.im/Pepperminty-Wiki/Lobby.svg)](https://gitter.im/Pepperminty-Wiki/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![License: MPL-2.0](https://img.shields.io/badge/License-MPL--2.0-blue.svg)](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/LICENSE)

> A Wiki in a box

Pepperminty Wiki is a complete wiki contained in a single file, inspired by @am2064's [Minty Wiki](https://github.com/am2064/Minty-Wiki). It's open source too (under MPL-2.0), so contributions are welcome!

Developed by Starbeamrainbowlabs (though contributions from others are welcome!), Pepperminty Wiki has a variety of useful (and cool!) features - such as file upload, a dynamic help page, page revision history, page tags, and more! Other amazing features are in the works too (like a theme gallery, auto update, and user watchlists), so check the release notes to see what's been added recently.

**Latest Version:** v0.17 (stable) v0.18-dev (development) ([Changelog](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Changelog.md))

## Table of Contents
 - [Screenshots and Demo](#screenshots-and-demo)
 - [Features](#features)
 - [Demo](#demo)
 - [Getting Started](#getting-started)
	 - [Requirements](#requirements)
	 - [Getting your own copy](#getting-your-own-copy)
	 	 - [Method 1: Using the latest pre-built stable release](#method-1-using-the-latest-pre-built-stable-release)
		 - [Method 2: Grabbing the pre-built verion from the repository](#method-2-grabbing-the-pre-built-verion-from-the-repository)
		 - [Method 3: Using the online downloader](#method-3-using-the-online-downloader)
		 - [Method 3.5: Using the downloader offline](#method-35-using-the-downloader-offline)
		 - [Method 4: Building your own from source](#method-4-building-your-own-from-source)
 - [Configuring](#configuring)
     - [User Accounts](#user-accounts)
 - [API Reference](#api-reference)
 - [Real World Usage](#real-world-usage)
 - [Todo and Contributing](#todo)
 - [License](#license)

## Screenshots and Demo
[![Main Page Example](https://i.imgur.com/5dmbKlz.png)](https://imgur.com/a/lsBc3cM)

Above: The Main Page used for testing purposes.

A live demo of the latest stable version can be found over at [my website](//starbeamrainbowlabs.com/labs/peppermint).

More screenshots can be found in [this Imgur Album](https://imgur.com/a/lsBc3cM).

## Features
- Configurable settings
- User login system
- Page creation (Sub pages supported)
- Markdown-powered syntax
	- Templating support
	- Additional syntax for resizing and floating images (see inbuilt help page)
	- File galleries
	- Short syntax for referencing uploaded files
	- Client-side mathematical expression parsing, courtesy of [MathJax](https://www.mathjax.org/)
	- Links to non-existent pages appear red
- Full page revision history (comparison / manipulation coming soon)
- Optional time-delayed search indexing
- Simple edit conflict detection
- Edit previewing (since v0.14, thanks to @ikisler)
- Internal links - Links to non-existent pages show up in red
- Printable page view
- Customisable theme
- ~~Basic 'search' bar~~ A full-text search engine!
	- _Dynamic server-side suggestions (since v0.13!)_
- (Optional) Sidebar with a tree of all the current pages
- Tags
- List of all pages and details
	- List of all tags and pages with any given tag
	- List of recent changes
- Inbuilt help page (modules can add their own sections to it)
- File upload and preview
	- Simple syntax for including media in a page (explanation on help page)
- Page protection
- Simple user settings page (set email address, change password)
- Threaded page comments (since v0.14)
- Statistics system - can be extended with modules (since v0.15)
- Customisable module based system
	- Allows you to add or remove features at will

## Getting Started
### Requirements
- PHP-enabled web-server (must be PHP 7+)
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
- The following PHP extensions:
    - `mbstring` (for utf8 string handling - currently **required**)
    - `imagick` (for preview generation)
    - `fileinfo` (for proper mime type checking of uploaded files)
    - `zip` (for compressing exports)
    - `intl` (for Unicode text normalization when searching and in the id index)
- Write access to Pepperminty Wiki's own folder (only for editing)
- Recommended: Block access to `peppermint.json`, where it stores it's settings

### Getting your own copy
Setting up your own copy of Pepperminty Wiki is easy. Since Pepperminty Wiki works on a module based system, all you need to do is choose the modules you want installed, and then configure your new installation so that it fits your needs. There are several ways to do this:

#### Method 1: Using the latest pre-built stable release
If you want a pre-built stable version, then you can [use the latest release](https://github.com/sbrl/Pepperminty-Wiki/releases/latest). It has a changelog that tells you what has changed since the last release, along with a pre-built version with all the latest modules.

#### Method 2: Grabbing the pre-built version from the repository
If you're feeling lazy, you  can grab the bleeding-edge version from this repository, which comes with all the latest modules. You can get it [here](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/build/index.php).

#### Method 3: Using the online downloader
Pepperminty Wiki has a downloader that you can use to select the modules you want to include in your install. The online downloader will give you the latest stable release. You can find it [here](//starbeamrainbowlabs.com/labs/peppermint/download.php).

#### Method 3.5: Using the downloader offline
You can also you the downloader offline. Simply clone this repository to your web server and then point your web browser at `your.server/path/to/pepperminty/wiki/download.php`.

#### Method 4: Building your own from source
Pepperminty Wiki can also be built from source (and I do this all the time when testing). Start by cloning the repository. Then go into the `modules` folder and append `.disabled` to the names of any modules you don't want to be included (e.g. `modules/page-edit.php` would become `modules/page-edit.php.disabled`). Then follow the instructions for your platform:

##### Windows
Simply run the `build.bat` script in the root of the repository. It will handle everything for you.

##### Linux and Everyone Else
Run the following commands from the root of the repository in order, adjusting them for your specific platform (these are for a standard Ubuntu Server install):

```bash
rm build/index.php
php rebuild_module_index.php
php build.php
```

These commands are also in `build.sh`. You can run that if you want. Here's an explanation of what each command does:

1. Deletes the old `index.php` in the build folder that comes with the repository
2. Rebuilds the module index that the build scripts uses to determine what modules it should include when building
3. Actually builds Pepperminty Wiki. Outputs to `index.php`.

## Configuring
To configure your new install, make sure that you've loaded the wiki in your browser at least once. Then open `peppermint.json` in your favourite text editor. If you need any help, just contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new).

~~Please note that configuration of Pepperminty Wiki will be done through a GUI soon.~~ _Most_ properties are now configurable in a graphical interface! It can be accessed through the _Edit Master Settings_ option in the more menu, or the `configure` action (e.g. `https://wiki.example.com/?action=configure`) if it doesn't appear for you.

The [configuration guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) are all the configuration directives that Pepperminty Wiki (and all the modules included in the repository) understand. It is generated automatically from `peppermint.guiconfig.json`.

 - [Configuration Guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php)
 - [`peppermint.guiconfig.json`](https://github.com/sbrl/Pepperminty-Wiki/blob/master/peppermint.guiconfig.json))

### User Accounts
User account details are currently stored as an object in `peppermint.json`, under the `users` special setting. Each user has their own object, in which lies their user data.

While users can change their own passwords and email addresses, you'll inevitably want to add your own users. You can do this through the brand-new user management page if you're logged in as a moderator or better (the `user-table` action - example url: `https://example.com/path/to/index.php?action=user-table`), or manually. Here's how to do it manually:

1. Open `peppermint.json` in your favourite text editor.
2. Create a new property on the `users` object, whose value is an object and key is the new user's username. Use the existing users for reference.
3. Hash the new user's password. This can be done in the terminal or with the `hash` action - but make sure you don't leave any traces of your passwords lying around for others to find!
    a. To use the `hash` action, navigate to `https://example.com/path/to/index.php?action=hash&string=my_temporary_password`. Don't forget to change your password afterwards, or clear both your browser history & server logs! You could even use [the demo instance](https://starbeamrainbowlabs.com/labs/peppermint/build/?action=hash&string=password) I have running on my server, but I don't have a filter on my server logs :-)
    b. To do it through the terminal, the following one-liner should do the trick: `echo -n "some_password" | php -r 'echo(password_hash(base64_encode(hash("sha384", trim(fgets(STDIN)))), PASSWORD_DEFAULT) . "\n");'`
4. Save `peppermint.json` back to disk.

~~In the future, user accounts will be manageable through a graphical interface. Follow #127 for updates!~~ User accounts are now manageable through a graphical interface! Access it through the `Edit user table` option on the credits page.

#### Default Credentials
The default user account details are as follows:

 - `admin` with password `password`
 - `user` with password `cheese`

**Please remember to change your account password! Only you are responsible for the security of your account.**


## API Reference
I have documented the current API and other things that make Pepperminty Wiki tick that you can use to create your own modules. You can find this documentation in the [Module_API_Docs.md](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Module_API_Docs.md) file in this repository.

I've also documented Pepperminty Wiki's entire REST API using [apiDoc](http://apidocjs.com/). You can view the docs [here](https://sbrl.github.io/Pepperminty-Wiki/docs/RestApi/).

If you do create a module, I'd love to hear about it. Even better, [send a pull request](https://github.com/sbrl/Pepperminty-Wiki/pulls/new)!

### Tldr Quick Links:
 - [HTTP REST API](https://sbrl.github.io/Pepperminty-Wiki/docs/RestApi/)
 - [PHP Module API](https://sbrl.github.io/Pepperminty-Wiki/docs/ModuleApi/)

## Real World Usage
None yet! Contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) and tell me about where you are using Pepperminty Wiki and I'll add you to this section :smiley_cat:

## Todo
Here's a list of things that I want to add at some point (please feel free to [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) and help out!).

 - Better page history (revert to revision, compare revisions, etc.) (#78, #79)
 - Intelligent auto-updating system that doesn't wipe your settings / module choices
 - A module registry of some sort (ideas welcome!)
 - Image maps (#103)
 - An app for Android (Sorry, iOS is not practical at the current time. Feel free to make one yourself! I'm happy to help out if you need help with Pepperminty Wiki itself (e.g. making it more machine-readable (#138)) - message on Gitter (see above), or open an issue on this repository.) - in progress over [here](https://github.com/sbrl/Pepperminty-Wiki-Client-Android/)!
 - User watchlists
 - Theme gallery (#5)
 - (See more on the [issue tracker](https://github.com/sbrl/Pepperminty-Wiki/issues)!)
 - ...?

Is the feature you want to see not on this list or not implemented yet? [Open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) or [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) - contributions welcome!

# License
Pepperminty Wiki is released under the Mozilla Public License 2.0. The full license text is included in the `LICENSE` file in this repository.
