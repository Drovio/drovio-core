<?php
//#section#[header]
// Namespace
namespace API\Model\modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Model
 * @namespace	\modules
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * Module Group Manager
 * 
 * Manages module groups
 * 
 * @version	1.0-2
 * @created	May 5, 2014, 10:44 (EEST)
 * @updated	April 8, 2015, 13:01 (EEST)
 */
class mGroup
{
	/**
	 * All the group trails for each group. Expands incrementally on each different request, works as cache.
	 * 
	 * @type	array
	 */
	private static $groupTrails = array();
	
	/**
	 * All module groups info to work as a cache.
	 * 
	 * @type	array
	 */
	private static $gInfo = array();
	
	/**
	 * Global group hierarchy, to work as a cache.
	 * 
	 * @type	array
	 */
	private static $gHierarchy = array();
	
	/**
	 * Gets the group path trail for a given group id.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 
	 * @param	string	$delimiter
	 * 		The delimiter for the path. By default it is the directory delimiter "/".
	 * 
	 * @return	string
	 * 		The group path from the root.
	 */
	public static function getTrail($groupID, $delimiter = "/")
	{
		if (empty(self::$groupTrails[$groupID."_".$delimiter]))
		{
			// Get Module Group's hierarchy Path
			$dbc = new dbConnection();
			$dbq = new dbQuery("2110571564692", "modules.groups");
	
			$attr = array();
			$attr['id'] = $groupID;
			$result = $dbc->execute($dbq, $attr);
			
			// Form groupTrail
			$groupTrail = $delimiter;
			while ($row = $dbc->fetch($result))
				$groupTrail .= self::getDirectoryName($row['id']).$delimiter;
			
			self::$groupTrails[$groupID."_".$delimiter] = $groupTrail;
		}
		
		return self::$groupTrails[$groupID."_".$delimiter];
	}
	
	/**
	 * Gets the group info in an array.
	 * 
	 * @param	integer	$groupID
	 * 		The module group id.
	 * 
	 * @return	array
	 * 		An array with the group info.
	 */
	public static function info($groupID)
	{
		// Check cache
		if (empty(self::$gInfo))
		{
			// Get Module Groups Info from database
			$dbq = new dbQuery("14319975508636", "modules.groups");
			$dbc = new dbConnection();
			
			// Get All group information
			$result = $dbc->execute($dbq);
			while ($info = $dbc->fetch($result))
				self::$gInfo[$info['id']] = $info;
		}
		
		return self::$gInfo[$groupID];
	}
	
	/**
	 * Get all module groups in the system.
	 * 
	 * @return	array
	 * 		An array of all module groups with their information.
	 */
	public static function getAllGroups()
	{
		// Check cache
		if (empty(self::$gHierarchy))
		{
			// Get Module Group's hierarchy Path
			$dbc = new dbConnection();
			$dbq = new dbQuery("27540462673664", "modules.groups");
			$result = $dbc->execute($dbq);
			self::$gHierarchy = $dbc->fetch($result, TRUE);
		}
		
		return self::$gHierarchy;
	}
	
	/**
	 * Gets the group directory name.
	 * 
	 * @param	integer	$id
	 * 		The group id.
	 * 
	 * @return	string
	 * 		The group directory name.
	 */
	private static function getDirectoryName($id)
	{
		return "g".$id.".mGroup";
	}
}
//#section_end#
?>