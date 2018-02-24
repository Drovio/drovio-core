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

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Developer\resources\paths;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;

/**
 * Abstract System Activity Logger
 * 
 * Abstract class for activity logging in the system.
 * 
 * @version	{empty}
 * @created	February 10, 2014, 12:29 (EET)
 * @revised	February 10, 2014, 12:29 (EET)
 */
abstract class activityLogger
{
	/**
	 * The root folder for storing the log files.
	 * 
	 * @type	string
	 */
	const LOG_ROOT_FOLDER = "/System/logs/";
	
	/**
	 * Gets the log filename for the activity.
	 * Use groups (folders) for the same activity and separate days if necessary.
	 * 
	 * @return	string
	 * 		The log inner file path.
	 */
	abstract protected function getLogFile();
	
	/**
	 * Creates a new entry log.
	 * 
	 * @param	string	$description
	 * 		The log description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function log($description)
	{
		// Get current timestamp
		$timestamp = time();
		
		// Check if log file exists
		$fileName = $this->getLogFile();
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER.$fileName;
		if (!file_exists(systemRoot.$filePath))
			$this->createFile($filePath);
		
		// Load log file
		$parser = new DOMParser();
		$parser->load($filePath);
		
		// Get root
		$root = $parser->evaluate("//log")->item(0);
		
		// Create entry file
		$entry = $parser->create("entry", $description);
		$parser->attr($entry, "time", $timestamp);
		$parser->append($root, $entry);
		
		// Save File
		return $parser->update();
	}
	
	/**
	 * Get all logs from the given activity.
	 * 
	 * @return	array
	 * 		An array of logs by time as a key and the log description as the value.
	 */
	public function getLogs()
	{
		$fileName = $this->getLogFile();
		$filePath = paths::getSysDynDataPath().self::LOG_FOLDER.$fileName;
		
		// Init log entries
		$logEntries = array();
		
		// Load log file
		$parser = new DOMParser();
		try
		{
			$parser->load($filePath);
		}
		catch (Exception $ex)
		{
			return $logEntries;
		}
		
		// Get root
		$entries = $parser->evaluate("//entry");
		foreach ($entries as $logentry)
			$logEntries[$parser->attr($logentry, "time")] = $parser->nodeValue($logentry);
		
		// Return log entries
		return $logEntries;
	}
	
	/**
	 * Creates the log file and initializes the content.
	 * 
	 * @param	string	$filePath
	 * 		The file path to create.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function createFile($filePath)
	{
		// Create empty file
		fileManager::create(systemRoot.$filePath, "", TRUE);
		
		// Create XML file root
		$parser = new DOMParser();
		$root = $parser->create("log");
		$parser->append($root);
		
		// Save Log
		return $parser->save(systemRoot.$filePath, "", TRUE);
	}
}
//#section_end#
?>