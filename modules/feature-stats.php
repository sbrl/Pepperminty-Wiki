<?php
register_module([
	"name" => "Statistics",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "An extensible statistics calculation system. Comes with a range of built-in statistics, but can be extended by other modules too.",
	"id" => "feature-stats",
	"code" => function() {
		global $settings;
		/**
		 * @api {get|post} ?action=stats-update Recalculate the wiki's statistics
		 * @apiName UpdateStats
		 * @apiGroup Utility
		 * @apiPermission Administrator
		 *
		 * @apiParam	{string}	secret	POST only, optional. If you're not logged in, you can specify the wiki's sekret (find it in peppermint.json) using this parameter.
		 */
		
		/*
		███████ ████████  █████  ████████ ███████
		██         ██    ██   ██    ██    ██
		███████    ██    ███████    ██    ███████
		     ██    ██    ██   ██    ██         ██
		███████    ██    ██   ██    ██    ███████
		
		██    ██ ██████  ██████   █████  ████████ ███████
		██    ██ ██   ██ ██   ██ ██   ██    ██    ██
		██    ██ ██████  ██   ██ ███████    ██    █████
		██    ██ ██      ██   ██ ██   ██    ██    ██
		 ██████  ██      ██████  ██   ██    ██    ███████
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
			
			update_statistics(true);
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
			}
		]);

		statistic_add([
			"id" => "page_count",
			"name" => "Page Count",
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
	}
]);

function update_statistics($update_all = false)
{
	global $settings, $statistic_calculators;
	
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
		
		if(!$update_all && microtime(true) - $start_time >= $stats_update_processingtime)
			break;
	}
	
	header("x-stats-recalculated: $stats_updated");
	//round((microtime(true) - $pageindex_read_start)*1000, 3)
	header("x-stats-calctime: " . round((microtime(true) - $start_time)*1000, 3) . "ms");
	
	stats_save($stats);
}

/**
 * Loads and returns the statistics cache file.
 * @return object The loaded & decoded statistics.
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
 * @param	object	The statistics cache to save.
 * @return	bool	Whether saving succeeded or not.
 */
function stats_save($stats)
{
	global $paths;
	return file_put_contents($paths->statsindex, json_encode($stats, JSON_PRETTY_PRINT) . "\n");
}
