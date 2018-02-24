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
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;

/**
 * Developer Project
 * 
 * Manages developer projects, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	December 23, 2013, 10:55 (EET)
 * @revised	December 23, 2013, 10:57 (EET)
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
	public static function info($id)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("826439764", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$result = $dbc->execute($dbq, $attr);
		$project = $dbc->fetch($result);
		
		if ($project)
			return $project;
		
		return FALSE;
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
	public static function updateBasicInfo($id, $title, $description = "")
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
	 * Updates a project's developer information.
	 * 
	 * @param	integer	$id
	 * 		The project id to update.
	 * 
	 * @param	string	$repository
	 * 		The project's repository.
	 * 
	 * @param	string	$editorSub
	 * 		The project's editor subdomain.
	 * 
	 * @param	string	$editorPath
	 * 		The project's editor path in the subdomain.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateDevInfo($id, $repository, $editorSub, $editorPath)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("2100666044", "developer.projects");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $id;
		$attr['repository'] = $repository;
		$attr['editorSub'] = $editorSub;
		$attr['editorPath'] = $editorPath;
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
}
//#section_end#
?>