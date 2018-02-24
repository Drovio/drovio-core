<?php
//#section#[header]
// Namespace
namespace AEL\Profiler;

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
 * @package	Profiler
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "log/activityLogger");
importer::import("DEV", "Projects", "project");
importer::import("AEL", "Platform", "application");

use \DEV\Profiler\log\activityLogger;
use \DEV\Projects\project;
use \AEL\Platform\application;

/**
 * Application File Logger
 * 
 * Logs application messages to project log files.
 * 
 * @version	0.1-1
 * @created	December 10, 2014, 14:52 (EET)
 * @revised	December 10, 2014, 14:52 (EET)
 */
class logger extends activityLogger
{
	/**
	 * The logger instance.
	 * 
	 * @type	logger
	 */
	private static $instance;
	
	/**
	 * Initialize the logger object.
	 * 
	 * @return	void
	 */
	private function __construct() {}
	
	/**
	 * Get the logger instance object.
	 * 
	 * @return	logger
	 * 		The logger instance object.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new logger();
		
		return self::$instance;
	}
	
	/**
	 * Get the application's log folder.
	 * 
	 * @return	string
	 * 		The application's log folder.
	 */
	protected function getLogFolder()
	{
		// Get application id to request
		$requestApplicationID = application::init();
		if (empty($requestApplicationID))
			return NULL;
		
		// Create project
		$project = new project($requestApplicationID);
		return $project->getRootFolder()."/Logs/";
	}
}
//#section_end#
?>