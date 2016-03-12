Module API Documentation
========================
The core of Pepperminty Wik exposes several global objects and functions that you can use to write your own modules. This page documents these objects and functions so that you can create your own modules more easily.

Indexes
-------
Pepperminty Wiki maintains several indexes containing various information about the current site that you can utilise. Some of them also have an 'API' of sorts that you can use to interact with them.

### `pageindex.json`
This is by _far_ the most important index. It contains an entry for each page, under which a number of interesting pieces of information are stored. It's automatically loaded into the global variable `$pageindex` too, so you don't even have to read it in. Here's an example pageindex:

```json
{
    "Internal link": {
        "filename": "Internal link.md",
        "size": 120,
        "lastmodified": 1446019377,
        "lasteditor": "admin",
        "tags": [
            "testing",
            "test tag with spaces",
            "really really really really really really long tag"
        ]
    },
    "Main Page": {
        "filename": "Main Page.md",
        "size": 151,
        "lastmodified": 1446388276,
        "lasteditor": "admin",
        "tags": []
    },
    "Internal link\/Sub": {
        "filename": "Internal link\/Sub.md",
        "size": 35,
        "lastmodified": 1446370194,
        "lasteditor": "admin",
        "tags": [
            "test"
        ]
    },
    "Files\/AJ Scr.png": {
        "filename": "Files\/AJ Scr.png.md",
        "size": 29,
        "lastmodified": 1445501914,
        "lasteditor": "admin",
        "uploadedfile": true,
        "uploadedfilepath": "Files\/AJ Scr.png",
        "uploadedfilemime": "image\/png"
    }
}
```

Currently, Pepperminty Wiki is configured to pretty print the json in the pageindex when saving it to disk, so if you find yourself saving the pageindex please do the same.

Note that in the future, when alternate data storage directories are supported, the `$entry->filename` will *not* contain the `$env->storage_prefix` prefix. You will need to add this manually if you use it.

### `idindex.json`
The id index converts page ids into page names and vice versa. It's loaded into the global variable `$idindex`, but you normally wouldn't need to touch that, as there's a seamless API that you can use instead:

#### `ids::getid($pagename)`
Gets the id associated with the given pagename. If it doesn'texist it will be created.

#### `ids::getpagename($id)`
Gets the page name associated with the given id. If it doesn't exist `false` will be returned.

Functions
---------

### `register_module($module_info)`
Register a new module with Pepperminty Wiki. This is the most important function. Here's an example:

```php
<?php
register_module([
	"name" => "Human readable module name",		// The name of your module, will be shown to users
	"version" => "0.1",							// The version number
	"author" => "Author Name",					// Your name
	"description" => "Module Description",		// A description of your module. Shown in the module downloader.
	"id" => "module-id",						// An id for your module name. Should be filename safe with no spaces or capital letters.
	"code" => function() {
		// Insert your module's code here
	}
]);

?>

```

The function that you provide will be executed after the initial setup has been completed.

### `module_exists($id)`
Checks to see if a module with the given id is currently loaded. Very useful for providing optional integration with other modules. Note that this may return false if all the modules haven't been loaded yet. All the modules are guaranteed to be loaded by the time the code in the `"code"` function is executed.

```php
<?php
module_exists("feature-search"); // Returns true if the feature-search module is loaded, false otherwise.
```

### `add_action($action_name, $code)`
Since Pepperminty Wiki works through actions via the GET parameter `action`, there is a function that lets you register a new handler for any given action. Note that you should call this inside the function you passed to `register_handler()`. Here's an example:

```php
<?php
register_module([
	"name" => "Human readable module name",
	"version" => "0.1",
	"author" => "Author Name",
	"description" => "Module Description",
	"id" => "module-id",
	"code" => function() {
		add_action("action_name", function() {
			exit("Hello, World!");
		});
	}
]);

?>

```

The above adds an action called `action_name`, which, when requested, outputs the text `Hello, World!`.

### `register_save_preprocessor($function)`
Registers a function to be called every time a page is edited. The function will be passed the following parameters:

1. A reference to the pageindex entry that is about to be saved.
2. The new text that is to replace the old text.
3. The old text that the new text is going to replace.

