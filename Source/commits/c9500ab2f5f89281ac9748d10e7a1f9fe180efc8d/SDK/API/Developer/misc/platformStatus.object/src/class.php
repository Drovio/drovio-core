<?php
//#section#[header]
// Namespace
namespace API\Developer\misc;

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
 * @namespace	\misc
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Platform status generator
 * 
 * Sets and gets the platform status data.
 * 
 * @version	{empty}
 * @created	January 31, 2014, 11:55 (EET)
 * @revised	January 31, 2014, 12:56 (EET)
 */
class platformStatus
{
	/**
	 * The status resource index file.
	 * 
	 * @type	string
	 */
	const STATUS_FILE = "/System/Resources/status.xml";
	
	/**
	 * Initializes the platform status parser.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Create file
		$this->createFile();
	}
	
	/**
	 * Updates a project's version.
	 * 
	 * @param	string	$projectName
	 * 		The project name.
	 * 
	 * @param	string	$projectVersion
	 * 		The project current version
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateProject($projectName, $projectVersion)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("//platform")->item(0);
		
		// Get projects
		$projects = $parser->evaluate("//projects")->item(0);
		if (empty($projects))
		{
			$projects = $parser->create("projects");
			$parser->prepend($root, $projects);
		}
		
		$project = $parser->evaluate("//projects/project[@name='".$projectName."']")->item(0);
		if (empty($project))
		{
			$project = $parser->create("project");
			$parser->attr($project, "name", $projectName);
			$parser->append($projects, $project);
		}
		
		// Set project version
		$parser->attr($project, "version", $projectVersion);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Get all project versions by name.
	 * 
	 * @return	array
	 * 		An array of project versions by project name.
	 */
	public function getProjects()
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		
		// Get all projects
		$projectArray = array();
		$projects = $parser->evaluate("//project");
		foreach ($projects as $project)
			$projectArray[$parser->attr($project, "name")] = $parser->attr($project, "version");
		
		return $projectArray;
	}
	
	/**
	 * Sets an operation date.
	 * 
	 * @param	string	$name
	 * 		The operation name.
	 * 
	 * @param	integer	$date
	 * 		The operation timestamp (unix timestamp).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setDate($name, $date)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("//platform")->item(0);
		
		// Get dates root
		$dates = $parser->evaluate("//dates")->item(0);
		if (empty($dates))
		{
			$dates = $parser->create("dates");
			$parser->prepend($root, $dates);
		}
		
		// Get date entry
		$dateEntry = $parser->evaluate("//dates/date[@name='".$name."']")->item(0);
		if (empty($dateEntry))
		{
			$dateEntry = $parser->create("date");
			$parser->attr($dateEntry, "name", $name);
			$parser->append($dates, $dateEntry);
		}
		
		// Update date and file
		$parser->nodeValue($dateEntry, $date);
		return $parser->update();
	}
	
	/**
	 * Gets an operation date time by name.
	 * 
	 * @param	string	$name
	 * 		The operation name.
	 * 
	 * @return	integer
	 * 		The operation timestamp.
	 */
	public function getDate($name)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		
		// Get date entry
		$dateEntry = $parser->evaluate("//dates/date[@name='".$name."']")->item(0);
		
		return $parser->nodeValue($dateEntry);
	}
	
	/**
	 * Sets a specific platform status given a name.
	 * 
	 * @param	string	$statusValue
	 * 		The status value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setStatus($statusValue)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("//platform")->item(0);
		
		// Get status entry
		$status = $parser->evaluate("//status")->item(0);
		if (empty($status))
		{
			$status = $parser->create("status");
			$parser->prepend($root, $status);
		}
		
		// Update status and file
		$parser->nodeValue($status, $statusValue);
		return $parser->update();
	}
	
	/**
	 * Gets a platform status value by name.
	 * 
	 * @return	string
	 * 		The status value.
	 */
	public function getStatus()
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		
		$status = $parser->evaluate("//status");
		return $parser->nodeValue($status);
	}
	
	/**
	 * Creates the status file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private function createFile()
	{
		// Get file path
		if (file_exists(systemRoot.self::STATUS_FILE))
			return TRUE;
		
		// Create file
		$parser = new DOMParser();
		$root = $parser->create("platform");
		$parser->append($root);
		
		fileManager::create(systemRoot.self::STATUS_FILE, "", TRUE);
		return $parser->save(systemRoot.self::STATUS_FILE, "", TRUE);
	}
}
//#section_end#
?>