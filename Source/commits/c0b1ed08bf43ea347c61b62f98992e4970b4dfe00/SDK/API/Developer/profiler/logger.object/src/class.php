<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "storage::cookies");

use \API\Resources\storage\cookies;

/**
 * System Logger
 * 
 * Logs all messages for any priority and category.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:49 (EEST)
 * @revised	July 22, 2013, 11:51 (EEST)
 */
class logger
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
	
	const DISK_R = 1;
	const DISK_W = 2;
	const DATABASE_R = 4;
	const DATABASE_W = 8;
	
	/**
	 * The array of log messages.
	 * 
	 * @type	array
	 */
	private static $logPool;
	
	/**
	 * Insert a log message to the pool
	 * 
	 * @param	string	$message
	 * 		The message content
	 * 
	 * @param	integer	$priority
	 * 		Priority indicator as defined by logger constants
	 * 
	 * @param	{type}	$description
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function log($message, $priority = self::INFO, $description = "", $type = self::DEBUG)
	{
		// Create a log Message
		$messageLog = array();
		$messageLog['message'] = $message;
		$messageLog['level'] = $priority;
		$messageLog['description'] = $description;
		
		// Check preferences/priority and proceed
		if (!self::getPriority($priority))
			return;
		
		// Record log message
		self::record($messageLog);
	}
	
	/**
	 * Returns all log messages as array
	 * 
	 * @return	array
	 * 		{description}
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
	 * Record the message given
	 * 
	 * @param	array	$messageLog
	 * 		The messageLog array as created by the log function
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
	
	private static function getPriority($priority)
	{
		return TRUE;
	}
	
	/**
	 * Gets the level name given the level code.
	 * 
	 * @param	integer	$level
	 * 		The log level.
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
	 * Activate logger.
	 * 
	 * @return	void
	 */
	public static function activate()
	{
		cookies::set("lggr", TRUE);
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
	 * @return	boolean
	 * 		True for active logger, false for inactive.
	 */
	public static function status()
	{
		return (is_null(cookies::get("lggr")) ? FALSE : cookies::get("lggr"));
	}
}
//#section_end#
?>