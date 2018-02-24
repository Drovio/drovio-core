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
importer::import("DEV", "Core", "coreProject");

use \DEV\Profiler\logger;
use \DEV\Core\coreProject;

/**
 * Core logger
 * 
 * Logs messages for the core project.
 * 
 * @version	0.1-1
 * @created	May 22, 2015, 16:03 (EEST)
 * @updated	May 22, 2015, 16:03 (EEST)
 */
class clogger extends logger
{
	/**
	 * Get the core project's log folder
	 * 
	 * @return	string
	 * 		The project's log folder.
	 */
	protected function getLogFolder()
	{
		// Get project's root folder
		$project = new coreProject();
		return $project->getRootFolder()."/Logs/";
	}
}
//#section_end#
?>