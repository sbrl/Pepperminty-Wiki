<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Statistics",
	"version" => "0.4.5",
	"author" => "Starbeamrainbowlabs",
	"description" => "An extensible statistics calculation system. Comes with a range of built-in statistics, but can be extended by other modules too.",
	"id" => "feature-stats",
	"code" => function() {
		global $settings, $env;
		
		/**
		 * @api {get} ?action=stats Show wiki statistics
		 * @apiName Stats
		 * @apiGroup Utility
		 * @apiPermission Anonymous
		 * @apiVersion 0.15.0
		 * @apiParam	{string}	format	Specify the format the data should be returned in. Supported formats: html (default), json.
		 * @apiParam	{string}	stat	HTML format only. If specified the page for the stat with this id is sent instead of the list of scalar stats.
		 */
		
		/*
		 * ███████ ████████  █████  ████████ ███████
		 * ██         ██    ██   ██    ██    ██
		 * ███████    ██    ███████    ██    ███████
		 *      ██    ██    ██   ██    ██         ██
		 * ███████    ██    ██   ██    ██    ███████
		 */
		add_action("stats", function() {
			global $settings, $statistic_calculators;
			
			$allowed_formats = [ "html", "json" ];
			$format = slugify($_GET["format"] ?? "html");
			
			if(!in_array($format, $allowed_formats)) {
				http_response_code(400);
				exit(page_renderer::render_main("Format error - $settings->sitename", "<p>Error: The format '$format' is not currently supported by this action on $settings->sitename. Supported formats: " . implode(", ", $allowed_formats) . "."));
			}
			
			$stats = stats_load();
			
			if($format == "json") {
				header("content-type: application/json");
				exit(json_encode($stats, JSON_PRETTY_PRINT));
			}
			
			$stat_pages_list = "<a href='?action=stats'>Main</a> | ";
			foreach($statistic_calculators as $stat_id => $stat_calculator) {
				if($stat_calculator["type"] == "scalar")
					continue;
				$stat_pages_list .= "<a href='?action=stats&stat=" . rawurlencode($stat_id) . "'>{$stat_calculator["name"]}</a> | ";
			}
			$stat_pages_list = trim($stat_pages_list, " |");
			
			if(!empty($_GET["stat"]) && !empty($statistic_calculators[$_GET["stat"]])) {
				$stat_calculator = $statistic_calculators[$_GET["stat"]];
				$content = "<h1>{$stat_calculator["name"]} - Statistics</h1>\n";
				$content .= "<p>$stat_pages_list</p>\n";
				switch($stat_calculator["type"]) {
					case "page-list":
						if(!module_exists("page-list")) {
							$content .= "<p>$settings->sitename doesn't current have the page listing module installed, so HTML rendering of this statistic is currently unavailable. Try " . hide_email($settings->admindetails_email, "contacting ".htmlentities($settings->admindetails_name)) . ", $settings->sitename's administrator and asking then to install the <code>page-list</code> module.</p>";
							break;
						}
						$content .= "<p><strong>Count:</strong> " . count($stats->{$_GET["stat"]}->value) . "</p>\n";
						$content .= generate_page_list($stats->{$_GET["stat"]}->value);
						break;
					
					case "page":
						$content .= $stat_calculator["render"]($stats->{$_GET["stat"]});
						break;
				}
			}
			else
			{
				$content = "<h1>Statistics</h1>\n";
				$content .= "<p>This page contains a selection of statistics about $settings->sitename's content. They are updated automatically about every " . trim(str_replace(["ago", "1 "], [""], human_time($settings->stats_update_interval))) . ", although $settings->sitename's local friendly moderators may update them earlier (you can see their names at the bottom of every page).</p>\n";
				$content .= "<p>$stat_pages_list</p>\n";
				
				$content .= "<table class='stats-table'>\n";
				$content .= "\t<tr><th>Statistic</th><th>Value</th></tr>\n\n";
				foreach($statistic_calculators as $stat_id => $stat_calculator) {
					if($stat_calculator["type"] !== "scalar")
						continue;
					
					$content .= "\t<tr><td>{$stat_calculator["name"]}</td><td>{$stats->$stat_id->value}</td></tr>\n";
				}
				$content .= "</table>\n";
			}
			
			exit(page_renderer::render_main("Statistics - $settings->sitename", $content));
		});
		
		/**
		 * @api {get|post} ?action=stats-update Recalculate the wiki's statistics
		 * @apiName UpdateStats
		 * @apiGroup Utility
		 * @apiPermission Administrator
		 * @apiVersion 0.15.0
		 * @apiParam	{string}	secret	POST only, optional. If you're not logged in, you can specify the wiki's sekret instead (find it in peppermint.json) using this parameter.
		 * @apiParam	{bool}		force	Whether the statistics should be recalculated anyway - even if they have already recently been recalculated. Default: no. Supported values: yes, no.
		 */
		
		/*
		 * ███████ ████████  █████  ████████ ███████
		 * ██         ██    ██   ██    ██    ██
		 * ███████    ██    ███████    ██    ███████
		 *      ██    ██    ██   ██    ██         ██
		 * ███████    ██    ██   ██    ██    ███████
		 * 
		 * ██    ██ ██████  ██████   █████  ████████ ███████
		 * ██    ██ ██   ██ ██   ██ ██   ██    ██    ██
		 * ██    ██ ██████  ██   ██ ███████    ██    █████
		 * ██    ██ ██      ██   ██ ██   ██    ██    ██
		 *  ██████  ██      ██████  ██   ██    ██    ███████
		 */
		add_action("stats-update", function() {
			global $env, $paths, $settings;
			
			
			if(!$env->is_admin &&
				(
					empty($_POST["secret"]) ||
					$_POST["secret"] !== $settings->secret
				)
			)
				exit(page_renderer::render_main("Error - Recalculating Statistics - $settings->sitename", "<p>You need to be logged in as a moderator or better to get $settings->sitename to recalculate it's statistics. If you're logged in, try <a href='?action=logout'>logging out</a> and logging in again as a moderator. If you aren't logged in, try <a href='?action=login&returnto=%3Faction%3Dstats-update'>logging in</a>.</p>"));
			
			// Delete the old stats cache
			if(file_exists($paths->statsindex))
				unlink($paths->statsindex);
			
			update_statistics(true, ($_GET["force"] ?? "no") == "yes");
			header("content-type: application/json");
			echo(file_get_contents($paths->statsindex) . "\n");
		});
		
		add_help_section("150-statistics", "Statistics", "<p>$settings->sitename records some statistics about itself, including the number of pages, the longest pages, the most wanted pages, the most linked-to pages, and more. They are updated roughly every " . human_time($settings->stats_update_interval) . ", though moderators may occasionally update them sooner.</p>
		<p>You can see these statistics <a href='?action=stats'>here</a>.</p>");
		
		//////////////////////////
		/// Built-in Statisics ///
		//////////////////////////
		

		statistic_add([
			"id" => "user_count",
			"name" => "Users",
			"type" => "scalar",
			"update" => function($old_stats) {
				global $settings;
				
				$result = new stdClass(); // completed, value, state
				$result->completed = true;
				$result->value = count(get_object_vars($settings->users));
				return $result;
			}
		]);
		
		statistic_add([
			"id" => "longest-pages",
			"name" => "Longest Pages",
			"type" => "page-list",
			"update" => function($old_stats) {
				global $pageindex;
				
				$result = new stdClass(); // completed, value, state
				$pages = [];
				foreach($pageindex as $pagename => $pagedata) {
					$pages[$pagename] = $pagedata->size;
				}
				arsort($pages);
				
				$result->value = array_keys($pages);
				$result->completed = true;
				return $result;
			}
		]);

		statistic_add([
			"id" => "page_count",
			"name" => "Page Count",
			"type" => "scalar",
			"update" => function($old_stats) {
				global $pageindex;
				
				$result = new stdClass(); // completed, value, state
				$result->completed = true;
				$result->value = count(get_object_vars($pageindex));
				return $result;
			}
		]);

		statistic_add([
			"id" => "file_count",
			"name" => "File Count",
			"type" => "scalar",
			"update" => function($old_stats) {
				global $pageindex;
				
				$result = new stdClass(); // completed, value, state
				$result->completed = true;
				$result->value = 0;
				foreach($pageindex as $pagename => $pagedata) {
					if(!empty($pagedata->uploadedfile) && $pagedata->uploadedfile)
						$result->value++;
				}
				return $result;
			}
		]);

		statistic_add([
			"id" => "redirect_count",
			"name" => "Redirect Pages",
			"type" => "scalar",
			"update" => function($old_stats) {
				global $pageindex;
				
				$result = new stdClass(); // completed, value, state
				$result->completed = true;
				$result->value = 0;
				foreach($pageindex as $pagename => $pagedata) {
					if(!empty($pagedata->redirect) && $pagedata->redirect)
						$result->value++;
				}
				return $result;
			}
		]);
		
		// Perform an automatic recalculation of the statistics if needed, but only if we're not on the CLI
		if($env->action !== "stats-update" && !is_cli())
			update_statistics(false);
		
		
		/*
		 *  ██████ ██      ██
		 * ██      ██      ██
		 * ██      ██      ██
		 * ██      ██      ██
		 *  ██████ ███████ ██
		 */
		if(module_exists("feature-cli")) {
			cli_register("stats", "Interact with and update the wiki statistics", function(array $args) : int {
				global $settings, $env;
				if(count($args) < 1) {
					echo("stats: interact with an manipulate the wiki statistics
Usage:
	stats {subcommand}

Subcommands:
	recalculate     Recalculates the statistics
	show            Shows the current statistics
");
					return 0;
				}
				
				switch($args[0]) {
					case "recalculate":
						echo("Updating statistics - ");
						$start_time = microtime(true);
						update_statistics(true, true);
						echo("done in ".round((microtime(true) - $start_time) * 1000, 2)."ms\n");
						echo("Recalculated {$env->perfdata->stats_recalcuated} statistics in {$env->perfdata->stats_calctime}ms (not including serialisation / saving to disk)\n");
						break;
					case "show":
						$stats = stats_load();
						foreach($stats as $name => $stat) {
							$lastupdated = render_timestamp($stat->lastupdated, true, false);
							if(is_object($stat->value)) {
								echo("*** $stat->name *** (last updated $lastupdated)\n");
								$i = 0;
								foreach($stat->value as $key => $value) {
									if($i >= 25) break;
									echo("$key: $value\n");
									$i++;
								}
							}
							else if(is_array($stat->value)) {
								// Display array differently, and truncate to 25 entries
								echo("*** $stat->name *** (last updated $lastupdated)\n");
								echo(implode("\n", array_slice($stat->value, 0, 25)));
								echo("\n");
							}
							else
								echo("$stat->name: ".var_export($stat->value, true)." (last updated $lastupdated)\n");
							
							echo("\n");
						}
						break;
				}
				return 0;
			});
		}
	}
]);

