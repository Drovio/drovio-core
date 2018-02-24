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
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Profile", "account");
importer::import("DEV", "Modules", "test/moduleTester");
importer::import("DEV", "Resources", "paths");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\filesystem\folderManager;
use \API\Profile\account;
use \DEV\Modules\test\moduleTester;
use \DEV\Resources\paths;

/**
 * Tester profile
 * 
 * Manages the tester profile.
 * 
 * @version	2.0-1
 * @created	October 22, 2014, 10:20 (BST)
 * @updated	December 18, 2015, 17:16 (GMT)
 */
class tester
{
	/**
	 * All the user's tester modules.
	 * 
	 * @type	array
	 */
	private static $testerModules = array();
	
	/**
	 * Checks if a user is tester for the given module.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function testerModule($moduleID)
	{
		// Validate account
		if (!account::validate())
			return FALSE;
		
		// Get workspace tester modules
		$testerModules = self::getWorkspaceModules();
		
		// Return status
		$testerActive = moduleTester::status($moduleID);
		return $testerActive && isset($testerModules[$moduleID]);
	}
	
	/**
	 * Get all modules in the tester's workspace.
	 * 
	 * @return	array
	 * 		An array of all modules' information.
	 */
	public static function getWorkspaceModules()
	{
		// Check cache
		if (empty(self::$testerModules))
		{
			// Get from database
			$dbq = new dbQuery("1164522233", "security.privileges.tester");
			self::$testerModules = self::getFromDB($dbq);
		}
		
		return self::$testerModules;
	}
	
	/**
	 * Gets the tester trunk for the connected account.
	 * 
	 * @return	mixed
	 * 		The trunk directory or NULL for guest accounts.
	 */
	public static function getTrunk()
	{
		// Check account
		if (!account::validate())
			return NULL;
			
		// Get trunk folder
		$trunkFolder = paths::getDevPath()."/TestingTrunk/usr.".account::getAccountID();
		
		// Create folder if doesn't exist
		if (!is_dir(systemRoot.$trunkFolder))
			folderManager::create(systemRoot.$trunkFolder);
		
		// Return Folder Path
		return $trunkFolder;
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