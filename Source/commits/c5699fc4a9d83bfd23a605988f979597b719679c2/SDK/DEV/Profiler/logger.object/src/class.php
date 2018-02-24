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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::cookies");

use \API\Resources\storage\cookies;

/**
 * Developer's System Logger
 * 
 * Logs all messages of any priority and category.
 * It creates a log viewer.
 * 
 * @version	1.0-2
 * @created	February 10, 2014, 10:52 (EET)
 * @revised	September 8, 2014, 17:05 (EEST)
 */
class logger
{
	/**
	 * The system is unusable.
	 * 
	 * @type	string
	 */
	const EMERGENCY = 1;
	/**
	 * Action must be taken immediately.
	 * 
	 * @type	string
	 */
	const ALERT = 2;
	/**
	 * Critical conditions.
	 * 
	 * @type	string
	 */
	const CRITICAL = 4;
	/**
	 * Error conditions.
	 * 
	 * @type	string
	 */
	const ERROR = 8;
	/**
	 * Warning conditions.
	 * 
	 * @type	string
	 */
	const WARNING = 16;
	/**
	 * Normal, but significant condition.
	 * 
	 * @type	string
	 */
	const NOTICE = 32;
	/**
	 * Informational message.
	 * 
	 * @type	string
	 */
	const INFO = 64;
	/**
	 * Debugging message.
	 * 
	 * @type	string
	 */
	const DEBUG = 128;
	
	/**
	 * The array of log messages.
	 * 
	 * @type	array
	 */
	private static $logPool;
	
	/**
	 * Logs or inserts a log message to the pool.
	 * 
	 * @param	string	$message
	 * 		The log short message.
	 * 
	 * @param	integer	$priority
	 * 		Priority indicator as defined by logger constants
	 * 
	 * @param	string	$description
	 * 		The log long description. It supports arrays and numbers. It is written with print_r().
	 * 
	 * @return	void
	 */
	public static function log($message, $priority = self::DEBUG, $description = "")
	{
		// Check log priority
		if (self::status() < $priority)
			return;
			
		// Create a log Message
		$messageLog = array();
		$messageLog['message'] = print_r($message, TRUE);
		$messageLog['level'] = $priority;
		$messageLog['description'] = print_r($description, TRUE);
		
		// Record log message
		self::record($messageLog);
	}
	
	/**
	 * Gets all log messages as array
	 * 
	 * @return	array
	 * 		All log messages from the pool.
	 * 		An array where each entry has a log array with its contents.
	 */
	public static function flush()
	{
		return self::$logPool;
	}
	
	/**
	 * Clears all log messages
	 * 
	 * @return	void
	 */
	public static function clear()
	{
		unset(self::$logPool);
		self::$logPool = array();
	}
	
	/**
	 * Record the message given to the log pool. It inserts extra information depending on the log type.
	 * 
	 * @param	array	$messageLog
	 * 		The messageLog array as created by the log function.
	 * 
	 * @return	void
	 */
	private static function record($messageLog)
	{
		// Update message log
		$messageLog['time'] = time();
		
		// Set backgrace if the log is debug or less than error
		if ($messageLog['level'] <= self::ERROR || $messageLog['level'] >= self::DEBUG)
		{
			// Filter backtrace
			$backtrace = debug_backtrace();
			
			//_____ Remove logger trace logs (2 traces - private and public functions)
			unset($backtrace[0]);
			unset($backtrace[0]);
			
			//_____ Update message backtrace
			$messageLog['trace'] = $backtrace;
		}
		
		// Record log message
		self::$logPool[] = $messageLog;
	}
	
	/**
	 * Gets the level name given the level code.
	 * 
	 * @param	integer	$level
	 * 		The log level code.
	 * 
	 * @return	string
	 * 		The level name.
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
	 * Activate logger.
	 * 
	 * @param	integer	$priority
	 * 		The logger priority. It prevents from logging unwanted messages instead of later filtering the view.
	 * 
	 * @return	void
	 */
	public static function activate($priority = self::DEBUG)
	{
		cookies::set("lggr", $priority);
	}
	
	/**
	 * Deactivate logger.
	 * 
	 * @return	void
	 */
	public static function deactivate()
	{
		cookies::delete("lggr");
	}
	
	/**
	 * Gets the status of the logger.
	 * 
	 * @return	mixed
	 * 		For active logger, it returns the priority, otherwise it returns false.
	 */
	public static function status()
	{
		return (is_null(cookies::get("lggr")) ? FALSE : cookies::get("lggr"));
	}
}
//#section_end#
?>