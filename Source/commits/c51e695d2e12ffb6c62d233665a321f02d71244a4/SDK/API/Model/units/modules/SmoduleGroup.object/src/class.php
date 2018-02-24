<?php
//#section#[header]
// Namespace
namespace API\Model\units\modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Model
 * @namespace	\units\modules
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * Module Group Manager
 * 
 * Gets information about module groups.
 * 
 * @version	{empty}
 * @created	November 6, 2013, 14:59 (EET)
 * @revised	November 6, 2013, 15:03 (EET)
 * 
 * @deprecated	Soon to be moved.
 */
class SmoduleGroup
{
	/**
	 * Gets module group information from database.
	 * 
	 * @param	integer	$id
	 * 		The group id.
	 * 
	 * @return	array
	 * 		An array of information as fetched from the database.
	 */
	public static function info($id)
	{
		// Get Module Group's hierarchy Path
		$dbq = new dbQuery("142968574", "units.groups");
		$dbc = new interDbConnection();
		
		// Get Module Group Data
		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);

		// Fetch
		$group = $dbc->fetch($result);
		return $group;
	}
	
	/**
	 * Gets the path to the group folder.
	 * 
	 * @param	integer	$id
	 * 		The group id.
	 * 
	 * @param	string	$delimiter
	 * 		The delimiter for the path.
	 * 
	 * @return	string
	 * 		The full path to the group folder (including parents).
	 */
	public static function trail($id, $delimiter = "/")
	{
		// Get Module Group's hierarchy Path
		$dbq = new dbQuery("158198994", "units.groups");
		$dbc = new interDbConnection();

		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);
		
		$gpath = $delimiter;
		while ($row = $dbc->fetch($result))
			$gpath .= self::dirName($row['id']).$delimiter;
		
		return $gpath;
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$id
	 * 		{description}
	 * 
	 * @return	void
	 */
	private static function dirName($id)
	{
		return "_grp_".$id;
	}
}
//#section_end#
?>