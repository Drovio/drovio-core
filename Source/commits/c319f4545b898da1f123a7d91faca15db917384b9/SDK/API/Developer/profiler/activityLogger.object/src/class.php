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

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * System Activity Logger
 * 
 * Logs system activity for later analysis.
 * Only important logs!
 * 
 * @version	{empty}
 * @created	August 10, 2013, 12:51 (EEST)
 * @revised	December 11, 2013, 19:57 (EET)
 */
class activityLogger
{
	/**
	 * The inner log folder.
	 * 
	 * @type	string
	 */
	const LOG_FOLDER = "/System/activity/logs/";
	
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
		$root = $parser->evaluate("//log")->item(0);
		
		// Create entry file
		$entry = $parser->create("entry", $description);
		$parser->append($root, $entry);
		$parser->attr($entry, "time", $timestamp);
		
		// Save File
		$parser->update();
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$date
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function getLogs($date = NULL)
	{
		$fileName = self::getFileName(time());
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER;
		
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath.$fileName);
		
		// Get root
		$log = $parser->evaluate("/log")->item(0);
		$entries = $parser->evaluate("entry", $log);
		$logEntries = array();
		foreach ($entries as $logentry)
			$logEntries[$parser->attr($logentry, "time")] = $parser->nodeValue($logentry);
		
		// Return log entries
		return $logEntries;
	}
	
	/**
	 * Creates the log file for the day.
	 * 
	 * @return	void
	 */
	private static function createFile()
	{
		$fileName = self::getFileName();
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER;
		fileManager::create(systemRoot.$filePath.$fileName, "", TRUE);
		
		// Create DOMParser
		$parser = new DOMParser();
		
		// Create root
		$root = $parser->create("log");
		$parser->append($root);
		
		// Save Log
		$parser->save(systemRoot.$filePath, $fileName, TRUE);
	}
	
	/**
	 * Gets the log filename for the day.
	 * 
	 * @param	{type}	$time
	 * 		{description}
	 * 
	 * @return	string
	 * 		{description}
	 */
	private static function getFileName($time)
	{
		return "log_".date("Y-m-d", $time).".xml";
	}
}
//#section_end#
?>