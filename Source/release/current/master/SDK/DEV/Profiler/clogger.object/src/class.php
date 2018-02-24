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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
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
 * @version	1.0-1
 * @created	May 22, 2015, 14:03 (BST)
 * @updated	October 30, 2015, 17:56 (GMT)
 */
class clogger extends logger
{
	/**
	 * The clogger instance.
	 * 
	 * @type	clogger
	 */
	private static $instance;
	
	/**
	 * Get the static clogger instance.
	 * 
	 * @return	clogger
	 * 		The clogger instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new clogger();
		
		return self::$instance;
	}
	
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