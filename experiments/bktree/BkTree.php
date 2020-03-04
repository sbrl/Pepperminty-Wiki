<?php

require_once("JsonStorageBox.php");

if (!function_exists('stats_standard_deviation')) {
	/**
	 * This user-land implementation follows the implementation quite strictly;
	 * it does not attempt to improve the code or algorithm in any way. It will
	 * raise a warning if you have fewer than 2 values in your array, just like
	 * the extension does (although as an E_USER_WARNING, not E_WARNING).
	 *
	 * @param array $a
	 * @param bool $sample [optional] Defaults to false
	 * @return float|bool The standard deviation or false on error.
	 */
	function stats_standard_deviation(array $a, $sample = false) {
		$n = count($a);
		if ($n === 0) {
			trigger_error("The array has zero elements", E_USER_WARNING);
			return false;
		}
		if ($sample && $n === 1) {
			trigger_error("The array has only 1 element", E_USER_WARNING);
			return false;
		}
		$mean = array_sum($a) / $n;
		$carry = 0.0;
		foreach ($a as $val) {
			$d = ((double) $val) - $mean;
			$carry += $d * $d;
		};
		if ($sample) {
		   --$n;
		}
		return sqrt($carry / $n);
	}
}

/**
 * A serialisable BK-Tree Implementation.
 * Ref: https://nullwords.wordpress.com/2013/03/13/the-bk-tree-a-data-structure-for-spell-checking/
 */
class BkTree
{
	private $box = null;
	
	private $nodes = [];
	
	// private $touched_ids = [];
	
	private $cost_insert = 1;
	private $cost_delete = 1;
	private $cost_replace = 1;
	
	public function __construct($filename) {
		$this->box = new JsonStorageBox($filename);
	}
	
	/**
	 * A utility function for calculating edit distance.
	 * Warning: Do not use this internally! It is *slow*. It's much faster to do this directly. This exists only for external use.
	 * @param	string	$a	The first string.
 	 * @param	string	$b	The second string to compare against.
	 * @return	int		The computed edit distance.
	 */
	public function edit_distance(string $a, string $b) : int {
		return levenshtein($a, $b, $this->cost_insert, $this->cost_replace, $this->cost_delete);
	}
	
	private function get_node_count() : int {
		if(!$this->box->has("node_count"))
			$this->set_node_count(0);
		return $this->box->get("node_count");
	}
	private function set_node_count(int $value) {
		$this->box->set("node_count", $value);
	}
	private function increment_node_count() {
		$this->box->set("node_count", $this->box->get("node_count") + 1);
	}
	
	/**
	 * Adds a string to the tree.
	 * @param	string	$string		The string to add.
	 * @return	int		The depth at which the new node was added.
	 */
	public function add(string $string) : int {
		// FUTURE: When we support deletes, we'll need to ensure that the root node is handled correctly
		if(!$this->box->has("node|0")) {
			$new = new stdClass();
			$new->value = $string;
			$new->children = new stdClass(); // [ "id" => int, "distance" => int ]
			$this->box->set("node|0", $new);
			$this->touched_ids[] = 0;
			$this->increment_node_count();
			return 0;
		}
		
		// if($string == "bunny") echo("\nStart $string\n");
		
		$next_node = $this->box->get("node|0"); // Grab the root to start with
		$next_node_id = 0;
		$depth = 0; $visted = 0;
		while(true) {
			$visted++;
			$distance = levenshtein($string, $next_node->value, $this->cost_insert, $this->cost_replace, $this->cost_delete);
			
			// if($string == "bunny") echo("$visted: Visiting $next_node->value, distance $distance (child distances ".implode(", ", array_map(function($el) { return $el->distance; }, $next_node->children)).")\n");
			
			if(isset($next_node->children->$distance)) {
				$child_id = $next_node->children->$distance;
				$next_node = $this->box->get("node|$child_id");
				$next_node_id = $child_id;
				// if($string == "cake") echo("Identical distance as {$next_node["value"]}, restarting loop\n");
				$depth++;
				continue; // Continue on the outer while loop
			}
			
			// if($string == "bunny") echo("Inserting on $next_node->value\n");
			
			// If we got here, then no existing children have the same edit distance
			// Note that here we don't push to avoid the overhead from either array_push() (considerable) or count() (also considerable).
			
			// Create the new child node
			$new_id = $this->get_node_count();
			$this->box->set("node|$new_id", (object) [
				"value" => $string,
				"children" => new stdClass()
			]);
			// Create the edge that points from the existing node to the new node
			$next_node->children->$distance = $new_id;
			$this->box->set("node|$next_node_id", $next_node);
			$this->increment_node_count();
			break;
		}
		return $depth;
	}
	
	/**
	 * Removes a string from the tree.
 	 * @param	string	$string	The string to remove.
	 * @return	bool	Whether the removal was successful.
	 */
	public function remove(string $string) : bool {
		throw new Error("Error: Not implemented");
		// TODO: Remove a node from the tree.
		// 1. Delete the connection from parent -> target
		// 2. Iterate over the target's children (if any) and re-hang them from the parent
		// NOTE: We need to be careful that the characteristics of the tree are preserved. We should test this by tracing a node's location in the tree and purposefully removing nodes in the chain and see if the results returned as still the same
	}
	
