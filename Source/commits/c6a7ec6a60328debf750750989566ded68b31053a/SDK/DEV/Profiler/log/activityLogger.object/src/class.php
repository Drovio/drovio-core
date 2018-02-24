<?php
//#section#[header]
// Namespace
namespace DEV\Profiler\log;

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
 * @namespace	\log
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Profiler", "logger");

use \API\Resources\filesystem\fileManager;
use \DEV\Profiler\logger;

/**
 * Abstract System Activity Logger
 * 
 * Logs activity to a given folder.
 * Keeps logs per day in separate files.
 * 
 * @version	0.2-1
 * @created	February 10, 2014, 12:29 (EET)
 * @revised	December 10, 2014, 13:59 (EET)
 */
abstract class activityLogger
{
	/**
	 * The system is unusable.
	 * 
	 * @type	integer
	 */
	const EMERGENCY = 1;
	/**
	 * Action must be taken immediately.
	 * 
	 * @type	integer
	 */
	const ALERT = 2;
	/**
	 * Critical conditions.
	 * 
	 * @type	integer
	 */
	const CRITICAL = 4;
	/**
	 * Error conditions.
	 * 
	 * @type	integer
	 */
	const ERROR = 8;
	/**
	 * Warning conditions.
	 * 
	 * @type	integer
	 */
	const WARNING = 16;
	/**
	 * Normal, but significant condition.
	 * 
	 * @type	integer
	 */
	const NOTICE = 32;
	/**
	 * Informational message.
	 * 
	 * @type	integer
	 */
	const INFO = 64;
	/**
	 * Debugging message.
	 * 
	 * @type	integer
	 */
	const DEBUG = 128;
	
	/**
	 * Gets the log folder for the activity.
	 * It is abstract to let the inherited class decide the folder of its preference.
	 * 
	 * @return	string
	 * 		The full log file path (without systemRoot).
	 */
	abstract protected function getLogFolder();
	
	/**
	 * Creates a new entry log.
	 * 
	 * @param	string	$description
	 * 		The log description.
	 * 
	 * @param	integer	$level
	 * 		The log level.
	 * 		Use the class constants.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function log($description, $level = self::DEBUG)
	{
		// Get txt log file
		$logFile = $this->getLogFile();
		
		// Check if file exists and create it
		if (!file_exists(systemRoot.$logFile))
			fileManager::create(systemRoot.$logFile, "", TRUE);
		
		// Set timestamp
		$timestamp = date('D M d, Y, H:i:s', time());
		
		// Get log type
		$type = logger::getLevelName($level);
		if (empty($type))
			$type = logger::getLevelName(self::DEBUG);
		
		// Set full description
		$description = trim($description);
		$descriptionFull = "[".$timestamp."] [".$type."] ".$description."\n";
		
		// Add new log entry
		return fileManager::put(systemRoot.$logFile, $description, FILE_APPENDÂ |Â LOCK_EX);
	}
	
	/**
	 * Get all logs for the given day.
	 * 
	 * @param	integer	$day
	 * 		The relative day interval in the past, starting from 0 as today, to get the logs.
	 * 		0: today
	 * 		1: yesterday
	 * 		2: day before yesterday
	 * 		...
	 * 
	 * @return	string
	 * 		All logs of the given day.
	 */
	public function getLogs($day = 0)
	{
		// Get relative day
		$timestamp = time() - ($day * 60 * 60 * 24);
		
		// Get log file
		$logFile = $this->getLogFile($timestamp);
		
		// Return all logs
		return fileManager::get(systemRoot.$logFile);
	}
	
	/**
	 * Get the log file according to the given time.
	 * 
	 * @param	integer	$time
	 * 		The relative timestamp.
	 * 		Leave NULL for current day.
	 * 		Add timestamp for past days.
	 * 		It is NULL by default.
	 * 
	 * @return	string
	 * 		The full log file path.
	 */
	private function getLogFile($time = NULL)
	{
		// Get the log folder from the inherited class
		$logFolder = $this->getLogFolder();
		
		// Create a log file per day
		$time = (empty($time) ? time() : $time);
		$logFile = date("Y-m-d", $time);
		
		// Get the entire path
		return $logFolder."/".$logFile.".txt";
	}
}
//#section_end#
?>