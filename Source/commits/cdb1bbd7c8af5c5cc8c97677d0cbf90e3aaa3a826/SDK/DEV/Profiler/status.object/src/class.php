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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * Platform status handler
 * 
 * Sets and gets the platform status data.
 * 
 * @version	{empty}
 * @created	February 11, 2014, 12:04 (EET)
 * @revised	February 11, 2014, 12:04 (EET)
 */
class status
{
	/**
	 * The status resource index file.
	 * 
	 * @type	string
	 */
	const STATUS_FILE = "/System/Resources/status.xml";
	
	/**
	 * The project deploy type attribute name.
	 * 
	 * @type	string
	 */
	const PROJECT_DEPLOY = "deploy";
	
	/**
	 * The project publish type attribute name.
	 * 
	 * @type	string
	 */
	const PROJECT_PUBLISH = "publish";
	
	/**
	 * The platform status code for healthy platform.
	 * 
	 * @type	string
	 */
	const STATUS_OK = 200;
	
	/**
	 * The platform status code for platform with errors.
	 * 
	 * @type	string
	 */
	const STATUS_ERROR = 500;
	
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
	 * 		The project's name.
	 * 
	 * @param	string	$projectVersion
	 * 		The project version to declare.
	 * 
	 * @param	string	$type
	 * 		The project version type.
	 * 		Use status constants.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateProject($projectName, $projectVersion, $type = self::PROJECT_DEPLOY)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("/platform")->item(0);
		
		// Get projects
		$projects = $parser->evaluate("/platform/projects")->item(0);
		if (empty($projects))
		{
			$projects = $parser->create("projects");
			$parser->prepend($root, $projects);
		}
		
		$project = $parser->evaluate("/platform/projects/project[@name='".$projectName."']")->item(0);
		if (empty($project))
		{
			$project = $parser->create("project");
			$parser->attr($project, "name", $projectName);
			$parser->append($projects, $project);
		}
		
		// Check for valid version type
		if ($type != self::PROJECT_PUBLISH && $type != self::PROJECT_DEPLOY)
			return FALSE;
		
		// Set version
		$parser->attr($project, $type, $projectVersion);
		
		// Update file
		return $parser->update();
	}
	
	/**
	 * Get all project versions.
	 * 
	 * @return	array
	 * 		An array of project versions by project name and version type.
	 * 		[projectName][deploy] = "2.0.0";
	 * 		[projectName][publish] = "1.0.0";
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
		{
			$projectName = $parser->attr($project, "name");
			$projectArray[$projectName][self::PROJECT_DEPLOY] = $parser->attr($project, self::PROJECT_DEPLOY);
			$projectArray[$projectName][self::PROJECT_PUBLISH] = $parser->attr($project, self::PROJECT_PUBLISH);
		}
		
		return $projectArray;
	}
	
	/**
	 * Sets a specific platform status given a status code (class constant) and status description.
	 * 
	 * @param	string	$statusValue
	 * 		The status description.
	 * 
	 * @param	string	$statusCode
	 * 		The status code.
	 * 		Use class constants.
	 * 		Otherwise it will be considered non valid and will return false.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setStatus($statusValue, $statusCode = self::STATUS_OK)
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		$root = $parser->evaluate("/platform")->item(0);
		
		// Get status entry
		$status = $parser->evaluate("/platform/status")->item(0);
		if (empty($status))
		{
			$status = $parser->create("status");
			$parser->prepend($root, $status);
		}
		
		// Check for valid status code.
		if ($statusCode != self::STATUS_OK && $statusCode != self::STATUS_ERROR)
			return FALSE;
		
		// Update status code and value and file
		$parser->attr($status, "code", $statusCode);
		$parser->nodeValue($status, $statusValue);
		return $parser->update();
	}
	
	/**
	 * Gets the platform status with code and description.
	 * 
	 * @return	array
	 * 		An array of one element, code => description.
	 */
	public function getStatus()
	{
		// Load file
		$parser = new DOMParser();
		$parser->load(self::STATUS_FILE);
		
		$status = $parser->evaluate("//status");
		$statusCode = $parser->attr($status, "code");
		$statusValue = $parser->nodeValue($status);
		
		$status = array();
		$status[$statusCode] = $statusValue;
		
		return $status;
	}
	
	/**
	 * Creates the status index file.
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