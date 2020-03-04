<?php

require("BkTree.php");

function time_callable($callable) {
	$start_time = microtime(true);
	return [
		"value" => $callable(),
		"time" => microtime(true) - $start_time
	];
}

function tree_create() {
	$tree = new BkTree("bktree.sqlite");
	
	echo("Populating tree - ");
	$time = microtime(true);
	$handle = fopen("enable1.shuf.txt", "r"); $i = 0;
	while(($line = fgets($handle)) !== false) {
		// if($i > 10) exit();
		$line = trim($line);
		$tree->add($line);
		$i++;
	}
	echo("done in ".round((microtime(true) - $time) * 1000, 2)."ms\n");
	
	fclose($handle);
	return $tree;
}
function tree_save(BkTree $tree) {
	echo("Saving tree\n");
	$tree->close();
}
function tree_load() {
	return new BkTree("bktree.sqlite");
}

function test_search_linear() {
	$start_time = microtime(true);
	$handle = fopen("enable1.shuf.txt", "r");
	while(($line = fgets($handle)) !== false) {
		if(levenshtein("cakke", trim($line)) > 2) continue;
		echo("linear match: ".trim($line)."\n");
	}
	echo("done in ".round((microtime(true) - $start_time) * 1000, 2)."ms\n");
	exit();
}

if(file_exists("bktree.sqlite"))
	$tree = time_callable("tree_load");
else
	$tree = time_callable("tree_create");

echo("Tree created in ".($tree["time"]*1000)."ms\n");
$tree = $tree["value"];

echo("Tree stats: ");
var_dump($tree->stats());

function test_auto() {
	global $tree;
	for($i = 0; $i < 1; $i++) {
		$start_time = microtime(true);
		$results = $tree->lookup("cakke", 2);
		echo("Lookup complete in ".round((microtime(true) - $start_time)*1000, 2)."ms (".count($results)." results found)\n");
	}
	exit();
}

test_auto();

echo("BkTree Test CLI\n");
echo("Exit with .exit\n");
echo("This ensures the tree is saved to disk\n");

while(true) {
	$line = readline("> "); // Newline is removed automatically
	if(strlen($line) == 0) continue;
	
	readline_add_history($line);
	
	if($line[0] == ".") {
		switch ($line) {
			case ".quit":
			case ".exit":
				$result = time_callable(function() use ($tree) {
					tree_save($tree);
				});
				echo("Serialised tree in ".round($result["time"] * 1000, 2)."ms\n");
				exit("exit\n");
				break;
		}
		continue;
	}
	
	// var_dump($line);
	
	$time = microtime(true);
	$results = $tree->lookup($line, 2); $i = 0;
	$time = round((microtime(true) - $time)*1000, 2);
	$time_sort = microtime(true);
	// Note that adding a cache here doesn't make a significant different to performance
	// The overhead of calling a function far outweighs that of calling levenshtein(), apparently
	usort($results, function($a, $b) use ($line, $tree) {
		return $tree->edit_distance($a, $line) - $tree->edit_distance($b, $line);
	});
	$time_sort = round((microtime(true) - $time_sort)*1000, 2);
	foreach($results as $result) {
		echo(
			str_pad($i, 5, " ", STR_PAD_LEFT).": ".
			str_pad($result, 20).
			" dist ".$tree->edit_distance($result, $line).
			"\n"
		);
		$i++;
	}
	// $start_time_inc = microtime(true);
	// $i = 0;
	// foreach($tree->lookup($line, 2) as $result) {
	// 	// var_dump($result);
	// 	echo(
	// 		str_pad(
	// 			str_pad("$i: $result", 20)."dist ".levenshtein($result, $line),
	// 			40
	// 		).
	// 		"+".round((microtime(true) - $start_time_inc)*1000, 2)."ms\n"
	// 	);
	// 	// readline("(press enter to continue)");
	// 
	// 	$start_time_inc = microtime(true);
	// 	$i++;
	// }
	echo("Found $i results in {$time}ms (+{$time_sort}ms sort)\n");
}
