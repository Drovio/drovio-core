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
 * Developer Project Category
 * 
 * Manages developer project categories, creating, updating and getting the proper information.
 * 
 * @version	{empty}
 * @created	December 23, 2013, 11:16 (EET)
 * @revised	December 30, 2013, 17:32 (EET)
 */
class projectCategory
{
	/**
	 * Create a new project category.
	 * 
	 * @param	string	$name
	 * 		The project category name.
	 * 
	 * @return	mixed
	 * 		The new category id if success, false otherwise.
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
	 * Get all categories.
	 * 
	 * @param	boolean	$array
	 * 		Defines the return object type.
	 * 
	 * @return	mixed
	 * 		An array of all categories or a mysqli resource object.
	 */
	public static function getTypes($array = TRUE)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		// Get query
		$dbq = new dbQuery("1100563189", "developer.projects.categories");
		
		// Set attributes and execute
		$attr = array();
		$result = $dbc->execute($dbq, $attr);
		
		if (!$array)
			return $result;
		
		$categories = $dbc->fetch($result, TRUE);
		return $project;
	}
	
	/**
	 * Gets the project category information.
	 * 
	 * @param	integer	$id
	 * 		The category id.
	 * 
	 * @return	mixed
	 * 		An array of information if category exists, false otherwise.
	 */
	public static function info($id)
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
	 * Create a new project type in the given category.
	 * 
	 * @param	integer	$category
	 * 		The category id.
	 * 
	 * @param	string	$name
	 * 		The project type name.
	 * 
	 * @return	mixed
	 * 		The type id if success, false otherwise.
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
	 * @param	{type}	$type
	 * 		{description}
	 * 
	 * @return	array
	 * 		An array of all project categories.
	 * 
	 * @deprecated	Not in use
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