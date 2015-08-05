# Pepperminty Wiki
A Wiki in a box

Pepperminty Wiki is a complete wiki contained in a single file, inspired by @am2064's [Minty Wiki](https://github.com/am2064/Minty-Wiki).

## Screenshots
![Main Page Example](https://cloud.githubusercontent.com/assets/9929737/9069904/12acfad6-3ae4-11e5-8ec4-6ec0e3de7249.png)

Above: A Main Page with the sidebar enabled.

## Features
- Configurable settings
- User login system
- Page creation
- Sub pages
- Markdown-powered syntax
- Internal links
- Printable page view
- Customisable theme
- Basic 'search' bar
- (Optional) Sidebar with a tree of all the current pages
- List of all pages & details
- Inbuilt help page

## Demo
A Live demo of the latest stable version can be found over at [my website](//starbeamrainbowlabs.com/labs/peppermint)

## Getting Started
### Requirements
- PHP-enabled webserver
- PHP session support (for logging in, see [here](https://php.net/manual/en/session.installation.php) for more information)
- Write access to own folder (only for editing)

### Getting your own copy
Setting up your own copy of Pepperminty Wiki is easy. Since Pepperminty Wiki works on a module based system, all you need to do is choose the modules you want installed, and then configure your new installation so that it fits your needs. There are several ways to do this:

#### Method 1: Using the latest pre-built stable release
If you want a pre-built stable version, then you can [use the latest release](https://github.com/sbrl/Pepperminty-Wiki/releases/latest). It has a changelog that tells you what has changed since the last release, along with a pre-built version with all the latest modules.

#### Method 2: Grabbing the pre-built verion from the repository
If you're feeling lazy, you  can grab the bleeding-edge version from this respository, which comes with all the latest modules. You can get it [here](https://raw.githubusercontent.com/sbrl/Pepperminty-Wiki/master/build/index.php).

#### Method 2: Using the online downloader
Pepperminty Wiki has a downloader that you can use to select the modules you want to include in your install. The online downloader will give you the latest stable release. You can find it [here](//starbeamrainbowlabs.com/labs/peppermint/download.php).

#### Method 2.5: Using the downloader offline
You can also you the downloader offline. Simply clone this repository to your web server and then point your web browser at `your.server/path/to/perppminty/wiki/download.php`.

#### Method 3: Building your own from source
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

Here's an explanation of what each command does:

1. Deletes the old `index.php` in the build folder that comes with the repository
2. Rebuilds the module index that the build scripts uses to determine what modules it should include when building
3. Actually builds Pepperminty Wiki. Outputs to `index.php`.

### Configuring
To configure your new install, open `index.php` in your favourite text editor and take a look at the comments. They should be self explanatory, but if you need any help, just contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new).

## Reference
At some point (soon I hope!), I am going to write a reference here for those who would like to build their own modules.

## Real World Usage
None yet! Contact me or [open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) and tell me about where you are using Pepperminty Wiki and I will add you to this section!

## Todo
Here's a list of things that I want to add at some point (please feel free to [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls) and help out!).

- Page history
- Password changing
- Intelligent auto-updating system that doesn't wipe your settings / module choices
- Make links to non-existent pages red
- Optional module support
- Redirect pages
- ...?

Is the feature you want to see not on this list? [Open an issue](//github.com/sbrl/Pepperminty-Wiki/issues/new) or [send a pull request](//github.com/sbrl/Pepperminty-Wiki/pulls)!