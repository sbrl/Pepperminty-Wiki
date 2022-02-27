<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/**
 * Resolves a relative path against a given base directory.
 * @since	0.20.0
 * @source	https://stackoverflow.com/a/44312137/1460422
 * @param	string		$path		The relative path to resolve.
 * @param	string|null	$basePath	The base directory to resolve against.
 * @return	string		An absolute path.
 */
function path_resolve(string $path, string $basePath = null) {
	// Make absolute path
	if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR) {
		if ($basePath === null) {
			// Get PWD first to avoid getcwd() resolving symlinks if in symlinked folder
			$path=(getenv('PWD') ?: getcwd()).DIRECTORY_SEPARATOR.$path;
		} elseif (strlen($basePath)) {
			$path=$basePath.DIRECTORY_SEPARATOR.$path;
		}
	}

	// Resolve '.' and '..'
	$components=array();
	foreach(explode(DIRECTORY_SEPARATOR, rtrim($path, DIRECTORY_SEPARATOR)) as $name) {
		if ($name === '..') {
			array_pop($components);
		} elseif ($name !== '.' && !(count($components) && $name === '')) {
			// … && !(count($components) && $name === '') - we want to keep initial '/' for abs paths
			$components[]=$name;
		}
	}

	return implode(DIRECTORY_SEPARATOR, $components);
}

/*
███████ ████████  ██████  ██████   █████   ██████  ███████ ██████   ██████  ██   ██
██         ██    ██    ██ ██   ██ ██   ██ ██       ██      ██   ██ ██    ██  ██ ██
███████    ██    ██    ██ ██████  ███████ ██   ███ █████   ██████  ██    ██   ███
     ██    ██    ██    ██ ██   ██ ██   ██ ██    ██ ██      ██   ██ ██    ██  ██ ██
███████    ██     ██████  ██   ██ ██   ██  ██████  ███████ ██████   ██████  ██   ██
*/

/**
 * Represents a key-value data store.
 * @license Apache 2.0
 */
class JsonStorageBox {
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
		$this->db = new \PDO("sqlite:" . path_resolve($filename, __DIR__)); // HACK: This might not work on some systems, because it depends on the current working directory
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
	}
	
	/**
	 * Deletes an item from the data store.
	 * @param	string	$key	The key of the item to delete.
	 * @return	bool	Whether it was really deleted or not. Note that if it doesn't exist, then it can't be deleted. Note also that if a node is deleted before being persisted to disk, this will return false when in actuality it was deleted successfully.
	 */
	public function delete(string $key) : bool {
		// Remove it from the cache
		if(isset($this->cache[$key]))
			unset($this->cache[$key]);
		// Remove it from disk
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
					"value" => json_encode($value_data["value"])
				]
			);
		}
		$this->db->commit();
		$this->db = null;
	}
}
