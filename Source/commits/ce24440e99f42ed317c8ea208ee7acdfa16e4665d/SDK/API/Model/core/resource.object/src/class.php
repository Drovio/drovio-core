<?php
//#section#[header]
// Namespace
namespace API\Model\core;

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
 * @namespace	\core
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Core", "coreProject");
importer::import("DEV", "Core", "test/rsrcTester");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");

use \API\Resources\filesystem\fileManager;
use \DEV\Core\coreProject;
use \DEV\Core\test\rsrcTester;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;

/**
 * Core resource reader.
 * 
 * This class is used to allow sdk objects to access resources either from the project either from the production.
 * 
 * @version	1.0-1
 * @created	May 27, 2015, 10:32 (EEST)
 * @updated	June 8, 2015, 15:28 (EEST)
 */
class resource
{
	/**
	 * Get a core resource.
	 * 
	 * @param	string	$resourcePath
	 * 		The resource subpath from the resource root folder down.
	 * 
	 * @return	mixed
	 * 		The resource content.
	 */
	public static function get($resourcePath)
	{
		// Get resource path
		$resourceRootPath = self::getPath($resourcePath);
		
		// Get file content
		return fileManager::get(systemRoot.$resourceRootPath."/".$resourcePath);
	}
	
	/**
	 * Get a core resource path.
	 * This function will decide whether to get the resource path from the project or from the published path.
	 * 
	 * @param	string	$resourcePath
	 * 		The resource subpath from the resource root folder down.
	 * 
	 * @param	boolean	$rootRelative
	 * 		Indicates whether the path is system root relative or absolute.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		The resource full path.
	 */
	public static function getPath($resourcePath, $rootRelative = TRUE)
	{
		// Get resource path according to runtime
		if (rsrcTester::status())
		{
			// Get project's resource folder
			$project = new coreProject();
			$resourceRootPath = $project->getResourcesFolder();
		}
		else
		{
			$version = projectLibrary::getLastProjectVersion(coreProject::PROJECT_ID, $live = FALSE);
			$projectRootFolder = projectLibrary::getPublishedPath(coreProject::PROJECT_ID, $version);
			$resourceRootPath = $projectRootFolder.projectLibrary::RSRC_FOLDER;
		}
		
		// Return root relative (or not) resource root path
		return ($rootRelative ? "" : systemRoot).$resourceRootPath;
	}
}
//#section_end#
?>