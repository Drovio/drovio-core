<?php
//#section#[header]
// Namespace
namespace DEV\Profile;

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
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "account");
importer::import("API", "Security", "privileges");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Security\privileges;

/**
 * Developer Profile
 * 
 * Manages the developer's profile.
 * 
 * @version	0.1-2
 * @created	October 22, 2014, 12:18 (EEST)
 * @revised	January 2, 2015, 10:20 (EET)
 */
class developer
{
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
	 * @return	void
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
		$dbc = new dbConnection();
		$result = $dbc->execute($dbq, $attr);
		return $dbc->get_num_rows($result) > 0;
	}
}
//#section_end#
?>