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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("BSS", "Market", "appMarket");

use \AEL\Platform\application;
use \API\Resources\filesystem\fileManager;
use \DEV\Apps\test\appTester;
use \DEV\Projects\project;
use \DEV\Projects\projectLibrary;
use \BSS\Market\appMarket;

/**
 * Application resource reader
 * 
 * This class is used for the application to have access to its resources.
 * 
 * @version	0.1-2
 * @created	May 4, 2015, 20:03 (EEST)
 * @updated	May 5, 2015, 9:09 (EEST)
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
		if ($subdomain == "boss")
			$appVersion = appMarket::getTeamAppVersion($applicationID);
		else if ($subdomain == "apps")
			$appVersion = projectLibrary::getLastProjectVersion($applicationID);
			
		// Get version token published path
		return projectLibrary::getPublishedPath($applicationID, $appVersion)."/resources/";
	}
}
//#section_end#
?>