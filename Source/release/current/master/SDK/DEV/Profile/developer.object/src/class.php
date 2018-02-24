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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
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
 * @version	1.0-1
 * @created	October 22, 2014, 10:18 (BST)
 * @updated	December 18, 2015, 17:16 (GMT)
 */
class developer
{
	/**
	 * Modules in workspace.
	 * 
	 * @type	array
	 */
	private static $mWork = array();
	
	/**
	 * Module groups in workspace.
	 * 
	 * @type	array
	 */
	private static $mgWork = array();
	
	/**
	 * Module groups in master workspace.
	 * 
	 * @type	array
	 */
	private static $gmasterWork = array();
	
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
		$workspace = self::getWorkspaceModules();
		return isset($workspace[$moduleID]);
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
		// Check cache
		if (empty(self::$mgWork))
		{
			// Get from database
			$dbq = new dbQuery("116211432", "security.privileges.developer");
			self::$mgWork = self::getFromDB($dbq);
		}
		
		return self::$mgWork[$groupID];
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
		// Check cache
		if (empty(self::$gmasterWork))
		{
			// Get from database
			$dbq = new dbQuery("642465987", "security.privileges.developer");
			self::$gmasterWork = self::getFromDB($dbq);
		}
		
		return self::$gmasterWork[$groupID];
	}
	
	/**
	 * Get all modules in the developer's workspace.
	 * 
	 * @return	array
	 * 		An array of all modules' information.
	 */
	public static function getWorkspaceModules()
	{
		// Check cache
		if (empty(self::$mWork))
		{
			// Get from database
			$dbq = new dbQuery("602589690", "security.privileges.developer");
			self::$mWork = self::getFromDB($dbq);
		}
		
		return self::$mWork;
	}
	
	/**
	 * Execute the given query with the current user.
	 * 
	 * @param	dbQuery	$dbq
	 * 		The query to execute.
	 * 
	 * @return	mixed
	 * 		The result status.
	 */
	private static function getFromDB($dbq)
	{
		// Check account
		if (!account::validate())
			return array();
		
		// Add current account as attribute
		$attr = array();
		$attr["aid"] = account::getAccountID();
		
		// Initialize result
		$dbData = array();

		// Initialize db connection and fetch data
		$dbc = new dbConnection();
		$result = $dbc->execute($dbq, $attr);
		while ($row = $dbc->fetch($result))
			$dbData[$row['id']] = $row;
		
		return $dbData;
	}
}
//#section_end#
?>