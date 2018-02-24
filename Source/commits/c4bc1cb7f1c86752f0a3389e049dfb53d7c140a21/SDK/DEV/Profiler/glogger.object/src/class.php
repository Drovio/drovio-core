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

importer::import("AEL", "Platform", "application");
importer::import("AEL", "Profiler", "logger");
importer::import("DEV", "Profiler", "clogger");

use \AEL\Platform\application;
use \AEL\Profiler\logger as appLogger;
use \DEV\Profiler\clogger;

/**
 * Global logger
 * 
 * Decides whether the code runs for core or for an application and logs accordingly.
 * 
 * @version	0.1-1
 * @created	May 22, 2015, 16:04 (EEST)
 * @updated	May 22, 2015, 16:04 (EEST)
 */
class glogger extends clogger
{
	/**
	 * Logs or inserts a log message to the pool.
	 * 
	 * @param	string	$message
	 * 		The log short message.
	 * 
	 * @param	string	$priority
	 * 		Priority indicator as defined by logger constants
	 * 
	 * @param	string	$description
	 * 		The log long description. It supports arrays and numbers. It is written with print_r().
	 * 
	 * @return	void
	 */
	public function log($message, $priority = parent::DEBUG, $description = "")
	{
		// Get whether this an application running
		$applicationID = application::init();
		if (!empty($applicationID))
			return appLogger::getInstance()->log($message, $priority, $description);
		
		// Log normally to core
		return parent::log($message, $priority, $description);
	}
}
//#section_end#
?>