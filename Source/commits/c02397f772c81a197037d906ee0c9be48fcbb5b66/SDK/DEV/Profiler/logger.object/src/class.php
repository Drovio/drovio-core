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

importer::import("ESS", "Environment", "cookies");
importer::import("DEV", "Profiler", "log/activityLogger");
importer::import("DEV", "Core", "coreProject");

use \ESS\Environment\cookies;
use \DEV\Profiler\log\activityLogger;
use \DEV\Core\coreProject;

/**
 * Developer's System Logger for Core
 * 
 * Logs all messages of any priority and category.
 * It logs messages to memory for the platform log viewer.
 * It logs messages below warning (to error) to project log files.
 * 
 * @version	4.1-4
 * @created	February 10, 2014, 10:52 (EET)
 * @updated	January 13, 2015, 18:01 (EET)
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
	 * The array of log messages.
	 * 
	 * @type	array
	 */
	private $logPool;
	
	/**
	 * Initialize the logger object and clear the log pool.
	 * 
	 * @return	void
	 */
	private function __construct()
	{
		$this->logPool = array();
	}
	
	/**
	 * Get the static logger instance.
	 * 
	 * @return	logger
	 * 		The logger instance.
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
			self::$instance = new logger();
		
		return self::$instance;
	}
	
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
	public function log($message, $priority = parent::DEBUG, $description = "")
	{
		// Check it is a static call
		$static = !(isset($this) && get_class($this) == __CLASS__);
		if ($static)
			return logger::getInstance()->log($message, $priority, $description);
		
		// Log to file if log is warning or less
		if ($priority <= parent::WARNING)
		{
			$logMessage = print_r($message, TRUE)."\n".print_r($description, TRUE);
			parent::log($logMessage, $priority);
		}
		
		// Check log priority
		if (self::status() < $priority)
			return;
			
		// Create a log Message
		$messageLog = array();
		$messageLog['message'] = print_r($message, TRUE);
		$messageLog['level'] = $priority;
		$messageLog['description'] = print_r($description, TRUE);
		
		// Record log message to memory
		$this->record($messageLog);
	}
	
	/**
	 * Gets all log messages as array
	 * 
	 * @return	array
	 * 		All log messages from the pool.
	 * 		An array where each entry has a log array with its contents.
	 */
	public function flush()
	{
		// Check it is a static call
		$static = !(isset($this) && get_class($this) == __CLASS__);
		if ($static)
			return logger::getInstance()->flush();
		
		// Return pool
		return $this->logPool;
	}
	
	/**
	 * Clears all log messages
	 * 
	 * @return	void
	 */
	public function clear()
	{
		// Check it is a static call
		$static = !(isset($this) && get_class($this) == __CLASS__);
		if ($static)
			return logger::getInstance()->clear();
		
		// Clear pool
		unset($this->logPool);
		$this->logPool = array();
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
	
	/**
	 * Record the message given to the log pool. It inserts extra information depending on the log type.
	 * 
	 * @param	array	$messageLog
	 * 		The messageLog array as created by the log function.
	 * 
	 * @return	void
	 */
	private function record($messageLog)
	{
		// Update message log
		$messageLog['time'] = time();
		
		// Set backgrace if the log is debug or less than error
		if ($messageLog['level'] <= parent::ERROR || $messageLog['level'] >= parent::DEBUG)
		{
			// Filter backtrace
			$backtrace = debug_backtrace();
			
			// Remove logger trace logs (2 traces - private and public functions)
			unset($backtrace[0]);
			unset($backtrace[0]);
			
			// Update message backtrace
			$messageLog['trace'] = $backtrace;
		}
		
		// Record log message
		$this->logPool[] = $messageLog;
	}
	
	/**
	 * Activate logger.
	 * 
	 * @param	integer	$priority
	 * 		The logger priority. It prevents from logging unwanted messages instead of later filtering the view.
	 * 
	 * @return	void
	 */
	public static function activate($priority = parent::DEBUG)
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
		cookies::remove("lggr");
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