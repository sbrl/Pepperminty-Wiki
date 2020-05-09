# Getting a Copy
Setting up your own copy of Pepperminty Wiki is easy. Since Pepperminty Wiki works on a module based system, all you need to do is choose the modules you want installed, and then configure your new installation so that it fits your needs. There are several ways to do this:

## Method 1: The latest pre-built stable release
If you want a pre-built stable version, then you can use the latest release. It has a changelog that tells you what has changed since the last release, along with a pre-built version with all the latest modules.

**Link:** [Latest Release](https://github.com/sbrl/Pepperminty-Wiki/releases/latest)

## Method 2: Using the online downloader
Pepperminty Wiki has a downloader that you can use to select the modules you want to include in your install. The online downloader will give you the latest stable release. You can find it here.

**Link:** [Online Downloader](https://starbeamrainbowlabs.com/labs/peppermint/download.php)

## Method 2.5: Using the downloader offline
You can also you the downloader offline. Simply clone this repository to your web server (or clone it locally and then upload):

```bash
git clone https://github.com/sbrl/Pepperminty-Wiki.git
```

Then, point your web browser at `your.server.com/path/to/pepperminty/wiki/download.php`.

## Method 3: Building from source
Pepperminty Wiki can also be built from source (and I do this all the time when testing). Start by cloning the repository:

```bash
git clone https://github.com/sbrl/Pepperminty-Wiki.git
```

Then go into the `modules` folder and append `.disabled` to the names of any modules you don't want to be included (e.g. `modules/page-edit.php` would become `modules/page-edit.php.disabled`). Then follow the instructions below. The resulting file will be located at `build/index.php`.

Run the following commands from the root of the repository in order, adjusting them for your specific platform if required:

```bash
rm build/index.php
php build.php
```

These commands are also in `build.sh`. If you have bash installed (i.e. Linux and macOS users), you can run that instead like this:

```bash
./build.sh build
```

The extra `build` is because the build script can do other things. Omit the `build` for a full list of tricks it has up its sleeve :D

Here's an explanation of what each command does:

1. Deletes the old `index.php` in the build folder that comes with the repository
2. Rebuilds the module index that the build scripts uses to determine what modules it should include when building, and then actually builds Pepperminty Wiki. Outputs to `index.php`.

Additional installation methods are being thought about. If there's a specific method you don't see here that you think should be here - let me know by [opening an issue](https://github.com/sbrl/Pepperminty-Wiki/issues/new)!