If you make the function that you pass here take the new text that is to be saved in as a reference, you may alter it before it is saved to disk.

### `page_renderer`
You probably want your module to output a nice user-friendly page instead of a simple text-based one. Luckily, Pepperminty Wiki has a system to let you do that.

#### `page_renderer::render_main($title, $content)`
This is the main page rendering function you'll want to use. It renders and returns a page much the same as the default `view` action does. Here's an example:

```php
<?php

exit(page_renderer::render_main("Page title", "Page content"));

?>

```


#### `page_renderer::render_minimal($title, $content)`
Similar to the above, but renders a printable page instead. For an example, click the "Printable" button at the top of any page on Pepperminty Wiki.

```php
<?php

exit(page_renderer::render_minimal("Page title", "Page content"));

?>

```

#### `page_renderer::render_username($name)`
Renders a username. Currently all this function does is prepend `$settings->admin_display_char` to the username if they are an admin. Example:

```php
<?php

exit(page_renderer::render_username("admin")); // Output would be something like "&#9670;admin".

?>
```

#### `page_renderer::register_part_preprocessor($code)`
This function's use is more complicated to explain. Pepperminty Wiki renders pages with a very simple templating system. For example, in the template a page's content is denoted by `{content}`. A function registered here will be passed all the components of a page _just_ before they are dropped into the template. Note that the function you pass in here should take a *reference* to the components, as the return value of the function passed is discarded. Here's an example:

```php
<?php
register_module([
	"name" => "Content shuffler",
	"version" => "0.1",
	"author" => "Bob",
	"description" => "Shuffles the content of a page randomly.",
	"id" => "extra-content-shuffler",
	"code" => function() {
		page_renderer::register_part_preprocessor(function(&$parts) {
			$parts["{body}"] = str_shuffle($parts["{body}"]);
		});
	}
]);

?>

```

Currently, the following parts are used in the templating process:

Key							| Purpose
----------------------------|------------------
`{body}`					| Holds the main body of the page.
`{sitename}`				| The name of the current installation of Pepperminty Wiki
`{favicon-url}`				| The url of the favicon.
`{header-html}`				| The extra HTML that will be added to the `<head />` tag.
`{navigation-bar}`			| The navigation bar's HTML.
`{navigation-bar-bottom}`	| The bottom navigation bar's HTML.
`{admin-details-name}`		| The name of the administrator.
`{admin-details-email}`		| The email address of the administrator.
`{admins-name-list}`		| The list of moderator's (user)names.
`{generation-date}`			| The date at which the page was generated.
`{all-pages-datalist}`		| The `<datalist />` tag that powers the search bar.

[Take a look at the code](https://github.com/sbrl/Pepperminty-Wiki/blob/master/core.php#L394) to see the very latest list of parts.

### `add_parser($code)`
This function adds a parser to Pepperminty Wiki. An example follows below, but please note that this will ~~probably~~ be changing soon so that the parser's name is attached to it when it is registered. This is so that the user can choose which of the registered parsers are used at any one time.

```php
<?php
register_module([
	"name" => "Reverse parser",
	"version" => "0.1",
	"author" => "Bob",
	"description" => "A parser that reverse the page's source.",
	"id" => "parser-reverse",
	"code" => function() {
		add_parser(function($source) {
			return strrev($source);
		});
	}
]);

?>

```

Variables
---------
There are a number of global variables floatign around that can give you a lot of information about the current request. ~~I will be tidying them up into a single `$env` object soon.~~ Most of the below have been tidied up into a single `$env` object now! Below is a table of all the variables Pepperminty Wiki has lying around:

Variable				| Description
------------------------|------------------------------------------
`$env`					| An object that contains a _bunch_ of useful information about the current request.
`$env->page`			| The current page name.
`$env->is_logged_in`	| Whether the current user is currently logged in.
`$env->is_admin`		| Whether the current user is an administrator.
`$env->user`			| The current user's name. Currently only set if the user is logged in.
`$env->action`			| The current action.
`$settings`				| The settings object from the top of the file.
`$pageindex`			| Contains a list of all the pages that Pepperminty Wiki currently knows about, along with information about each page. Exists to improve performance.
