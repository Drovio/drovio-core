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

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "developer");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Security", "account");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\profiler\tester as devTester;
use \API\Model\units\sql\dbQuery;
use \API\Profile\developer;
use \API\Resources\storage\session;
use \API\Resources\filesystem\folderManager;
use \API\Security\account;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Tester profile
 * 
 * Manages the tester profile.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 13:50 (EEST)
 * @revised	August 9, 2013, 15:07 (EEST)
 */
class tester
{
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
			
		/*
		// Get session status
		$testerModule = self::getSessionTester($moduleID);

		// If not, get from database
		if (is_null($testerModule))
		{
			$testerModule = self::getDBTesterModule($moduleID);

			// If tester, store to session
			if ($testerModule)
				self::setSessionTester($moduleID);
		}*/
		
		// Get status from database
		$testerModule = self::getDBTesterModule($moduleID);
		
		// Get Status
		$status = (devTester::status() && $testerModule);
		
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
	
	/**
	 * Gets the tester status from the session.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to check.
	 * 
	 * @return	boolean
	 * 		Returns true if tester, false otherwise.
	 */
	private static function getSessionTester($moduleID)
	{
		// Get all module access
		$testerModules = session::get("testerModules", NULL, "developer");
		
		// Return this module's access (if any)
		if (!is_null($testerModules))
			return $testerModules[$moduleID];
		
		// Return NULL otherwise
		return NULL;
	}
	
	/**
	 * Sets the tester status to the session.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to set.
	 * 
	 * @return	void
	 */
	private static function setSessionTester($moduleID)
	{
		// Get all tester modules
		$testerModules = session::get("testerModules", NULL, "developer");
		if (is_null($testerModules))
			$testerModules = array();
		
		// Set module access
		$testerModules[$moduleID] = TRUE;
		session::set("testerModules", $testerModules, "developer");
	}
}
//#section_end#
?>