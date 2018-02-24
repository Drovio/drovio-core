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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("AEL", "Platform", "application");
importer::import("API", "Model", "core/manifests");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Apps", "appManifest");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \AEL\Platform\application as AELApplication;
use \API\Model\core\manifests as coreManifests;
use \API\Profile\account;
use \API\Profile\team;
use \API\Resources\DOMParser;
use \DEV\Apps\test\appTester;
use \DEV\Apps\appManifest;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Application Model Manager
 * 
 * Provides na interface for some basic functionality for applications.
 * 
 * @version	3.0-1
 * @created	December 5, 2014, 16:38 (EET)
 * @updated	May 12, 2015, 15:06 (EEST)
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
		$iconUrl = application::getApplicationIconUrl($applicationID, $version);
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
		// Get icon from published path
		$iconPath = projectLibrary::getPublishedPath($applicationID, $applicationVersion)."/resources/.assets/icon.png";
		if (file_exists(systemRoot.$iconPath))
		{
			$iconPath = str_replace(paths::getPublishedPath(), "", $iconPath);
			$iconUrl = url::resolve("lib", $iconPath);
			return $iconUrl;
		}
		
		return NULL;
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
		
		// Get 'service' folder inside the account foler
		$serviceName = self::getServiceName($applicationID);
		return team::getServicesFolder("/AppData/".$serviceName);
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
		
		// Get 'service' folder inside the account foler
		$serviceName = self::getServiceName($applicationID);
		return account::getServicesFolder("/AppData/".$serviceName);
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
	
	/**
	 * Get the application folder name as a service.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application service folder name.
	 */
	private static function getServiceName($applicationID)
	{
		return "app".md5("app_service_".$applicationID);
	}
}
//#section_end#
?>