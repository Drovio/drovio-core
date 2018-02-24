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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Profile
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::moduleTester");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\moduleTester;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;

/**
 * Tester profile
 * 
 * Manages the tester profile.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 13:50 (EEST)
 * @revised	December 20, 2013, 19:09 (EET)
 */
class tester
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
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
		// Get user profile
		$accountID = account::getAccountID();
		if (empty($accountID))
			$trunkFolder = paths::getDevPath()."/TestingTrunk/usr.guest";
		else
			$trunkFolder = paths::getDevPath()."/TestingTrunk/usr.".$accountID;
		
		// Create folder if doesn't exist
		if (!is_dir(systemRoot.$trunkFolder))
			folderManager::create(systemRoot.$trunkFolder);
		
		// Get Folder Path
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
		$dbc = new interDbConnection();
		$dbq = new dbquery("1164522233", "security.privileges.tester");
		
		// Check account
		if (!account::validate())
			return FALSE;
		
		$attr = array();
		$attr["mid"] = $moduleID;
		$attr["aid"] = account::getAccountID();
		$access = $dbc->execute($dbq, $attr);
		$testerStatus = ($dbc->get_num_rows($access) > 0);
		
		// Get Status
		return ($testerStatus || developer::moduleInWorkspace($moduleID));
	}
}
//#section_end#
?>