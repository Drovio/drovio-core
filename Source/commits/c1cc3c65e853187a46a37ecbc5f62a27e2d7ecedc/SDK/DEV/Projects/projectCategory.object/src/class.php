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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;

/**
 * Developer Project Category
 * 
 * Manages developer project categories, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	February 18, 2014, 12:18 (EET)
 * @revised	February 18, 2014, 12:18 (EET)
 */
class projectCategory
{
	/**
	 * Create a new project type.
	 * 
	 * @param	string	$name
	 * 		The project type name.
	 * 
	 * @return	mixed
	 * 		The new type id if success, false otherwise.
	 */
	public static function createType($name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("951976139", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		$projectCategory = $dbc->fetch($result);
		
		if ($projectCategory)
			return $projectCategory['id'];
		
		return FALSE;
	}
	
	/**
	 * Get all project types.
	 * 
	 * @return	array
	 * 		An array of all project types.
	 */
	public static function getTypes()
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1100563189", "developer.projects.categories");
		
		// Get types
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Gets the project category information.
	 * 
	 * @param	integer	$id
	 * 		The project category id.
	 * 
	 * @return	mixed
	 * 		An array of information if category exists, false otherwise.
	 */
	public static function categoryInfo($id)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("331603421", "developer.projects.categories");
		
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
	 * Create a new project category in the given category.
	 * 
	 * @param	string	$type
	 * 		The project type.
	 * 
	 * @param	string	$name
	 * 		The category name.
	 * 
	 * @return	mixed
	 * 		The category id if success, false otherwise.
	 */
	public static function createCategory($type, $name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("1037618671", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$attr['type'] = $type;
		$attr['name'] = $name;
		$result = $dbc->execute($dbq, $attr);
		$projectCategory = $dbc->fetch($result);
		
		if ($projectCategory)
			return $projectCategory['id'];
		
		return FALSE;
	}
	
	/**
	 * Get all project categories in a given project type.
	 * 
	 * @param	string	$type
	 * 		The project type.
	 * 
	 * @return	array
	 * 		An array of all project categories.
	 */
	public static function getCategories($type)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$attr = array();
		$attr['type'] = $type;
		$dbq = new dbQuery("184965085", "developer.projects.categories");
		
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>