/**
 * Updates the wiki's statistics.
 * @package feature-stats
 * @param  bool $update_all Whether all the statistics should be checked and recalculated, or just as many as we have time for according to the settings.
 * @param  bool $force      Whether we should recalculate statistics that don't currently require recalculating anyway.
 */
function update_statistics($update_all = false, $force = false)
{
	global $settings, $env, $paths, $statistic_calculators;
	
	// If the firstrun wizard isn't complete, then there's no point in updating the statistics index
	if(isset($settings->firstrun_complete) && $settings->firstrun_complete == false)
		return;
	
	$stats_mtime = file_exists($paths->statsindex) ? filemtime($paths->statsindex) : 0;
	
	// Clear the existing statistics if we are asked to recalculate them all
	if($force)
		stats_save(new stdClass());
	// If the stats index exists and has been modified recently, then don't 
	// even bother to load it
	// This is an important optimisation, because json_decode is *slow*
	else if(file_exists($paths->statsindex) && time() - $stats_mtime < $settings->stats_update_interval)
		return;
	
	$stats = stats_load();
	
	$start_time = microtime(true);
	$ran_out_of_time = false;
	$stats_updated = 0;
	foreach($statistic_calculators as $stat_id => $stat_calculator)
	{
		// If statistic doesn't exist or it's out of date then we should recalculate it.
		// Otherwise, leave it and continue on to the next stat.
		if(!empty($stats->$stat_id) && $start_time - $stats->$stat_id->lastupdated < $settings->stats_update_interval)
			continue;
		
		$mod_start_time = microtime(true);
		
		// Run the statistic calculator, passing in the existing stats data
		$calculated = $stat_calculator["update"](!empty($stats->$stat_id) ? $stats->$stat_id : new stdClass());
		
		$new_stat_data = new stdClass();
		$new_stat_data->id = $stat_id;
		$new_stat_data->name = $stat_calculator["name"];
		$new_stat_data->lastupdated = $calculated->completed ? $mod_start_time : $stats->$stat_id->lastupdated;
		$new_stat_data->value = $calculated->value;
		if(!empty($calculated->state))
			$new_stat_data->state = $calculated->state;
		
		// Save the new statistics
		$stats->$stat_id = $new_stat_data;
		
		$stats_updated++;
		
		// Check to make sure we haven't run out of time to update the statistics this session
		if(!$update_all && microtime(true) - $start_time >= $settings->stats_update_processingtime) {
			$ran_out_of_time = true;
			break;
		}
	}
	
	$env->perfdata->stats_recalcuated = $stats_updated;
	$env->perfdata->stats_calctime = round((microtime(true) - $start_time)*1000, 3);
	
	if(!is_cli()) {
		header("x-stats-recalculated: {$env->perfdata->stats_recalcuated}");
		//round((microtime(true) - $pageindex_read_start)*1000, 3)
		header("x-stats-calctime: {$env->perfdata->stats_calctime}ms");
	}
	
	stats_save($stats);
	// If we ran out of time, reset the mtime for performance reasons (see the 
	// beginning of this function)
	if($ran_out_of_time) 
		touch($paths->statsindex, $stats_mtime);
}

/**
 * Loads and returns the statistics cache file.
 * @package	feature-stats
 * @return	object		The loaded & decoded statistics.
 */
function stats_load()
{
	global $paths;
	static $stats = null;
	if($stats == null)
		$stats = file_exists($paths->statsindex) ? json_decode(file_get_contents($paths->statsindex)) : new stdClass();
	return $stats;
}
/**
 * Saves the statistics back to disk.
 * @package	feature-stats
 * @param	object	The statistics cache to save.
 * @return	bool	Whether saving succeeded or not.
 */
function stats_save($stats)
{
	global $paths;
	echo("STATS_SAVE BEGIN, CONTENT_DUMP:\n");
	var_dump($stats);
	echo("\nCONTENT_JSON:\n");
	var_dump(json_encode($stats, JSON_PRETTY_PRINT, 10));
	echo("\nLAST_ERROR: ".json_last_error()."\n");
	echo("\nSTATS_SAVE END to $paths->statsindex\n");
	return file_put_contents($paths->statsindex, json_encode($stats, JSON_PRETTY_PRINT) . "\n");
}
