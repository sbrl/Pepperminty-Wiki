# ![Pepperminty Wiki Logo](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/logo.png) Pepperminty Wiki [![Build Status](https://travis-ci.org/sbrl/Pepperminty-Wiki.svg?branch=master)](https://travis-ci.org/sbrl/Pepperminty-Wiki) [![Join the chat at https://gitter.im/Pepperminty-Wiki/Lobby](https://badges.gitter.im/Pepperminty-Wiki/Lobby.svg)](https://gitter.im/Pepperminty-Wiki/Lobby?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![License: MPL-2.0](https://img.shields.io/badge/License-MPL--2.0-blue.svg)](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/LICENSE)

> A Wiki in a box

Pepperminty Wiki is a complete wiki contained in a single file, inspired by @am2064's [Minty Wiki](https://github.com/am2064/Minty-Wiki). It's open source too (under MPL-2.0), so contributions are welcome!

Developed by Starbeamrainbowlabs (though contributions from others are welcome!), Pepperminty Wiki has a variety of useful (and cool!) features - such as file upload, a dynamic help page, page revision history, page tags, and more! Other amazing features are in the works too (like a theme gallery, autoupdate, and user watchlists), so check the release notes to see what's been added recently.

**Latest Version:** v0.15.1 (stable) v0.16-dev (development) ([Changelog](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Changelog.md))

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
 - [API Reference](#api-reference)
 - [Real World Usage](#real-world-usage)
 - [Todo and Contributing](#todo)
 - [License](#license)

## Screenshots and Demo
![Main Page Example](https://i.imgur.com/5dmbKlz.png)

Above: The Main Page used for testing purposes.

A live demo of the latest stable version can be found over at [my website](//starbeamrainbowlabs.com/labs/peppermint)

## Features
- Configurable settings
- User login system
- Page creation (Sub pages supported)
- Markdown-powered syntax
	- Templating support
	- Additional syntax for resizing and floating images (see inbuilt help page)
	- File galleries
	- Short syntax for referencing uploaded files
	- Client side mathematical expression parsing, courtesy of [MathJax](https://www.mathjax.org/)
	- Links to non-existent pages appear red
- Full page revision history (comparison / manipulation coming soon)
- Optional time-delayed search indexing
- Simple edit conflict detection
- Edit previewing (since v0.14, thanks to @ikisler)
- Internal links - Links to non-existent pages show up in red
- Printable page view
- Customisable theme
- ~~Basic 'search' bar~~ A full text search engine!
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

Please note that configuration of Pepperminty Wiki will be done through a GUI soon.

The [configuration guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php) are all the configuration directives that Pepperminty Wiki (and all the modules included in the repository) understand. It is generated automatically from `peppermint.guiconfig.json`.

 - [Configuration Guide](https://starbeamrainbowlabs.com/labs/peppermint/peppermint-config-info.php)
 - [`peppermint.guiconfig.json`](https://github.com/sbrl/Pepperminty-Wiki/blob/master/peppermint.guiconfig.json))

## API Reference
I have documented the current API and other things that make Pepperminty Wiki tick that you can use to create your own modules. You can find this documentation in the [Module_API_Docs.md](https://github.com/sbrl/Pepperminty-Wiki/blob/master/Module_API_Docs.md) file in this repository.

I've also documented Pepperminty Wiki's entire REST API using [apiDoc](http://apidocjs.com/). You can view the docs [here](https://sbrl.github.io/Pepperminty-Wiki/docs/RestApi/).

tldr Quick Links:
 - [HTTP REST API](https://sbrl.github.io/Pepperminty-Wiki/docs/RestApi/)
 - [PHP Module API](https://sbrl.github.io/Pepperminty-Wiki/docs/ModuleApi/)

If you do create a module, I'd love to hear about it. Even better, [send a pull request](https://github.com/sbrl/Pepperminty-Wiki/pulls/new)!

## Real World Usage
None yet! Contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) and tell me about where you are using Pepperminty Wiki and I'll add you to this section :smiley_cat:

## Todo
Here's a list of things that I want to add at some point (please feel free to [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) and help out!).

 - Better page history (revert to revision, compare revisions, etc.) (#78, #79)
 - Intelligent auto-updating system that doesn't wipe your settings / module choices
 - A module registry of some sort (ideas welcome!)
 - Image maps (#103)
 - User management for moderators ( + moderator management for the wiki owner) (#127)
 - An app for Android (Sorry, iOS is not practical at the current time. Feel free to make one yourself! I'm happy to help out if you need help with Pepperminty Wiki itself (e.g. making it more machine-readable (#138)) - message on Gitter (see above), or open an issue on this repository.) - in progress over [here](https://github.com/sbrl/Pepperminty-Wiki-Client-Android/)!
 - User watchlists
 - Theme gallery (#5)
 - (See more on the [issue tracker](https://github.com/sbrl/Pepperminty-Wiki/issues)!)
 - ...?

Is the feature you want to see not on this list or not implemented yet? [Open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) or [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) - contributions welcome!

# License
Pepperminty Wiki is released under the Mozilla Public License 2.0. The full license text is included in the `LICENSE` file in this repository.
