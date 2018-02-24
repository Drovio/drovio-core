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

use \API\Resources\filesystem\fileManager;
use \DEV\Core\coreProject;
use \DEV\Core\test\rsrcTester;

/**
 * Core resource reader.
 * 
 * This class is used to allow sdk objects to access resources either from the project either from the production.
 * 
 * @version	0.1-3
 * @created	May 27, 2015, 10:32 (EEST)
 * @updated	May 29, 2015, 14:59 (EEST)
 */
class resource
{
	/**
	 * Get a core resource.
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
		// Get resource path according to runtime
		if (rsrcTester::status())
			$resourceRootPath = paths::getSDKRsrcPath();
		else
		{
			// Get project's resource folder
			$project = new coreProject();
			$resourceRootPath = $project->getResourcesFolder()."/resources";
		}
		
		// Get file content
		return fileManager::get(systemRoot.$resourceRootPath."/".$resourcePath);
	}
}
//#section_end#
?>