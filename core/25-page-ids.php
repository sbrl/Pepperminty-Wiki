<?php

//////////////////////////
///// Page id system /////
//////////////////////////
if(!file_exists($paths->idindex))
	file_put_contents($paths->idindex, "{}");
$idindex_decode_start = microtime(true);
$idindex = json_decode(file_get_contents($paths->idindex));
$env->perfdata->idindex_decode_time = round((microtime(true) - $idindex_decode_start)*1000, 3);
/**
 * Provides an interface to interact with page ids.
 * @package core
 */
class ids
{
	/**
	 * Gets the page id associated with the given page name.
	 * If it doesn't exist in the id index, it will be added.
	 * @package core
	 * @param	string	$pagename	The name of the page to fetch the id for.
	 * @return	int		The id for the specified page name.
	 */
	public static function getid($pagename)
	{
		global $idindex;
		
		$pagename_norm = Normalizer::normalize($pagename, Normalizer::FORM_C);
		foreach ($idindex as $id => $entry)
		{
			// We don't need to normalise here because we normralise when assigning ids
			if($entry == $pagename_norm)
				return $id;
		}
		
		// This pagename doesn't have an id - assign it one quick!
		return self::assign($pagename);
	}

	/**
	 * Gets the page name associated with the given page id.
	 * Be warned that if the id index is cleared (e.g. when the search index is
	 * rebuilt from scratch), the id associated with a page name may change!
	 * @package core
	 * @param	int		$id		The id to fetch the page name for.
	 * @return	string	The page name currently associated with the specified id.
	 */
	public static function getpagename($id)
	{
		global $idindex;

		if(!isset($idindex->$id))
			return false;
		else
			return $idindex->$id;
	}
	
	/**
	 * Moves a page in the id index from $oldpagename to $newpagename.
	 * Note that this function doesn't perform any special checks to make sure
	 * that the destination name doesn't already exist.
	 * @package core
	 * @param	string	$oldpagename	The old page name to move.
	 * @param	string	$newpagename	The new page name to move the old page name to.
	 */
	public static function movepagename($oldpagename, $newpagename)
	{
		global $idindex, $paths;
		
		$pageid = self::getid(Normalizer::normalize($oldpagename, Normalizer::FORM_C));
		$idindex->$pageid = Normalizer::normalize($newpagename, Normalizer::FORM_C);
		
		file_put_contents($paths->idindex, json_encode($idindex));
	}
	
	/**
	 * Removes the given page name from the id index.
	 * Note that this function doesn't handle multiple entries with the same
	 * name. Also note that it may get re-added during a search reindex if the
	 * page still exists.
	 * @package core
	 * @param	string	$pagename	The page name to delete from the id index.
	 */
	public static function deletepagename($pagename)
	{
		global $idindex, $paths;
		
		// Get the id of the specified page
		$pageid = self::getid($pagename);
		// Remove it from the pageindex
		unset($idindex->$pageid);
		// Save the id index
		file_put_contents($paths->idindex, json_encode($idindex));
	}
	
	/**
	 * Clears the id index completely.
	 * Will break the inverted search index! Make sure you rebuild the search
	 * index (if the search module is installed, of course) if you want search
	 * to still work. Of course, note that will re-add all the pages to the id
	 * index.
	 * @package core
	 */
	public static function clear()
	{
		global $paths, $idindex;
		// Delete the old id index
		unlink($paths->idindex);
		// Create the new id index
		file_put_contents($paths->idindex, "{}");
		// Reset the in-memory id index
		$idindex = new stdClass();
	}

	/**
	 * Assigns an id to a pagename. Doesn't check to make sure that
	 * pagename doesn't already exist in the id index.
	 * @package core
	 * @param	string	$pagename	The page name to assign an id to.
	 * @return	int					The id assigned to the specified page name.
	 */
	protected static function assign($pagename)
	{
		global $idindex, $paths;
		
		$pagename = Normalizer::normalize($pagename, Normalizer::FORM_C);

		$nextid = count(array_keys(get_object_vars($idindex)));
		// Increment the generated id until it's unique
		while(isset($idindex->nextid))
			$nextid++;
		
		// Update the id index
		$idindex->$nextid = $pagename;

		// Save the id index
		file_put_contents($paths->idindex, json_encode($idindex));

		return $nextid;
	}
}
//////////////////////////
//////////////////////////
