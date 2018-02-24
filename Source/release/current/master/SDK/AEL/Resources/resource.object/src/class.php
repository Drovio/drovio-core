<?php
//#section#[header]
// Namespace
namespace AEL\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "url");
importer::import("AEL", "Platform", "application");
importer::import("API", "Model", "apps/appSessionManager");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");
importer::import("BSS", "Market", "appMarket");

use \ESS\Environment\url;
use \AEL\Platform\application;
use \API\Model\apps\appSessionManager;
use \API\Resources\filesystem\fileManager;
use \DEV\Apps\test\appTester;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;
use \BSS\Market\appMarket;

/**
 * Application resource reader
 * 
 * This class is used for the application to have access to its resources.
 * 
 * @version	1.0-3
 * @created	May 4, 2015, 18:03 (BST)
 * @updated	November 23, 2015, 15:14 (GMT)
 */
class resource
{
	/**
	 * Get an application resource.
	 * This function will decide whether to load the resource from the project or from the published path.
	 * 
	 * @param	string	$resourcePath
	 * 		The resource subpath from the resource root folder down.
	 * 
	 * @return	mixed
	 * 		The resource content.
	 */
	public static function get($resourcePath)
	{
		// Get application path according to runtime
		if (appTester::publisherLock())
			$applicationPath = self::getApplicationPath();
		else
		{
			// Get application id
			$applicationID = application::init();
			
			// Get project's resource folder
			$project = new project($applicationID);
			$applicationPath = $project->getResourcesFolder();
		}
		
		// Get file content
		return fileManager::get(systemRoot.$applicationPath."/".$resourcePath);
	}
	
	/**
	 * Get a resolved url pointing to an application resource file.
	 * 
	 * @param	string	$resourcePath
	 * 		The resource subpath from the resource root folder down.
	 * 
	 * @return	string
	 * 		The resource file url.
	 */
	public static function getURL($resourcePath)
	{
		// Get application path according to runtime
		if (appTester::publisherLock())
		{
			// Get production application path
			$applicationPath = self::getApplicationPath();
			
			// Resolve url
			$applicationPath = str_replace(paths::getLibraryPath(), "", $applicationPath);
			$resourceUrl = url::resolve("lib", $applicationPath);
		}
		else
		{
			// Get application id
			$applicationID = application::init();
			
			// Get project's resource folder
			$project = new project($applicationID);
			$applicationPath = $project->getResourcesFolder();
			
			// Resolve url
			$applicationPath = str_replace(paths::getRepositoryPath(), "", $applicationPath);
			$resourceUrl = url::resolve("repo", $applicationPath);
		}
		
		// Return resource url
		return $resourceUrl."/".$resourcePath;
	}
	
	/**
	 * Gets the application's library path to the latest version, according to current running domain.
	 * 
	 * @return	string
	 * 		The application library path.
	 */
	private static function getApplicationPath()
	{
		// Get application id
		$applicationID = application::init();
		
		// Get subdomain where the application is running
		$subdomain = appTester::currentDomain();
		
		// Get team's or app's running version according to subdomain
		if ($subdomain == "apps")
			$appVersion = appSessionManager::getInstance()->getVersion($applicationID);
		else
			$appVersion = appMarket::getTeamAppVersion($applicationID);
			
		// Get version token published path
		return projectLibrary::getPublishedPath($applicationID, $appVersion).projectLibrary::RSRC_FOLDER;
	}
}
//#section_end#
?>