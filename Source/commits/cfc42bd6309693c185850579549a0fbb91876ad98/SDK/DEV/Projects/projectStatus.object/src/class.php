<?php
//#section#[header]
// Namespace
namespace DEV\Projects;

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
 * @package	Projects
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;
use \DEV\Projects\project;

/**
 * Developer Project Status Manager
 * 
 * Manages project status.
 * 
 * @version	v. 0.1-0
 * @created	February 18, 2014, 12:22 (EET)
 * @revised	July 11, 2014, 21:32 (EEST)
 */
class projectStatus
{
	/**
	 * Create a new project status.
	 * 
	 * @param	string	$name
	 * 		The status name-description.
	 * 
	 * @return	mixed
	 * 		The project status id if success, false otherwise.
	 */
	public static function create($name)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$dbq = new dbQuery("1382259048", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		$projectStatus = $dbc->fetch($result);
		
		if ($projectStatus)
			return $projectStatus['id'];
		
		return FALSE;
	}
	
	/**
	 * Get all project status.
	 * 
	 * @return	array
	 * 		An array of all status information.
	 */
	public static function getStatus()
	{
		// Initialize database connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("15820415350028", "developer.projects");
		
		// Set attributes and execute
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Update a project's status.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to update the status.
	 * 
	 * @param	integer	$status
	 * 		The status type as the database schema says.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function update($projectID, $status)
	{
		// Initialize database connection
		$dbc = new dbConnection();
		
		// Get query
		$dbq = new dbQuery("254607052", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $projectID;
		$attr['status'] = $status;
		$result = $dbc->execute($dbq, $attr);
		
		// Update log
		if ($result)
			self::updateLog($projectID, $status);
		
		return $result;
	}
	
	/**
	 * Get the project's status history log.
	 * Includes the current status and the history entries.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to get the log from.
	 * 
	 * @return	array
	 * 		An array of the current status and timestamp and the history entries.
	 * 		
	 * 		Schema:
	 * 		['current']['status']
	 * 		['current']['timestamp']
	 * 		['history'][entry]['status']
	 * 		['history'][entry]['timestamp']
	 */
	public static function getHistoryLog($projectID)
	{
		// Init project
		$project = new project($projectID);
		$rootFolder = $project->getRootFolder();
		$statusFilePath = $rootFolder."/Log/status.xml";
		
		// Init parser
		$parser = new DOMParser();
		try
		{
			$parser->load($statusFilePath);
		}
		catch (Exception $ex)
		{
			return array();
		}
		
		// Init log
		$log = array();
		
		// Get current status
		$current = $parser->evaluate("//current")->item(0);
		$log['current']['status'] = $parser->attr($current, "status");
		$log['current']['timestamp'] = $parser->attr($current, "timestamp");
		
		// Get history status entries
		$log['history'] = array();
		$history_entries = $parser->evaluate("//history/entry");
		foreach ($history_entries as $entry)
		{
			$logEntry = array();
			$logEntry['status'] = $parser->attr($entry, "status");
			$logEntry['timestamp'] = $parser->attr($entry, "timestamp");
			
			$log['history'][] = $logEntry;
		}
		
		return $log;
	}
	
	private static function updateLog($projectID, $status)
	{
		// Init project
		$project = new project($projectID);
		$rootFolder = $project->getRootFolder();
		$statusFilePath = $rootFolder."/Log/status.xml";
		
		// Init parser
		$parser = new DOMParser();
		try
		{
			$parser->load($statusFilePath);
		}
		catch (Exception $ex)
		{
			// Create file
			fileManager::create(systemRoot.$statusFilePath, "", TRUE);
			
			// Create root
			$root = $parser->create("status");
			$parser->append($root);
			$parser->save(systemRoot.$statusFilePath);
			
			// Create structure
			$current = $parser->create("current");
			$parser->append($root, $current);
			
			$history = $parser->create("history");
			$parser->append($root, $history);
			$parser->update();
		}
		
		// Get timestamp
		$timestamp = time();
		
		// Update current
		$current = $parser->evaluate("//current")->item(0);
		$parser->attr($current, "status", $status);
		$parser->attr($current, "timestamp", $timestamp);
		
		// Add entry to history
		$history = $parser->evaluate("//history")->item(0);
		$entry = $parser->create("entry");
		$parser->prepend($history, $entry);
		$parser->attr($entry, "status", $status);
		$parser->attr($entry, "timestamp", $timestamp);
		
		// Update file
		return $parser->update();
	}
}
//#section_end#
?>