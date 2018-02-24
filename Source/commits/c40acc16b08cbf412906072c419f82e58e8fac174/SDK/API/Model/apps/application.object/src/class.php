<?php
//#section#[header]
// Namespace
namespace API\Model\apps;

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
 * @package	Model
 * @namespace	\apps
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("AEL", "Platform", "application");
importer::import("API", "Model", "core/manifests");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Apps", "library/appScript");
importer::import("DEV", "Apps", "library/appStyle");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Apps", "appManifest");
importer::import("DEV", "Apps", "appSettings");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \ESS\Protocol\BootLoader;
use \AEL\Platform\application as AELApplication;
use \API\Model\core\manifests as coreManifests;
use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\folderManager;
use \API\Resources\settingsManager;
use \DEV\Apps\library\appScript;
use \DEV\Apps\library\appStyle;
use \DEV\Apps\test\appTester;
use \DEV\Apps\appManifest;
use \DEV\Apps\appSettings;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Application Model Manager
 * 
 * Provides na interface for some basic functionality for applications.
 * 
 * @version	7.0-1
 * @created	December 5, 2014, 14:38 (GMT)
 * @updated	November 16, 2015, 19:43 (GMT)
 */
class application
{
	/**
	 * Get all application manifest permissions from the application library path.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 
	 * @return	array
	 * 		An array of all manifests' information by id.
	 */
	public static function getApplicationManifests($applicationID, $version)
	{
		// Get all core manifests
		$coreManifests = coreManifests::getManifests();
		
		// Load application manifest file from published path
		$applicationPath = self::getApplicationPath($applicationID, $version);
		$parser = new DOMParser();
		try
		{
			$parser->load($applicationPath.appManifest::MANIFEST_FILE);
		}
		catch (Exception $ex)
		{
			return NULL;
		}
		
		// Get all manifest protected package permissions
		$appManifests = array();
		$permissions = $parser->evaluate("/manifest/permissions/perm");
		foreach ($permissions as $prm)
		{
			$mfID = $parser->attr($prm, "id");
			$appManifests[] = $coreManifests[$mfID];
		}
		
		// Return all manifests
		return $appManifests;
	}
	
	/**
	 * Get application settings manager according to runtime.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$applicationVersion
	 * 		The application version.
	 * 
	 * @return	settingsManager
	 * 		The settings manager object.
	 */
	public static function getAppSettings($applicationID, $applicationVersion = "")
	{
		// Check application id
		if (empty($applicationID))
			return NULL;
		
		// Get the app settings from repository or from the published version
		if (AELApplication::onDEV())
		{
			return new appSettings($applicationID);
		}
		else
		{
			// Load from the latest published version
			$applicationPath = self::getApplicationPath($applicationID, $applicationVersion);
			if (!$applicationPath)
				return NULL;
			return new settingsManager($applicationPath, $fileName = "settings", $rootRelative = TRUE);
		}
	}
	
	/**
	 * Gets the application's library path to the given version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 
	 * @return	string
	 * 		The application library path.
	 */
	public static function getApplicationPath($applicationID, $version)
	{
		// Get version token published path
		return projectLibrary::getPublishedPath($applicationID, $version);
	}
	
	/**
	 * Get application information for a given version regarding the release version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 		If empty, get the last published and approved version.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all application information including release title, project description, icon url and more.
	 */
	public static function getApplicationInfo($applicationID, $version = "")
	{
		// Get last project version
		if (empty($version))
			$version = projectLibrary::getLastProjectVersion($applicationID);
		
		// Check version
		if (empty($version))
			return NULL;
			
		// Get project release info
		$appInfo = projectLibrary::getProjectReleaseInfo($applicationID, $version);
		
		// Check release info
		if (empty($appInfo))
			return NULL;
		
		// Get icon and add to information
		$iconUrl = self::getApplicationIconUrl($applicationID, $version);
		if (!empty($iconUrl))
			$appInfo['icon_url'] = $iconUrl;
		
		// Return application list
		return $appInfo;
	}
	
	/**
	 * Get the application's icon url according to the given version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$applicationVersion
	 * 		The application version.
	 * 
	 * @return	mixed
	 * 		Returns the icon url or NULL if the application doesn't have an icon.
	 */
	public static function getApplicationIconUrl($applicationID, $applicationVersion)
	{
		// Get icon from project
		return projectLibrary::getProjectIconUrl($applicationID, $applicationVersion);
	}
	
	/**
	 * Get the application library css code.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$cssFileName
	 * 		The css file name from the application library.
	 * 
	 * @param	string	$applicationVersion
	 * 		The application version, for production mode.
	 * 
	 * @return	string
	 * 		The css content.
	 */
	public static function getApplicationLibraryCSS($applicationID, $cssFileName, $applicationVersion = "")
	{
		// Get the app settings from repository or from the published version
		if (!appTester::publisherLock())
		{
			$devAppStyle = new appStyle($applicationID, $cssFileName);
			return $devAppStyle->get();
			
		}
		
		// Load from production
		return BootLoader::loadResourceCSS($applicationID, $applicationID, $cssFileName, $applicationVersion);
	}
	
	/**
	 * Get the application library js code.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$jsFileName
	 * 		The js file name from the application library.
	 * 
	 * @param	string	$applicationVersion
	 * 		The application version, for production mode.
	 * 
	 * @return	string
	 * 		The js content.
	 */
	public static function getApplicationLibraryJS($applicationID, $jsFileName, $applicationVersion = "")
	{
		// Get the app settings from repository or from the published version
		if (!appTester::publisherLock())
		{
			$devAppScript = new appScript($applicationID, $jsFileName);
			return $devAppScript->get();
		}
		
		// Load from production
		return BootLoader::loadResourceJS($applicationID, $applicationID, $jsFileName, $applicationVersion);
	}
	
	/**
	 * Get the application service path inside the team folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 */
	public static function getTeamApplicationFolder()
	{
		// Get application id
		$applicationID = AELApplication::init();
		if (empty($applicationID))
			return NULL;
		
		// Get application folder inside the account folder
		$appFolder = self::getAppFolderName($applicationID);
		
		// Return the folder path
		return team::getServicesFolder("/AppData/".$appFolder);
	}
	
	/**
	 * Get the application service path inside the account folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 */
	public static function getAccountApplicationFolder()
	{
		// Get application id
		$applicationID = AELApplication::init();
		if (empty($applicationID))
			return NULL;
		
		// Get application folder inside the account folder
		$appFolder = self::getAppFolderName($applicationID);
		
		// Return the folder path
		return account::getServicesFolder("/AppData/".$appFolder);
	}
	
	/**
	 * Get the application folder name as a service.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application service folder name.
	 */
	public static function getAppFolderName($applicationID)
	{
		return "app".md5("app_service_".$applicationID);
	}
	
	/**
	 * Get the application service path inside the team folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 * 
	 * @deprecated	Use getTeamApplicationFolder() instead.
	 */
	public static function getTeamApplicationPath()
	{
		return self::getTeamApplicationFolder();
	}
	
	/**
	 * Get the application service path inside the account folder.
	 * 
	 * @return	mixed
	 * 		The application path or NULL if there is no active application.
	 * 
	 * @deprecated	Use getAccountApplicationFolder() instead.
	 */
	public static function getAccountApplicationPath()
	{
		return self::getAccountApplicationFolder();
	}
}
//#section_end#
?>