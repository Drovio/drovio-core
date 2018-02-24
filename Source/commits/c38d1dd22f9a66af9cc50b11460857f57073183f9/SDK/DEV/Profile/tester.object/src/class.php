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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
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
 * @version	1.1-1
 * @created	October 22, 2014, 12:20 (EEST)
 * @updated	June 22, 2015, 11:33 (EEST)
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
		
		// Check cache
		if (empty(self::$testerModules))
			self::$testerModules = self::getDBTesterModule();
		
		// Return status
		$testerActive = moduleTester::status($moduleID);
		return $testerActive && self::$testerModules[$moduleID];
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
	 * Gets the tester status from the database.
	 * 
	 * @return	boolean
	 * 		Returns true if tester, false otherwise.
	 */
	private static function getDBTesterModule()
	{
		// Check account
		if (!account::validate())
			return FALSE;
		
		// Initialize result
		$testerStatus = array();
			
		// You are a tester as a developer and as a tester generally
		$dbc = new dbConnection();
		$dbq = new dbquery("1164522233", "security.privileges.tester");
		
		$attr = array();
		$attr["aid"] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		while ($row = $dbc->fetch($result))
			$testerStatus[$row['id']] = TRUE;
		
		// Get Status
		return $testerStatus;
	}
}
//#section_end#
?>