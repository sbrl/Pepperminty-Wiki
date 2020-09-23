<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */



/*
 * ███    ███  ██████  ██████  ██    ██ ██      ███████ ███████
 * ████  ████ ██    ██ ██   ██ ██    ██ ██      ██      ██
 * ██ ████ ██ ██    ██ ██   ██ ██    ██ ██      █████   ███████
 * ██  ██  ██ ██    ██ ██   ██ ██    ██ ██      ██           ██
 * ██      ██  ██████  ██████   ██████  ███████ ███████ ███████
 */

/** A list of all the currently loaded modules. Not guaranteed to be populated until an action is executed. @var array */
$modules = [];

/**
 * Registers a module.
 * @package core
 * @param  array	$moduledata	The module data to register.
 */
function register_module($moduledata)
{
	global $modules;
	//echo("registering module\n");
	//var_dump($moduledata);
	$modules[] = $moduledata;
}

/**
 * Checks to see whether a module with the given id exists.
 * @package core
 * @param  string   $id	 The id to search for.
 * @return bool     Whether a module is currently loaded with the given id.
 */
function module_exists($id)
{
	global $modules;
	foreach($modules as $module)
	{
		if($module["id"] == $id)
			return true;
	}
	return false;
}


/*
 *  █████   ██████ ████████ ██  ██████  ███    ██ ███████
 * ██   ██ ██         ██    ██ ██    ██ ████   ██ ██
 * ███████ ██         ██    ██ ██    ██ ██ ██  ██ ███████
 * ██   ██ ██         ██    ██ ██    ██ ██  ██ ██      ██
 * ██   ██  ██████    ██    ██  ██████  ██   ████ ███████
 */

$actions = new stdClass();

/**
 * Registers a new action handler.
 * @package core
 * @param	string		$action_name	The action to register.
 * @param	callable	$func			The function to call when the specified
 * 										action is requested.
 */
function add_action($action_name, $func)
{
	global $actions;
	$actions->$action_name = $func;
}

/**
 * Figures out whether a given action is currently registered.
 * Only guaranteed to be accurate in inside an existing action function
 * @package	core
 * @param	string	$action_name	The name of the action to search for
 * @return	bool		Whether an action with the specified name exists.
 */
function has_action($action_name)
{
	global $actions;
	return !empty($actions->$action_name);
}


/*
 * ███████  █████  ██    ██ ██ ███    ██  ██████
 * ██      ██   ██ ██    ██ ██ ████   ██ ██
 * ███████ ███████ ██    ██ ██ ██ ██  ██ ██   ███
 *      ██ ██   ██  ██  ██  ██ ██  ██ ██ ██    ██
 * ███████ ██   ██   ████   ██ ██   ████  ██████
*/

$save_preprocessors = [];

/**
 * Register a new proprocessor that will be executed just before
 * an edit is saved.
 * @package core
 * @param	callable	$func	The function to register.
 */
function register_save_preprocessor($func)
{
	global $save_preprocessors;
	$save_preprocessors[] = $func;
}


/*
 * ██   ██ ███████ ██      ██████
 * ██   ██ ██      ██      ██   ██
 * ███████ █████   ██      ██████
 * ██   ██ ██      ██      ██
 * ██   ██ ███████ ███████ ██
 */

$help_sections = [];

/**
 * Adds a new help section to the help page.
 * @package core
 * @param string $index   The string to index the new section under.
 * @param string $title   The title to display above the section.
 * @param string $content The content to display.
 */
function add_help_section($index, $title, $content)
{
	global $help_sections;
	
	$help_sections[$index] = [
		"title" => $title,
		"content" => $content
	];
}

if(!empty($settings->enable_math_rendering))
	add_help_section("22-mathematical-mxpressions", "Mathematical Expressions", "<p>$settings->sitename supports rendering of mathematical expressions. Mathematical expressions can be included practically anywhere in your page. Expressions should be written in LaTeX and enclosed in dollar signs like this: <code>&#36;x^2&#36;</code>.</p>
	<p>Note that expression parsing is done on the viewer's computer with javascript (specifically MathJax) and not by $settings->sitename directly (also called client side rendering).</p>");


/*
 * ███████ ████████  █████  ████████ ███████
 * ██         ██    ██   ██    ██    ██
 * ███████    ██    ███████    ██    ███████
 *      ██    ██    ██   ██    ██         ██
 * ███████    ██    ██   ██    ██    ███████
*/

/** An array of the currently registerd statistic calculators. Not guaranteed to be populated until the requested action function is called. */
$statistic_calculators = [];

/**
 * Registers a statistic calculator against the system.
 * @package core
 * @param	array	$stat_data	The statistic object to register.
 */
function statistic_add($stat_data) {
	global $statistic_calculators;
	$statistic_calculators[$stat_data["id"]] = $stat_data;
}

/**
 * Checks whether a specified statistic has been registered.
 * @package	core
 * @param	string	$stat_id	The id of the statistic to check the existence of.
 * @return	bool		Whether the specified statistic has been registered.
 */
function has_statistic($stat_id) {
	global $statistic_calculators;
	return !empty($statistic_calculators[$stat_id]);
}