	/**
	 * Convenience function that returns just the first result when looking up a string.
	 * @param	string	$string		The string to lookup
	 * @param	integer	$distance	The maximum edit distance to search.
	 * @return	string|null			The first matching string, or null if no results were found.
	 */
	public function lookup_one(string $string, int $distance = 1) {
		$result = $this->lookup($string, $distance, 1);
		if(empty($result)) return null;
		return $result[0];
		
		// foreach($this->lookup($string, $distance) as $item)
		// 	return $item;
	}
	
	/**
	 * Generator that walks the BK-Tree and iteratively yields results.
	 * TODO: Refactor this to use an array, since generators are ~
	 * @param	string	$string			The search string.
	 * @param	integer	$max_distance	The maximum edit distance to search.
	 * @param	integer	$count			The number of results to return. 0 = All results found. Note that results will be in a random order.
	 * @return	Generator<string>		Iteratively yielded similar resultant strings from the BK-Tree.
	 */
	public function lookup(string $string, int $max_distance = 1, int $count = 0) {
		if($this->get_node_count() == 0) return null;
		
		$result = []; $result_count = 0;
		$stack = [ $this->box->get("node|0") ];
		$stack_top = 0;
		
		// https://softwareengineering.stackexchange.com/a/226162/58491
		while($stack_top >= 0) {
			// Take the topmost node off the stack
			$node_current = $stack[$stack_top];
			unset($stack[$stack_top]);
			$stack_top--;
			
			$distance = levenshtein($string, $node_current->value, $this->cost_insert, $this->cost_replace, $this->cost_delete);
			
			/*
			echo("[lookup] Visiting $node_current->value (distance $distance, child distances ".implode(", ", array_map(function($el) { return $el->distance; }, $node_current->children)).")\n");
			
			if(in_array($node_current->value, ["worlds", "domicil", "mealiest", "stopgaps", "pibroch", "upwardly", "nontruth", "vizoring"])) {
				echo("[lookup] Children: ".implode(", ", array_map(function($el) {
					return "$el->distance: ".$this->box->get("node|$el->id")->value;
				}, $node_current->children))."\n");
			}
			if($node_current->value == "bunny") exit();
			*/
			
			// If the edit distance from the target string to this node is within the tolerance, yield it
			if($distance <= $max_distance) {
				// readline("press any key to continue");
				$result[] = $node_current->value;
				if($count != 0 && $result_count >= $count) break;
				// yield $node_current["value"];
			}
			
			// Adding the key here speeds it up, apparently
			// Ref: https://phpbench.com/
			for($child_distance = $distance - $max_distance; $child_distance <= $distance + $max_distance; $child_distance++) {
				if(!isset($node_current->children->$child_distance))
					continue;
					
				// echo("[lookup] Recursing on child ".$this->box->get("node|$child->id")->value." (distance $child->distance)\n");
				// Push the node onto the stack
				// Note that it doesn't actually matter that the stack isn't an accurate representation of ancestor nodes at any given time here. The stack is really a hybrid between a stack and a queue, having features of both.
				$stack_top++;
				$stack[$stack_top] = $this->box->get("node|{$node_current->children->$child_distance}");
			}
		}
		
		return $result;
	}
	
	/**
	 * Calculate statistics about the BK-Tree.
	 * Useful for analysing a tree's structure.
	 * If the tree isn't balanced, you may need to insert items in a different order.
	 * @return array An array of statistics about this BK-Tree.
	 */
	public function stats() {
		$result = [
			"depth_max" => 0,
			"depth_min_leaf" => INF,
			"depth_average" => 0,
			"depth_average_noleaf" => 0,
			"depth_standard_deviation" => [],
			"child_count_average" => 0,
			"child_count_max" => 0,
			"nodes" => $this->get_node_count(),
			"leaves" => 0,
			"non_leaves" => 0
		];
		
		$start_time = microtime(true);
		
		$stack = [ [ "node" => $this->box->get("node|0"), "depth" => 0 ] ];
		
		// https://softwareengineering.stackexchange.com/a/226162/58491
		while(!empty($stack)) {
			// Take the top-most node off the stack
			$current = array_pop($stack);
			
			// echo("Visiting "); var_dump($current);
			
			// Operate on the node
			$result["depth_standard_deviation"][] = $current["depth"];
			$result["depth_average"] += $current["depth"];
			if($current["depth"] > $result["depth_max"])
				$result["depth_max"] = $current["depth"];
			if(empty($current["node"]->children) && $current["depth"] < $result["depth_min_leaf"])
				$result["depth_min_leaf"] = $current["depth"];
			
			$child_count = count((array)($current["node"]->children));
			$result["child_count_average"] += $child_count;
			if($child_count > $result["child_count_max"])
				$result["child_count_max"] = $child_count;
			if($child_count > 0) {
				$result["depth_average_noleaf"] += $current["depth"];
				$result["non_leaves"]++;
			}
			else
				$result["leaves"]++;
			
			// Iterate over the child nodes
			foreach($current["node"]->children as $child_distance => $child_id) {
				$stack[] = [
					"node" => $this->box->get("node|$child_id"),
					"depth" => $current["depth"] + 1
				];
			}
		}
		$result["depth_average"] /= $result["nodes"];
		$result["depth_average_noleaf"] /= $result["non_leaves"];
		$result["child_count_average"] /= $result["nodes"];
		$result["depth_standard_deviation"] = stats_standard_deviation($result["depth_standard_deviation"]);
		
		$result["time_taken"] = microtime(true) - $start_time;
		
		return $result;
	}
	
	/**
	 * Saves changes to the tree back to disk.
	 * @return	void
	 */
	public function close() {
		$this->box->close();
	}
}
