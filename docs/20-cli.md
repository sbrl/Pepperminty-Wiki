# Command-Line Interface
Since v0.21, Pepperminty Wiki now has a command-line interface. This can be used for a number of maintenance tasks - including (but not limited to) updating the search index and the didyoumean typo correction engine index (the latter of which can currently _only_ be updated through the CLI, but as it stabilises this should change).

To use it, you should be comfortable with the terminal (or command-line). If you do not intend to use it or don't have command-line access to your web server, you should disable it by unchecking the `cli_enabled` setting in the master control settings, or setting it to `false` in `peppermint.json`.

First, `cd` to be next to the `index.php` file of your wiki (if you have multiple wikis with symlinks to `index.php` - which is fully supported by the way - then you should `cd` to be next to the symlink of the target wiki`):

```bash
cd path/to/directory
```

Now you can interact with Pepperminty Wiki's CLI. Display the basic help text like so:

```bash
php index.php
```

This will tell you about a number of different subcommands, but the 2 you'll probably be interested in are `shell` and `exec`. The `shell` command is the one you'll want first. Use it like this:

```bash
php index.php shell
```

This takes you to the Pepperminty Wiki shell. Type `help` here and hit enter to show the list of shell commands supported. To try out a command, simply type it and hit enter to display usage information. For example, type `search` to display the available `search subcommands`.

A subcommand of the `search` command is `rebuild`, which rebuilds the search index fromt he command line. You can call it like so:

```
search rebuild
```

After every command has executed, the following will be displayed:

```
<<<< 0 <<<<
```

This is an indicator of the exit code of the last command executed. The exit code functions the same as you  may  be familiar with in your favourite regular shell, such as bash or zsh. An exit code of 0 indicates success.

The reason for this is the `exec` subcommand of main `index.php`. Once you are back in your regular shell terminal (type `exit` to exit the Pepperminty Wiki shell), type this:

```bash
php index.php exec help
```

The `exec` command can be used to call commands from the Pepperminty Wiki shell directly in from your main shell (e.g. bash, zsh, fish, etc.). As another example, here's how you'd rebuild the search index with the `exec` subcommand:

```bash
php index.php exec search rebuild
```
