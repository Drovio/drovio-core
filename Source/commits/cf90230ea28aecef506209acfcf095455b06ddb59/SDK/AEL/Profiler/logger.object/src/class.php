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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * @version	0.2-2
 * @created	December 10, 2014, 14:52 (EET)
 * @updated	March 12, 2015, 13:17 (EET)
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
	 * The application id to log for.
	 * 
	 * @type	integer
	 */
	private $applicationID;
	
	/**
	 * Initialize the logger object.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id to get the instance for.
	 * 		If there is a running application already, this will be ignored.
	 * 
	 * @return	void
	 */
	private function __construct($applicationID = "")
	{
		// Initialize application
		$runningApplicationID = application::init();
		$runningApplicationID = (empty($runningApplicationID) ? $applicationID : $runningApplicationID);
		$this->applicationID = $runningApplicationID;
	}
	
	/**
	 * Get the logger instance object.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id to get the instance for.
	 * 		If there is a running application already, this will be ignored.
	 * 
	 * @return	logger
	 * 		The logger instance object.
	 */
	public static function getInstance($applicationID = "")
	{
		if (!isset(self::$instance))
			self::$instance = new logger($applicationID);
		
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
		// Check current application id
		if (empty($this->applicationID))
			return NULL;

		// Create project
		$project = new project($this->applicationID);
		return $project->getRootFolder()."/Logs/";
	}
}
//#section_end#
?>