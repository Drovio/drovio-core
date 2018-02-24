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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/directory");
importer::import("DEV", "Profiler", "logger");

use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\directory;
use \DEV\Profiler\logger;

/**
 * Abstract System Activity Logger
 * 
 * Logs activity to a given folder.
 * Keeps logs per day in separate files.
 * 
 * @version	3.1-1
 * @created	February 10, 2014, 10:29 (GMT)
 * @updated	October 30, 2015, 16:17 (GMT)
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
	 * @param	string	$message
	 * 		The log message.
	 * 
	 * @param	integer	$level
	 * 		The log level.
	 * 		Use the class constants.
	 * 
	 * @param	string	$description
	 * 		The log long description.
	 * 		It supports arrays and numbers.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function log($message, $level = self::DEBUG, $description = "")
	{
		// Get txt log file
		$logFile = $this->getLogFileByTime();
		if (empty($logFile))
			return FALSE;
		
		// Check if file exists and create it
		if (!file_exists(systemRoot.$logFile))
			fileManager::create(systemRoot.$logFile, "", TRUE);
		
		// Set timestamp
		$timestamp = date('D M d, Y, H:i:s', time());
		
		// Get log type
		$type = self::getLevelName($level);
		if (empty($type))
			$type = self::getLevelName(self::DEBUG);
		
		// Set full description
		$message = trim($message);
		$descriptionFull = "[".$timestamp."] [".$type."] ".print_r($message, TRUE)."\n";
		if (!empty($description))
			$descriptionFull .= trim(print_r($description, TRUE))."\n";
		
		// Add new log entry
		return fileManager::put(systemRoot.$logFile, $descriptionFull, FILE_APPEND | LOCK_EX);
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
		$logFile = $this->getLogFileByTime($timestamp);
		
		// Return all logs
		return fileManager::get(systemRoot.$logFile);
	}
	
	/**
	 * Get all logs from the given file name.
	 * 
	 * @param	string	$filename
	 * 		The filename to get the logs for, extension not inluced.
	 * 		If empty, get the current day's log.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		All logs of the given file.
	 */
	public function getLogsByFile($filename = "")
	{
		// Get log file
		$logFile = $this->getLogFileByName($filename);

		// Return all logs
		return fileManager::get(systemRoot.$logFile);
	}
	
	/**
	 * Remove a file log.
	 * 
	 * @param	string	$filename
	 * 		The filename to remove, extension not inluced.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeLogByFile($filename)
	{
		// Get log file
		$logFile = $this->getLogFileByName($filename);

		// Return all logs
		return fileManager::remove(systemRoot.$logFile);
	}
	
	/**
	 * Get all log files in the given directory.
	 * 
	 * @return	array
	 * 		An array of all log files with their details.
	 */
	public function getLogFiles()
	{
		// Get the log folder from the inherited class
		$logFolder = $this->getLogFolder();
		if (empty($logFolder))
			return array();
		
		// Get all log files inside that folder
		$logList = directory::getContentDetails(systemRoot.$logFolder, $includeHidden = FALSE);
		return $logList['files'];
	}
	
	/**
	 * Gets the level name given the level code.
	 * 
	 * @param	integer	$level
	 * 		The log level code.
	 * 
	 * @return	mixed
	 * 		The level name or NULL if given level is not valid.
	 */
	public static function getLevelName($level)
	{
		switch ($level)
		{
			case self::EMERGENCY:
				return "emergency";
				break;
			case self::ALERT:
				return "alert";
				break;
			case self::CRITICAL:
				return "critical";
				break;
			case self::ERROR:
				return "error";
				break;
			case self::WARNING:
				return "warning";
				break;
			case self::NOTICE:
				return "notice";
				break;
			case self::INFO:
				return "info";
				break;
			case self::DEBUG:
				return "debug";
				break;
		}
		
		// No valid level
		return NULL;
	}
	
	/**
	 * Get all logger priority levels.
	 * 
	 * @return	array
	 * 		An array of levels by level code and description.
	 */
	public static function getLevels()
	{
		$levels = array();
		$levels[self::DEBUG] = "Debug";
		$levels[self::INFO] = "Info";
		$levels[self::NOTICE] = "Notice";
		$levels[self::WARNING] = "Warning";
		$levels[self::ERROR] = "Error";
		$levels[self::CRITICAL] = "Critical";
		$levels[self::ALERT] = "Alert";
		$levels[self::EMERGENCY] = "Emergency";
		
		return $levels;
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
	private function getLogFileByTime($time = NULL)
	{
		// Get the log folder from the inherited class
		$logFolder = $this->getLogFolder();
		if (empty($logFolder))
			return NULL;
		
		// Create a log file per day
		$time = (empty($time) ? time() : $time);
		$logFile = date("Y_m_d", $time);
		
		// Get the entire path
		return $logFolder."/log".$logFile.".txt";
	}
	
	/**
	 * Get the log file according to the given file name.
	 * 
	 * @param	string	$name
	 * 		The log file name.
	 * 		If empty, get the current day's file.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The full log file path.
	 */
	private function getLogFileByName($name = "")
	{
		// Get the log folder from the inherited class
		$logFolder = $this->getLogFolder();
		if (empty($logFolder))
			return NULL;
		
		// Check log file name
		if (empty($name))
			return $this->getLogFileByTime();
		
		// Get the entire path
		return $logFolder."/".$name.".txt";
	}
}
//#section_end#
?>