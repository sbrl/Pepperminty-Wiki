<?php
register_module([
	"name" => "Statistics",
	"version" => "0.2",
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
		 * @since v0.15
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
			$format = $_GET["format"] ?? "html";
			
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
							$content .= "<p>$settings->sitename doesn't current have the page listing module installed, so HTML rendering of this statistic is currently unavailable. Try <a href='mailto:" . hide_email($settings->admindetails_email) . "'>contacting $settings->admindetails_name</a>, $settings->sitename's administrator and asking then to install the <code>page-list</code> module.</p>";
							break;
						}
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
		 * @since v0.15
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
			unlink($paths->statsindex);
			
			update_statistics(true, ($_GET["force"] ?? "no") == "yes");
			header("content-type: application/json");
			echo(file_get_contents($paths->statsindex) . "\n");
		});
		
		add_help_section("150-statistics", "Statistics", "<p></p>");
		
		//////////////////////////
		/// Built-in Statisics ///
		//////////////////////////

		// The longest pages
		statistic_add([
			"id" => "longest-pages",
			"name" => "Longest Pages",
			"type" => "page",
			"update" => function($old_stats) {
				global $pageindex;
				
				$result = new stdClass(); // completed, value, state
				$pages = [];
				foreach($pageindex as $pagename => $pagedata) {
					$pages[$pagename] = $pagedata->size;
				}
				arsort($pages);
				
				$result->value = $pages;
				$result->completed = true;
				return $result;
			},
			"render" => function($stats_data) {
				$result = "<h2>$stats_data->name</h2>\n";
				$result .= "<ol class='stats-list longest-pages-list'>\n";
				$i = 0;
				foreach($stats_data->value as $pagename => $page_length) {
					$result .= "\t<li class='stats-item long-page'>$pagename <em>(" . human_filesize($page_length) . ")</em></li>\n";
					$i++;
				}
				$result .= "</ol>\n";
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
		
		// Perform an automatic recalculation of the statistics if needed
		if($env->action !== "stats-update")
			update_statistics(false);
	}
]);

/**
 * Updates the wiki's statistics.
 * @package feature-stats
 * @param  boolean $update_all Whether all the statistics should be checked and recalculated, or just as many as we have time for according to the settings.
 * @param  boolean $force      Whether we should recalculate statistics that don't currently require recalculating anyway.
 */
function update_statistics($update_all = false, $force = false)
{
	global $settings, $statistic_calculators;
	
	// Clear the existing statistics if we are asked to recalculate them all
	if($force)
		stats_save(new stdClass());
	
	$stats = stats_load();
	
	$start_time = microtime(true);
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
		
		if(!$update_all && microtime(true) - $start_time >= $settings->stats_update_processingtime)
			break;
	}
	
	header("x-stats-recalculated: $stats_updated");
	//round((microtime(true) - $pageindex_read_start)*1000, 3)
	header("x-stats-calctime: " . round((microtime(true) - $start_time)*1000, 3) . "ms");
	
	stats_save($stats);
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
	return file_put_contents($paths->statsindex, json_encode($stats, JSON_PRETTY_PRINT) . "\n");
}
