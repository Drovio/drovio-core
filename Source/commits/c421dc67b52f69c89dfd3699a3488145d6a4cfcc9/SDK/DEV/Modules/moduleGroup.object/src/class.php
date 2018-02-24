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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Model", "modules/module");
importer::import("API", "Model", "modules/mGroup");
importer::import("API", "Profile", "account");
importer::import("DEV", "Profile", "developer");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Model\modules\module;
use \API\Model\modules\mGroup;
use \API\Profile\account;
use \DEV\Profile\developer;

/**
 * Module Group Manager
 * 
 * Manages module groups
 * 
 * @version	1.0-1
 * @created	April 1, 2014, 23:18 (EEST)
 * @updated	April 8, 2015, 13:01 (EEST)
 */
class moduleGroup
{
	/**
	 * Creates a new module group.
	 * 
	 * @param	string	$description
	 * 		The group description (name).
	 * 
	 * @param	integer	$parent_id
	 * 		The parent group id.
	 * 		Leave empty (null) for root groups.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($description, $parent_id = NULL)
	{
		// Check if user has master access to parent group
		$result = developer::masterGroup($parent_id);
		if ($result === FALSE && !is_null($parent_id))
			return $result;
		
		// Create new group in database
		$dbc = new dbConnection();
		$dbq = new dbQuery("28968422602365", "modules.groups");
		
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
		
		// Check if group is empty of modules
		$gModules = module::getAllModules($groupID);
		if (count($gModules) > 0)
			return FALSE;
		
		// Check if group is empty of other groups
		$dbc = new dbConnection();
		$attr = array();
		$attr['id'] = $groupID;
		$dbq = new dbQuery("15503192604037", "modules.groups");
		$result = $dbc->execute($dbq, $attr);
		if ($dbc->get_num_rows($result) > 1)
			return FALSE;
		
		// Remove group from DB
		$attr = array();
		$attr['id'] = $groupID;
		$dbq = new dbQuery("16331534361504", "modules.groups");
		return $dbc->execute($dbq, $attr);
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
		$dbc = new dbConnection();
		$dbq = new dbQuery("30755936288205", "modules.groups");
		
		// Get Module Group Data
		$attr = array();
		$attr['gid'] = $groupID;
		$attr['description'] = $description;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>