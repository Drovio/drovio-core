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
importer::import("DEV", "Modules", "modulesProject");

use \DEV\Profiler\logger;
use \DEV\Modules\modulesProject;

/**
 * Modules logger
 * 
 * Logs messages for the modules project.
 * 
 * @version	1.0-1
 * @created	May 22, 2015, 14:03 (BST)
 * @updated	October 30, 2015, 17:57 (GMT)
 */
class mlogger extends logger
{
	/**
	 * The mlogger instance.
	 * 
	 * @type	mlogger
	 */
	private static $instance;
	
	/**
	 * Get the static mlogger instance.
	 * 
	 * @return	mlogger
	 * 		The mlogger instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new mlogger();
		
		return self::$instance;
	}
	
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