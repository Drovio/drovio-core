<?php
//#section#[header]
// Namespace
namespace API\Developer\components\units\modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\units\modules
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");
importer::import("API", "Security", "account");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;
use \API\Security\account;

/**
 * Module Group Manager
 * 
 * Manages module groups
 * 
 * @version	{empty}
 * @created	November 26, 2013, 11:31 (EET)
 * @revised	November 30, 2013, 12:26 (EET)
 */
class moduleGroup
{
	/**
	 * All the group trails for each group. Expands incrementally on each different request.
	 * 
	 * @type	array
	 */
	private static $groupTrails = array();
	
	/**
	 * Gets the group trail for a given group id.
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
			$dbc = new interDbConnection();
			$dbq = new dbQuery("158198994", "units.groups");
	
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
	 * Creates a new module group.
	 * 
	 * @param	string	$description
	 * 		The group description (name).
	 * 
	 * @param	integer	$parent_id
	 * 		The parent group id. Leave empty for root groups.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($description, $parent_id = NULL)
	{
		// Check if user has master access to parent group
		$result = developer::masterGroup($parent_id);
		if ($result === FALSE)
			return $result;
		
		// create new group
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1238026976", "units.groups");
		
		$attr = array();
		$attr['parent_id'] = (is_null($parent_id) ? 'NULL' : $parent_id);
		$attr['description'] = $description;

		$result = $dbc->execute($dbq, $attr);

		if ($result === FALSE)
			return $result;
		
		// Get group id
		$row = $dbc->fetch($result);
		$groupID = $row['last_id'];
		
		// Grant development access to developer for new group
		$dbq = new dbQuery("184651428", "security.privileges.developer");
	
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$attr['gid'] = $groupID;
		$result = $dbc->execute($dbq, $attr);
		
		if ($result === FALSE)
			return $result;
		
		return $result;
	}
	
	/**
	 * Deletes an existing module group.
	 * 
	 * @param	integer	$groupID
	 * 		The group id to be deleted.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function delete($groupID)
	{
		// Check if user has development access to group
		$result = developer::masterGroup($groupID);
		if ($result === FALSE)
			return $result;
		
		// Check if group is empty
		
		// Remove group from DB
		$attr = array();
		$attr['id'] = $groupID;
		$dbq = new dbQuery("389193473", "units.groups");
		//$result = $this->dbc->execute($dbq, $attr);

		// Delete group directory
		if (!$result)
			return FALSE;
		
		// Remove repository folder
		return TRUE;
	}
	
	public static function info($groupID)
	{
		// Get Module Group's hierarchy Path
		$dbc = new interDbConnection();
		$dbq = new dbQuery("142968574", "units.groups");
		
		// Get Module Group Data
		$attr = array();
		$attr['id'] = $groupID;
		$result = $dbc->execute($dbq, $attr);

		// Fetch
		return $dbc->fetch($result);
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