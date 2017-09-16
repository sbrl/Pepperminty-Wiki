# Developer Documentation
The core of Pepperminty Wiki exposes several global objects, classes, functions, and miscellaneous files that you can use to write your own modules. This page documents these them so that you can create your own modules more easily.

## Table of Contents
 - [Rest API](#rest-api)
 - [Module API](#module-api)
     - [Global Variables](#global-variables)
 - [Files](#files)
     - [`pageindex.json`](#pageindexjson)
     - [`idindex.json`](#idindexjson)
     - [`invindex.json`](#invindexjson)
     - [`recent-changes.json`](#recent-changesjson)
     - [`statsindex.json`](#statsindexjson)

## Rest API
The REST api provided by Pepperminty Wiki itself is documented for bot owners and software developers alike over on GitHub pages [here](https://sbrl.github.io/Pepperminty-Wiki/docs/RestApi/).

## Module API
The main PHP-based module API is documented with php documentor. The docs can be found [here](https://sbrl.github.io/Pepperminty-Wiki/docs/ModuleApi/), hosted on GitHub Pages.

This documentation covers all the functions and classes available in both the Pepperminty Wiki core, and the modules stored in this repository - as well as mentioning which module they are a part of.

There are one or two additional things that haven't made their way into the module api docs, which are detailed below:

### `page_renderer::register_part_preprocessor($code)`
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

### Global Variables
There are a number of global variables floating around that can give you a lot of information about the current request. ~~I will be tidying them up into a single `$env` object soon.~~ Most of the below have been tidied up into a single `$env` object now! Below is a table of all the variables Pepperminty Wiki has lying around:

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


## Files
Pepperminty Wiki maintains several files (most of which are indexes) containing various information about the current site that you can utilise. Some of them also have an 'API' of sorts that you can use to interact with them - which is documented in the [module api](#module-api) above.

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

Now that alternate data storage directories are supported, the `$entry->filename` will *not* contain the `$env->storage_prefix` prefix. You will need to add this manually if you use it.

### `idindex.json`
The id index converts page ids into page names and vice versa. It's loaded into the global variable `$idindex`, but you normally wouldn't need to touch that, as there's a seamless API that you can use instead, which can be found under the `ids` class.

### `invindex.json`
This is the main search index. Obviously, it's only present if the `feature-search` module is loaded and active. It can be interacted with though the `search` class that the `feature-search` module exposes.

### `recent-changes.json`
This is not loaded automatically, but it contains a list of recent changes that have occurred throughout the wiki. You don't have to fiddle with it directly though if you just want to add a new change, because the `feature-recent-changes` module has a fewe handy methods you can use for that purpose.

### `statsindex.json`
This file is brand new as of v0.15, and contains the most recently calculated statistics about the wiki. The `feature-stats` module oversees the regeneration of this file. Consult if you need access to such statistics that might be somewhat expensive to calculate.
