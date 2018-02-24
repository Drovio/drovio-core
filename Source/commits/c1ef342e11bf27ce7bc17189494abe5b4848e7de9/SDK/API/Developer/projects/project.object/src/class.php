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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Developer\resources\paths;
use \API\Model\units\sql\dbQuery;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	December 23, 2013, 10:55 (EET)
 * @revised	December 24, 2013, 10:38 (EET)
 */
class project
{
	/**
	 * Create a new developer project.
	 * 
	 * @param	string	$title
	 * 		The project title.
	 * 
	 * @param	integer	$category
	 * 		The project category id.
	 * 
	 * @param	string	$description
	 * 		The project description.
	 * 
	 * @return	mixed
	 * 		If success, return the project id created. FALSE otherwise.
	 */
	public static function create($title, $category, $description = "")
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("906810998", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['title'] = $title;
		$attr['category'] = $category;
		$attr['description'] = $description;
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
	 * @return	mixed
	 * 		An array of information if project found, false otherwise.
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
			$dbq = new dbQuery("826439764", "developer.projects");
		}
		else
			return FALSE;
		
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project;
		
		return FALSE;
	}
	
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
	 * @param	integer	$type
	 * 		The project's new type id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setType($id, $type)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("892768812", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['type'] = $type;
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
}
//#section_end#
?>