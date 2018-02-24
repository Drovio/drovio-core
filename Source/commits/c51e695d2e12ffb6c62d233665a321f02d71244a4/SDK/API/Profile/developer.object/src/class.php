<?php
//#section#[header]
// Namespace
namespace API\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Security", "account");
importer::import("API", "Security", "privileges");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Comm", "database::connections::interDbConnection");

use \API\Security\account;
use \API\Security\privileges;
use \API\Model\units\sql\dbQuery;
use \API\Comm\database\connections\interDbConnection;

/**
 * Developer Profile
 * 
 * Manages the developer's profile.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 13:42 (EEST)
 * @revised	July 31, 2013, 13:42 (EEST)
 */
class developer
{
	private static $devModules = array();
	
	/**
	 * Checks if the developer has the given module in the workspace.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function moduleInWorkspace($moduleID)
	{
		// Initialize
		$dbq = new dbQuery("602589690", "security.privileges.developer");

		$attr = array();
		$attr["mid"] = $moduleID;
		return self::execute($dbq, $attr);
	}
	
	/**
	 * Checks if the developer has the given module group in the workspace.
	 * 
	 * @param	integer	$groupID
	 * 		The module group id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function moduleGroupInWorkspace($groupID)
	{
		// Initialize
		$dbq = new dbQuery("116211432", "security.privileges.developer");
		
		$attr = array();
		$attr["gid"] = $groupID;
		return self::execute($dbq, $attr);
	}
	
	/**
	 * Checks if the developer has the given module group in the workspace as master.
	 * 
	 * @param	integer	$groupID
	 * 		The module group id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function masterGroup($groupID)
	{
		// Check if group_id is NULL and user is DEVADMIN for manage root moduleGroups
		if (is_null($group_id) && privileges::get_userToGroup("DEVADMIN"))
			return TRUE;
			
		// Initialize
		$dbq = new dbQuery("642465987", "security.privileges.developer");
		
		$attr = array();
		$attr["gid"] = $groupID;
		return self::execute($dbq, $attr);
	}
	
	/**
	 * Execute the given query with the current user.
	 * 
	 * @param	dbQuery	$dbq
	 * 		The query to execute.
	 * 
	 * @param	array	$attr
	 * 		The query attributes.
	 * 
	 * @return	boolean
	 * 		The result status.
	 */
	private static function execute($dbq, $attr)
	{
		// Check account
		if (!account::validate())
			return FALSE;
			
		$attr["aid"] = account::getAccountID();

		// Initialize
		$dbc = new interDbConnection();
		$result = $dbc->execute($dbq, $attr);
		return $dbc->get_num_rows($result) > 0;
	}
}
//#section_end#
?>