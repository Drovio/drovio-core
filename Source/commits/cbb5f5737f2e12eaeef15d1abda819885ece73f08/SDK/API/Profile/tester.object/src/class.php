<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");
importer::import("DEV", "Profiler", "test::moduleTester");
importer::import("DEV", "Resources", "paths");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;
use \API\Resources\filesystem\folderManager;
use \API\Security\account as sAccount;
use \DEV\Profiler\test\moduleTester;
use \DEV\Resources\paths;

/**
 * Tester profile
 * 
 * Manages the tester profile.
 * 
 * @version	0.1-2
 * @created	July 31, 2013, 13:50 (EEST)
 * @revised	October 4, 2014, 23:20 (EEST)
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
		if (isset(self::$testerModules[$moduleID]))
			return self::$testerModules[$moduleID];
		
		// Get status from database
		$testerModule = self::getDBTesterModule($moduleID);
		
		// Get Status
		$status = (moduleTester::status($moduleID) && $testerModule);
		
		// Set variable
		self::$testerModules[$moduleID] = $status;
		
		// Return status
		return $status;
	}
	
	/**
	 * Gets the tester trunk for the connected account.
	 * 
	 * @return	string
	 * 		The trunk directory.
	 */
	public static function getTrunk()
	{
		// Get trunk folder
		$trunkFolder = paths::getDevPath()."/TestingTrunk/usr.".sAccount::getAccountID();
		
		// Create folder if doesn't exist
		if (!is_dir(systemRoot.$trunkFolder))
			folderManager::create(systemRoot.$trunkFolder);
		
		// Return Folder Path
		return $trunkFolder;
	}
	
	/**
	 * Gets the tester status from the database.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	boolean
	 * 		Returns true if tester, false otherwise.
	 */
	private static function getDBTesterModule($moduleID)
	{
		// You are a tester as a developer and as a tester generally
		$dbc = new dbConnection();
		$dbq = new dbquery("1164522233", "security.privileges.tester");
		
		// Check account
		if (!sAccount::validate())
			return FALSE;
		
		$attr = array();
		$attr["mid"] = $moduleID;
		$attr["aid"] = sAccount::getAccountID();
		$access = $dbc->execute($dbq, $attr);
		$testerStatus = ($dbc->get_num_rows($access) > 0);
		
		// Get Status
		return ($testerStatus || developer::moduleInWorkspace($moduleID));
	}
}
//#section_end#
?>