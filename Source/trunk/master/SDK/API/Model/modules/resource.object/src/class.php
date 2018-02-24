<?php
//#section#[header]
// Namespace
namespace API\Model\modules;

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
 * @namespace	\modules
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Modules", "test/mrsrcTester");
importer::import("DEV", "Projects", "projectLibrary");
importer::import("DEV", "Resources", "paths");
importer::import("ESS", "Environment", "url");

use \API\Resources\filesystem\fileManager;
use \DEV\Modules\modulesProject;
use \DEV\Modules\test\mrsrcTester;
use \DEV\Projects\projectLibrary;
use \DEV\Resources\paths;
use \ESS\Environment\url;

/**
 * Modules resource reader.
 * 
 * This class is used to allow modules to access resources either from the project either from the production.
 * 
 * @version	1.0-1
 * @created	May 27, 2015, 8:35 (BST)
 * @updated	November 23, 2015, 15:14 (GMT)
 */
class resource
{
	/**
	 * Get a modules resource.
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
		// Get full resource path
		$resourcePath = self::getPath($resourcePath, $rootRelative = FALSE);
		
		// Get file content
		return fileManager::get($resourcePath);
	}
	
	/**
	 * Get a resolved url pointing to a pages resource file.
	 * 
	 * @param	string	$resourcePath
	 * 		The resource subpath from the resource root folder down.
	 * 
	 * @return	string
	 * 		The resource file url.
	 */
	public static function getURL($resourcePath)
	{
		// Get resource path
		$resourcePath = self::getPath($resourcePath);
			
		// Get resource path according to runtime
		if (mrsrcTester::status())
		{
			// Resolve url
			$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
			$resourceUrl = url::resolve("repo", $resourcePath);
		}
		else
		{
			// Resolve url
			$resourcePath = str_replace(paths::getLibraryPath(), "", $resourcePath);
			$resourceUrl = url::resolve("lib", $resourcePath);
		}
		
		// Return resource url
		return $resourceUrl;
	}
	
	/**
	 * Get a pages resource path.
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
		if (mrsrcTester::status())
		{
		
			// Get project's resource folder
			$project = new modulesProject();
			$resourceRootPath = $project->getResourcesFolder();
		}
		else
		{
			$version = projectLibrary::getLastProjectVersion(modulesProject::PROJECT_ID, $live = FALSE);
			$projectRootFolder = projectLibrary::getPublishedPath(modulesProject::PROJECT_ID, $version);
			$resourceRootPath = $projectRootFolder.projectLibrary::RSRC_FOLDER;
		}
		
		// Return root relative (or not) resource root path
		return ($rootRelative ? "" : systemRoot).$resourceRootPath."/".$resourcePath;
	}
}
//#section_end#
?>