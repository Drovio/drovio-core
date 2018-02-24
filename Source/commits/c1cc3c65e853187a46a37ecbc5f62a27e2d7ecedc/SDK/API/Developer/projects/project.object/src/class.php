<?php
//#section#[header]
// Namespace
namespace API\Developer\projects;

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
 * @namespace	\projects
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	December 23, 2013, 10:55 (EET)
 * @revised	February 18, 2014, 12:05 (EET)
 * 
 * @deprecated	Use DEV\Projects\project instead.
 */
class project
{
	/**
	 * Create a new developer project.
	 * 
	 * @param	string	$title
	 * 		The project title.
	 * 
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @param	string	$description
	 * 		The project description.
	 * 
	 * @return	mixed
	 * 		If success, return the project id created. FALSE otherwise.
	 */
	public static function create($title, $type, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("906810998", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['type'] = $type;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project['id'];
		
		return FALSE;
	}
	
	/**
	 * Gets the project information with the given id.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @param	{type}	$name
	 * 		{description}
	 * 
	 * @return	mixed
	 * 		An array of information if project found, null otherwise.
	 */
	public static function info($id = "", $name = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		$attr = array();
		
		// Get query
		if (!empty($id))
		{
			$attr['id'] = $id;
			$dbq = new dbQuery("826439764", "developer.projects");
		}
		else if (!empty($name))
		{
			$attr['name'] = $name;
			$dbq = new dbQuery("406610094", "developer.projects");
		}
		else
			return NULL;
		
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project;
		
		return NULL;
	}
	
	/**
	 * Updates a project's basic information.
	 * 
	 * @param	integer	$id
	 * 		The project id to update.
	 * 
	 * @param	string	$title
	 * 		The project's new title.
	 * 
	 * @param	string	$description
	 * 		The project's new description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($id, $title, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("994839582", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Sets the unique project name.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @param	string	$name
	 * 		The project's unique name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setName($id, $name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("2100666044", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Sets the project's type.
	 * 
	 * @param	integer	$id
	 * 		The project's id.
	 * 
	 * @param	{type}	$category
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setCategory($id, $category)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("892768812", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['category'] = $category;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Update a project's status.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @param	integer	$status
	 * 		The project's new status id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateStatus($id, $status)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("254607052", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['status'] = $status;
		$result = $dbc->execute($dbq, $attr);
		
		return $result;
	}
	
	/**
	 * Get the project's repository by project id.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @return	string
	 * 		The project's repository folder.
	 */
	public static function getRepository($id)
	{
		$repository = paths::getRepositoryPath();
		$projectFolder = "p".md5("devProject_".$id).".project";
		
		return $repository."/".$projectFolder;
	}
	
	/**
	 * Get the project's publish folder by project id.
	 * 
	 * @param	integer	$id
	 * 		The project id.
	 * 
	 * @return	string
	 * 		The project's publish folder.
	 */
	public static function getPublishedPath($id)
	{
		$library = paths::getPublishedPath();
		$projectFolder = "p".md5("devProject_".$id).".project";
		
		return $library."/".$projectFolder;
	}
	
	/**
	 * Get all logged in account's projects.
	 * 
	 * @param	boolean	$raw
	 * 		Defines whether the result will be fetched or returned as raw data.
	 * 
	 * @return	mixed
	 * 		Returns an array of numbered projects or a mysql resource result.
	 */
	public static function getMyProjects($raw = TRUE)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("25020897459919", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		
		if (!$raw)
			$result = $dbc->fetch($result, TRUE);
			
		return $result;
	}
	
	/**
	 * Validates whether the logged in account has access to the given project id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True is account has access, false otherwise.
	 */
	public static function validate($projectID)
	{
		// Get account projects
		$dbc = new interDbConnection();
		$dbq = new dbQuery("25020897459919", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		
		// Check if project id is in projects
		while ($project = $dbc->fetch($result))
			if ($project['id'] == $projectID)
				return TRUE;
			
		return FALSE;
	}
}
//#section_end#
?>