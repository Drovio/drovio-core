<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter;

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
 * @package	Developer
 * @namespace	\appcenter
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */
/*
importer::import("ESS", "Protocol", "server::RuntimeException");
importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Developer", "appcenter::appManager");
importer::import("API", "Developer", "versionControl::vcsManager");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "ServiceManager");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "settingsManager");

use \ESS\Protocol\server\RuntimeException;
use \API\Comm\database\connections\interDbConnection;
use \API\Developer\appcenter\application;
use \API\Developer\appcenter\appManager;
use \API\Developer\versionControl\vcsManager;
use \API\Developer\profiler\tester;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Profile\ServiceManager;
use \API\Security\account;
use \API\Resources\storage\session;
use \API\Resources\storage\cookies;
use \API\Resources\filesystem\fileManager;
use \API\Resources\settingsManager;
*/
/**
 * Application Player
 * 
 * Runs the application for the first time.
 * 
 * @version	{empty}
 * @created	November 2, 2013, 17:51 (EET)
 * @revised	April 6, 2014, 23:45 (EEST)
 * 
 * @deprecated	Use \DEV\Apps\appPlayer instead.
 */
class appPlayer
{
	/**
	 * The application data.
	 * 
	 * @type	array
	 */
	private static $applicationData;
	
	/**
	 * Initializes the application importer.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		$importerPath = "/System/Library/devKit/appCenter/ACL/Platform/importer.php";
		$importerDevPath = "/.developer/Repository/Library/devKit/appCenter/trunk/master/ACL/Platform/importer.object/src/class.php";
		importer::req($importerDevPath, $root = TRUE, $once = TRUE);
	}
	
	/**
	 * Gets the application data by application id.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	array
	 * 		The application data in array.
	 */
	public static function getApplicationData($appID)
	{
		if (!isset(self::$applicationData))
		{
			// Load application data
			$dbc = new interDbConnection();
			$q = new dbQuery("1280168054", "apps");
			$attr = array();
			$attr['id'] = $appID;
			$result = $dbc->execute($q, $attr);
			self::$applicationData = $dbc->fetch($result);
		}
		
		return self::$applicationData;
	}
	
	/**
	 * Checks if the user running this application is tester.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True if user is author, false otherwise.
	 */
	public static function isTester($appID)
	{
		self::getApplicationData($appID);
		return (self::$applicationData['authorID'] == account::getAccountID());
	}
	
	/**
	 * Checks if the application is in tester mode for running in appCenter.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True if tester mode is enabled and user is tester, false otherwise.
	 */
	public static function testerStatus($appID)
	{
		$cookie = cookies::get("appTester");
		$testerCookie = (empty($cookie) ? FALSE : TRUE);
		
		$testerStatus = TRUE;
		if (isset($appID))
			$testerStatus = self::isTester($appID);
		
		return $testerCookie && $testerStatus;
	}
	
	/**
	 * Activates the appCenter tester mode.
	 * 
	 * @return	void
	 */
	public static function activateTester()
	{
		return cookies::set("appTester", TRUE);
	}
	
	/**
	 * Deactivates the appCenter tester mode.
	 * 
	 * @return	void
	 */
	public static function deactivateTester()
	{
		return cookies::delete("appTester");
	}
	
	/**
	 * Gets the content of the requested view of the running application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	string
	 * 		The view content.
	 */
	public static function getView($appID, $viewName = "")
	{
		$viewPath = self::getViewPath($appID, $viewName);
		$viewHTML = $viewPath."view.html";

		return trim(fileManager::get(systemRoot.$viewHTML));
	}
	
	/**
	 * Runs the requested view php code for the running application.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 
	 * @return	mixed
	 * 		The php code return value.
	 */
	public static function play($appID, $viewName = "")
	{
		try
		{
			$viewPath = self::getViewPath($appID, $viewName);
			$appPath = $viewPath."view.php";
	
			return importer::incl($appPath, $root = TRUE, $once = FALSE);
		}
		catch (Exception $ex)
		{
			// Catches the exception thrown by the programmer's code
			return RuntimeException::get($ex);
		}
	}
	
	/**
	 * Gets the application's developer folder path to the view.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The view name.
	 * 		If empty, the player loads the startup view from the application settings.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	private static function getViewPath($appID, $viewName = "")
	{
		if (empty($viewName))
		{
			// Get Settings
			$appSettings = self::getAppSettings($appID);
			$viewName = $appSettings->get("STARTUP_VIEW");
		}
		
		// Get Run Path
		$appPath = self::getApplicationRunPath($appID);
		return $appPath."/views/".$viewName.".view/";
	}
	
	/**
	 * Gets the settings manager for the application.
	 * Decides whether to load the settings from the developer folder or the published folder.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	settingsManager
	 * 		The appSettings or settingsManager object.
	 */
	public static function getAppSettings($appID)
	{
		if (self::testerStatus($appID))
		{
			// Initialize application
			$app = new application($appID);
			
			// Get Settings
			return $app->getSettings();
		}
		else
		{
			$appPath = self::getApplicationRunPath($appID)."/config/";
			return new settingsManager($appPath, $fileName = "Settings", $rootRelative = TRUE);
		}
	}
	
	/**
	 * Gets the application's publish folder path to the startup view.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The view path.
	 */
	public static function getApplicationRunPath($appID)
	{
		$tester = self::testerStatus();
		
		if ($tester)
		{
			// Load view content
			$appMan = new appManager();
			$devPath = $appMan->getDevAppFolder($appID);
			return $devPath."/.repository/trunk/master/";
		}
		else
			return appManager::getPublishedAppFolder($appID);
	}
}
//#section_end#
?>