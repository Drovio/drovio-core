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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \ESS\Protocol\server\AsCoProtocol;
use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * System Error Logger
 * 
 * Logs all errors and exceptions of the system.
 * 
 * @version	{empty}
 * @created	January 27, 2014, 14:44 (EET)
 * @revised	February 10, 2014, 12:41 (EET)
 * 
 * @deprecated	Use DEV\Profiler\log\errorLogger instead.
 */
class errorLogger
{
	/**
	 * The inner log folder.
	 * 
	 * @type	string
	 */
	const LOG_FOLDER = "/System/activity/errors/";
	
	/**
	 * Logs a given exception in the system.
	 * 
	 * @param	Exception	$exception
	 * 		The exception thrown.
	 * 
	 * @return	void
	 */
	public static function logException($exception)
	{
		// Set exception description
		$description = "Exception (".$exception->errCode.")\n";
		$description .= $exception->getMessage()."\n";
		$description .= $exception->getTraceAsString()."\n\n";
		
		// Get server data
		$description .= "Server Data\n";
		$description .= print_r($_SERVER, TRUE)."\n\n";
		
		// Set ascop data
		if (AsCoProtocol::exists())
		{
			$description .= "AsCoP Data\n";
			$description .= AsCoProtocol::getSubdomain()." | ".AsCoProtocol::getPath()."\n";
		}
		
		// Log exception
		self::log($description);
	}
	
	/**
	 * Creates a new entry log.
	 * 
	 * @param	string	$description
	 * 		The log description.
	 * 
	 * @return	void
	 */
	public static function log($description)
	{
		// Get current timestamp
		$timestamp = time();
		
		// Check if log file exists
		$fileName = self::getFileName($timestamp);
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER;
		if (!file_exists(systemRoot.$filePath.$fileName))
			self::createFile();
		
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$root = $parser->evaluate("//errlog")->item(0);
		
		// Create entry file
		$entry = $parser->create("entry", $description);
		$parser->append($root, $entry);
		$parser->attr($entry, "time", $timestamp);
		
		// Save File
		$parser->update();
	}
	
	/**
	 * Get all logs in the given date.
	 * 
	 * @param	mixed	$date
	 * 		The date or time from which we want the logs.
	 * 
	 * @return	array
	 * 		An array of logs by time and description.
	 */
	public static function getLogs($date = NULL)
	{
		$date = (empty($date) ? time() : $date);
		$fileName = self::getFileName($date);
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER;
		
		// Load log file
		$parser = new DOMParser();
		try
		{
			$parser->load($filePath.$fileName);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Get root
		$log = $parser->evaluate("/errlog")->item(0);
		$entries = $parser->evaluate("entry", $log);
		$logEntries = array();
		foreach ($entries as $logentry)
			$logEntries[$parser->attr($logentry, "time")] = $parser->nodeValue($logentry);
		
		// Return log entries
		return $logEntries;
	}
	
	/**
	 * Creates the rror log file of the day.
	 * 
	 * @return	void
	 */
	private static function createFile()
	{
		$fileName = self::getFileName(time());
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER;
		fileManager::create(systemRoot.$filePath.$fileName, "", TRUE);
		
		// Create DOMParser
		$parser = new DOMParser();
		
		// Create root
		$root = $parser->create("errlog");
		$parser->append($root);
		
		// Save Log
		$parser->save(systemRoot.$filePath, $fileName, TRUE);
	}
	
	/**
	 * Get the error's log file name.
	 * 
	 * @param	integer	$time
	 * 		The timestamp to get the date.
	 * 
	 * @return	string
	 * 		The log file name.
	 */
	private static function getFileName($time)
	{
		return "errlog_".date("Y-m-d", $time).".xml";
	}
}
//#section_end#
?>