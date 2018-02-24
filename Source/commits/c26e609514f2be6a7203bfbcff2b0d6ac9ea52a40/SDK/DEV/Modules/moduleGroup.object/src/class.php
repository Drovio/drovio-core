<?php
//#section#[header]
// Namespace
namespace DEV\Modules;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Modules
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");
importer::import("API", "Security", "account");
importer::import("API", "Model", "modules::mGroup");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;
use \API\Security\account;
use \API\Model\modules\mGroup;

/**
 * Module Group Manager
 * 
 * Manages module groups
 * 
 * @version	{empty}
 * @created	April 1, 2014, 23:18 (EEST)
 * @revised	June 13, 2014, 11:48 (EEST)
 */
class moduleGroup
{
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
	 * 
	 * @deprecated	Use \API\Model\modules\mGroup::getTrail() instead.
	 */
	public static function getTrail($groupID, $delimiter = "/")
	{
		return mGroup::getTrail($groupID, $delimiter);
	}
		
	/**
	 * Creates a new module group.
	 * 
	 * @param	{type}	$description
	 * 		The group description (name).
	 * 
	 * @param	integer	$parent_id
	 * 		The parent group id. Leave empty (null) for root groups.
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
	 * Removes an existing module group.
	 * The group must be empty of modules or other groups.
	 * 
	 * @param	integer	$groupID
	 * 		The group id to be deleted.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($groupID)
	{
		// Check if user has development access to group
		$result = developer::masterGroup($groupID);
		if ($result === FALSE)
			return $result;
		
		$dbc = new interDbConnection();
		
		// Check if group is empty of modules
		$attr = array();
		$attr['gid'] = $groupID;
		$dbq = new dbQuery("666615842", "units.modules");
		$result = $dbc->execute($dbq, $attr);
		if ($dbc->get_num_rows($result) > 0)
			return FALSE;
		
		// Check if group is empty of other groups
		$attr = array();
		$attr['id'] = $groupID;
		$dbq = new dbQuery("1272869646", "units.groups");
		$result = $dbc->execute($dbq, $attr);
		if ($dbc->get_num_rows($result) > 1)
			return FALSE;
		
		// Remove group from DB
		$attr = array();
		$attr['id'] = $groupID;
		$dbq = new dbQuery("389193473", "units.groups");
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Gets the group info in an array.
	 * 
	 * @param	integer	$groupID
	 * 		The module group id.
	 * 
	 * @return	array
	 * 		An array with the group info.
	 * 
	 * @deprecated	Use \API\Model\modules\mGroup::info() instead.
	 */
	public static function info($groupID)
	{
		return mGroup::info($groupID);
	}
	
	/**
	 * Updates a module group's description
	 * 
	 * @param	integer	$groupID
	 * 		The module group id.
	 * 
	 * @param	string	$description
	 * 		The module group description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($groupID, $description)
	{
		// Get Module Group's hierarchy Path
		$dbc = new interDbConnection();
		$dbq = new dbQuery("33621070078516", "units.groups");
		
		// Get Module Group Data
		$attr = array();
		$attr['gid'] = $groupID;
		$attr['description'] = $description;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>