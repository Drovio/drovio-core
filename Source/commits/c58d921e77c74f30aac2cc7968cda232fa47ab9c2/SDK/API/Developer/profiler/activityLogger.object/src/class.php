<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

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
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "log::activityLogger");

use \DEV\Profiler\log\activityLogger as DEVActivityLogger;

/**
 * System Activity Logger
 * 
 * Logs system activity for later analysis.
 * Only important logs!
 * 
 * @version	{empty}
 * @created	August 10, 2013, 12:51 (EEST)
 * @revised	February 11, 2014, 11:09 (EET)
 * 
 * @deprecated	Use DEV\Profiler\log\activityLogger instead.
 */
class activityLogger extends DEVActivityLogger
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $logTime;
	
	/**
	 * Creates a new entry log.
	 * 
	 * @param	{type}	$time
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($time = "")
	{
		$this->logTime = (empty($time) ? time() : $time);
	}
	
	/**
	 * Creates the log file for the day.
	 * 
	 * @return	void
	 */
	protected function getLogFile()
	{
		return "/activity/log_".date("Y-m-d", $this->logTime).".xml";
	}
}
//#section_end#
?>