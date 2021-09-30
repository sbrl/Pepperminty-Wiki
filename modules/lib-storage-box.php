<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

register_module([
	"name" => "Library: Storage box",
	"version" => "0.13",
	"author" => "Starbeamrainbowlabs",
	"description" => "A library module that provides a fast cached key-value store backed by SQLite. Used by the search engine.",
	"id" => "lib-storage-box",
	"code" => function() {
		
	}
]);

/*
███████ ████████  ██████  ██████   █████   ██████  ███████ ██████   ██████  ██   ██
██         ██    ██    ██ ██   ██ ██   ██ ██       ██      ██   ██ ██    ██  ██ ██
███████    ██    ██    ██ ██████  ███████ ██   ███ █████   ██████  ██    ██   ███
     ██    ██    ██    ██ ██   ██ ██   ██ ██    ██ ██      ██   ██ ██    ██  ██ ██
███████    ██     ██████  ██   ██ ██   ██  ██████  ███████ ██████   ██████  ██   ██
*/

/**
 * Represents a key-value data store.
 * 
 */
class StorageBox {
	const MODE_JSON = 0;
	const MODE_ARR_SIMPLE = 1;
	
	/**
	 * The SQLite database connection.
	 * @var \PDO
	 */
	private $db;
	
	/**
	 * A cache of values.
	 * @var object[]
	 */
	private $cache = [];
	
	/**
	 * A cache of prepared SQL statements.
	 * @var \PDOStatement[]
	 */
	private $query_cache = [];
	
	/**
	 * Initialises a new store connection.
	 * @param	string	$filename	The filename that the store is located in.
	 */
	function __construct(string $filename) {
		$firstrun = !file_exists($filename);
		$filename_db = path_resolve($filename, __DIR__);
		if(!file_exists($filename_db)) touch($filename_db);
		$this->db = new \PDO("sqlite:$filename_db"); // HACK: This might not work on some systems, because it depends on the current working directory
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if($firstrun) {
			$this->query("CREATE TABLE store (key TEXT UNIQUE NOT NULL, value TEXT)");
		}
	}
	/**
	 * Makes a query against the database.
	 * @param	string	$sql		The (potentially parametised) query to make.
	 * @param	array	$variables	Optional. The variables to substitute into the SQL query.
	 * @return	\PDOStatement		The result of the query, as a PDOStatement.
	 */
	private function query(string $sql, array $variables = []) {
		// Add to the query cache if it doesn't exist
		if(!isset($this->query_cache[$sql]))
			$this->query_cache[$sql] = $this->db->prepare($sql);
		$this->query_cache[$sql]->execute($variables);
		return $this->query_cache[$sql]; // fetchColumn(), fetchAll(), etc. are defined on the statement, not the return value of execute()
	}
	
	/**
	 * Determines if the given key exists in the store or not.
	 * @param	string	$key	The key to test.
	 * @return	bool	Whether the key exists in the store or not.
	 */
	public function has(string $key) : bool {
		if(isset($this->cache[$key]))
			return true;
		return $this->query(
			"SELECT COUNT(key) FROM store WHERE key = :key;",
			[ "key" => $key ]
		)->fetchColumn() > 0;
	}
	
	/**
	 * Returns an iterable that returns all the keys that do not contain the given string.
	 * @param	string			$exclude		The string to search for when excluding keys.
	 * @return	PDOStatement	The iterable. Use a foreach loop on it.
	 */
	public function get_keys(string $exclude) : \PDOStatement {
		return $this->query(
			"SELECT key FROM store WHERE key NOT LIKE :containing;",
			[ "containing" => "%$exclude%" ]
		);
	}
	
	/**
	 * Gets a value from the store.
	 * @param	string	$key	The key value is stored under.
	 * @return	mixed	The stored value.
	 */
	public function get(string $key) {
		// If it's not in the cache, insert it
		if(!isset($this->cache[$key])) {
			$this->cache[$key] = [ "modified" => false, "value" => json_decode($this->query(
				"SELECT value FROM store WHERE key = :key;",
				[ "key" => $key ]
			)->fetchColumn()) ];
		}
		return $this->cache[$key]["value"];
	}
	public function get_arr_simple(string $key, string $delimiter = "|") {
		// If it's not in the cache, insert it
		if(!isset($this->cache[$key])) {
			$this->cache[$key] = [
				"modified" => false,
				"value" => explode($delimiter, $this->query(
					"SELECT value FROM store WHERE key = :key;",
					[ "key" => $key ]
				)->fetchColumn())
			];
		}
		return $this->cache[$key]["value"];
	}
	
	/**
	 * Sets a value in the data store.
	 * Note that this does NOT save changes to disk until you close the connection!
	 * @param	string	$key	The key to set the value of.
	 * @param	mixed	$value	The value to store.
	 */
	public function set(string $key, $value) : void {
		if(!isset($this->cache[$key])) $this->cache[$key] = [];
		$this->cache[$key]["value"] = $value;
		$this->cache[$key]["modified"] = true;
		$this->cache[$key]["mode"] = self::MODE_JSON;
	}
	public function set_arr_simple(string $key, $value, string $delimiter = "|") : void {
		if(!isset($this->cache[$key])) $this->cache[$key] = [];
		$this->cache[$key]["value"] = $value;
		$this->cache[$key]["modified"] = true;
		$this->cache[$key]["delimiter"] = $delimiter;
		$this->cache[$key]["mode"] = self::MODE_ARR_SIMPLE;
	}
	
	/**
	 * Deletes an item from the data store.
	 * @param	string	$key	The key of the item to delete.
	 * @return	bool	Whether it was really deleted or not. Note that if it doesn't exist, then it can't be deleted.
	 */
	public function delete(string $key) : bool {
		// Remove it from the cache
		if(isset($this->cache[$key]))
			unset($this->cache[$key]);
		// Remove it from disk
		// TODO: Queue this action for the transaction later
		return $this->query(
			"DELETE FROM store WHERE key = :key;",
			[ "key" => $key ]
		)->rowCount() > 0;
	}
	
	/**
	 * Empties the store.
	 */
	public function clear() : void {
		// Empty the cache;
		$this->cache = [];
		// Empty the disk
		$this->query("DELETE FROM store;");
	}
	
	/**
	 * Syncs changes to disk and closes the PDO connection.
	 */
	public function close() : void {
		$this->db->beginTransaction();
		foreach($this->cache as $key => $value_data) {
			// If it wasn't modified, there's no point in saving it, is there?
			if(!$value_data["modified"])
				continue;
			
			$this->query(
				"INSERT OR REPLACE INTO store(key, value) VALUES(:key, :value)",
				[
					"key" => $key,
					"value" => $value_data["mode"] == self::MODE_ARR_SIMPLE ?
						implode($value_data["delimiter"], $value_data["value"]) :
						json_encode($value_data["value"])
				]
			);
		}
		$this->db->commit();
		$this->db = null;
	}
}

?>
