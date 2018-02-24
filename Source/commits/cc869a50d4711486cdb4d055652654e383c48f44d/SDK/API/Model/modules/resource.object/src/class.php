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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("DEV", "Modules", "modulesProject");
importer::import("DEV", "Modules", "test/mrsrcTester");

use \API\Resources\filesystem\fileManager;
use \DEV\Modules\modulesProject;
use \DEV\Modules\test\mrsrcTester;

/**
 * Modules resource reader.
 * 
 * This class is used to allow modules to access resources either from the project either from the production.
 * 
 * @version	0.1-1
 * @created	May 27, 2015, 10:35 (EEST)
 * @updated	May 27, 2015, 10:35 (EEST)
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
		// Get resource path according to runtime
		if (mrsrcTester::status())
			$resourceRootPath = paths::getModulesRsrcPath();
		else
		{
			// Get project's resource folder
			$project = new modulesProject();
			$resourceRootPath = $project->getResourcesFolder();
		}
		
		// Get file content
		return fileManager::get(systemRoot.$resourceRootPath."/".$resourcePath);
	}
}
//#section_end#
?>