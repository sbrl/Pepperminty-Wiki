<?php
register_module([
	"name" => "Did you mean? support",
	"version" => "0.1",
	"author" => "Starbeamrainbowlabs",
	"description" => "Ever searched for something but couldn't find it because you couldn't spell it correctly? This module is for you! It adds spelling correction for search queries based on the words in the inverted search index.",
	"id" => "lib-storage-box",
	"depends" => [ "lib-search-engine" ],
	"code" => function() {
		
	}
]);

/**
 * Calculates the standard deviation of an array of numbers.
 * @source https://stackoverflow.com/a/57694168/1460422
 * @param	array	$array	The array of numbers to calculate the standard deviation of.
 * @return	float	The standard deviation of the numbers in the given array.
 */
function standard_deviation(array $array): float {
    $size = count($array);
    $mean = array_sum($array) / $size;
    $squares = array_map(function ($x) use ($mean) {
        return pow($x - $mean, 2);
    }, $array);

    return sqrt(array_sum($squares) / ($size - 1));
}

/**
 * A serialisable BK-Tree Implementation.
 * Ref: https://nullwords.wordpress.com/2013/03/13/the-bk-tree-a-data-structure-for-spell-checking/
 */
class BkTree {
	private $box = null;
	
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
	 * @param	string	$string				The string to add.
	 * @param	int		$starting_node_id	The id fo node to start insertion from. Defaults to 0 - for internal use only.
	 * @return	int		The depth at which the new node was added.
	 */
	public function add(string $string, int $starting_node_id = 0) : int {
		if(!$this->box->has("node|0")) {
			// If the root node of the tree doesn't exist, create it
			$new = new stdClass();
			$new->value = $string;
			$new->children = new stdClass(); // [ "id" => int, "distance" => int ]
			$this->box->set("node|0", $new);
			$this->touched_ids[] = 0;
			$this->increment_node_count();
			return 0;
		}
		
		if(!$this->box->has("node|$starting_node_id"))
			throw new Exception("Error: Failed to find node with id $starting_node_id to begin insertion");
		
		// if($string == "bunny") echo("\nStart $string\n");
		
		$next_node = $this->box->get("node|$starting_node_id"); // Grab the root to start with
		$next_node_id = $starting_node_id;
		$depth = 0; $visted = 0;
		while(true) {
			$visted++;
			$distance = levenshtein($string, $next_node->value, $this->cost_insert, $this->cost_replace, $this->cost_delete);
			
			if(isset($next_node->children->$distance)) {
				$child_id = $next_node->children->$distance;
				$next_node = $this->box->get("node|$child_id");
				$next_node_id = $child_id;
				$depth++;
				continue; // Continue on the outer while loop
			}
			
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
	 * BUG: If this deletes the root node, then it's all over and it will crash
 	 * @param	string	$string	The string to remove.
	 * @return	bool	Whether the removal was successful.
	 */
	public function remove(string $string) : bool {
		$stack = [ [ "node" => $this->box->get("node|0"), "id" => 0 ] ];
		$node_target = $stack[0]["node"];
		$node_target_id = 0;
		
		while($node_target->value !== $string) {
			$distance = levenshtein($string, $node_target->value, $this->cost_insert, $this->cost_replace, $this->cost_delete);
			
			// Failed to recurse to find the node with the value in question
			if(!isset($node_target->children->$distance))
				return false;
			
			$node_target_id = $node_target->children->$distance;
			$node_target = $this->box->get("node|$node_target_id");
			$stack[] = [ "node" => $node_target, "id" => $node_target_id ];
		}
		
		// The last item but 1 on the stack is the parent node
		$parent = $stack[count($stack) - 2];
		
		// 1. Delete the connection from parent -> target
		foreach($parent["node"]->children as $distance => $id) {
			if($id == $node_target_id) {
				unset($parent["node"]->children->$distance);
				break;
			}
		}
		
		// Save the parent node's back to disk
		// Note that we do this *before* sorting out the orphans, since it's possible that $this->add() will modify it further
		$this->box->set("node|{$parent["id"]}", $parent["node"]);
		
		// 2. Iterate over the target's children (if any) and re-hang them from the parent
		
		// Hang the now orphaned children and all their decendants from the parent
		foreach($node_target->children as $distance => $id) {
			$orphan = $this->box->get("node|$id");
			$substack = [ [ "node" => $orphan, "id" => $id ] ]; $substack_top = 0;
			while($substack_top >= 0) {
				$next = $substack[$substack_top];
				unset($substack[$substack_top]);
				$substack_top--;
				
				$this->box->delete("node|{$next["id"]}"); // Delete the orphan node
				$this->add($next["node"]->value, $parent["id"]); // Re-hang it from the parent
				
				foreach($next["node"]->children as $distance => $sub_id) {
					$substack[++$substack_top] = [
						"node" => $this->box->get("node|$sub_id"),
						"id" => $sub_id
					];
				}
			}
		}
		
		// Delete the target node
		$this->box->delete("node|$node_target_id");
		
		return true;
	}
	
	public function trace(string $string) {
		$stack = [
			(object) [ "node" => $this->box->get("node|0"), "id" => 0 ]
		];
		$node_target = $stack[0]->node;
		
		while($node_target->value !== $string) {
			$distance = levenshtein($string, $node_target->value, $this->cost_insert, $this->cost_replace, $this->cost_delete);
			
			// var_dump($node_target);
			
			// Failed to recurse to find the node with the value in question
			if(!isset($node_target->children->$distance))
				return null;
			
			$node_target_id = $node_target->children->$distance;
			$node_target = $this->box->get("node|$node_target_id");
			$stack[] = (object) [ "node" => $node_target, "id" => $node_target_id ];
		}
		return $stack;
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
	}
	
	/**
	 * Generator that walks the BK-Tree and iteratively yields results.
	 * Note that the returned array is *not* sorted.
	 * @param	string	$string			The search string.
	 * @param	integer	$max_distance	The maximum edit distance to search.
	 * @param	integer	$count			The number of results to return. 0 = All results found. Note that results will be in a random order.
	 * @return	array<string>			Similar resultant strings from the BK-Tree.
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
			
			// If the edit distance from the target string to this node is within the tolerance, store it
			if($distance <= $max_distance) {
				$result[] = $node_current->value;
				$result_count++;
				if($count != 0 && $result_count >= $count) break;
			}
			
			for($child_distance = $distance - $max_distance; $child_distance <= $distance + $max_distance; $child_distance++) {
				if(!isset($node_current->children->$child_distance))
					continue;
				
				$stack[++$stack_top] = $this->box->get("node|{$node_current->children->$child_distance}");
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
		$result["depth_standard_deviation"] = standard_deviation($result["depth_standard_deviation"]);
		
		$result["time_taken"] = microtime(true) - $start_time;
		
		return $result;
	}
	
	/**
	 * Iteratively walks the BkTree.
	 * Warning: This is *slow*
	 * @return Generator<stdClass> A generator that iteratively walks the tree and yields every item therein that's connected to the root node.
	 */
	public function walk() {
		$stack = [ (object)[
			"id" => 0,
			"node" => $this->box->get("node|0"),
			"parent_id" => -1,
			"parent" => null,
			"depth" => 0
		] ];
		$stack_top = 0;
		
		// https://softwareengineering.stackexchange.com/a/226162/58491
		while(!empty($stack)) {
			// Take the topmost node off the stack
			$current = $stack[$stack_top];
			unset($stack[$stack_top]);
			$stack_top--;
			
			// echo("Visiting "); var_dump($current);
			yield $current;
			
			// Iterate over the child nodes
			foreach($current->node->children as $child_distance => $child_id) {
				$stack_top++;
				$stack[$stack_top] = (object) [
					"id" => $child_id,
					"node" => $this->box->get("node|{$current->node->children->$child_distance}"),
					"parent_id" => $current->id,
					"parent" => $current->node,
					"depth" => $current->depth + 1
				];
			}
		}
	}
	
	/**
	 * Saves changes to the tree back to disk.
	 * @return	void
	 */
	public function close() {
		$this->box->close();
	}
}


?>
