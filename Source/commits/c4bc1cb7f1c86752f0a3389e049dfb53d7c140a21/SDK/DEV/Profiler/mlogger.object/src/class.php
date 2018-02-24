<?php
//#section#[header]
// Namespace
namespace DEV\Profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profiler
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "logger");
importer::import("DEV", "Modules", "modulesProject");

use \DEV\Profiler\logger;
use \DEV\Modules\modulesProject;

/**
 * Modules logger
 * 
 * Logs messages for the modules project.
 * 
 * @version	0.1-1
 * @created	May 22, 2015, 16:03 (EEST)
 * @updated	May 22, 2015, 16:03 (EEST)
 */
class mlogger extends logger
{
	/**
	 * Get the modules project's log folder
	 * 
	 * @return	string
	 * 		The project's log folder.
	 */
	protected function getLogFolder()
	{
		// Get project's root folder
		$project = new modulesProject();
		return $project->getRootFolder()."/Logs/";
	}
}
//#section_end#
?>