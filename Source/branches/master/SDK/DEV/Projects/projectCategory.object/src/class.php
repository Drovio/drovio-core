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
importer::import("API", "Model", "sql::dbQuery");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * Developer Project Category
 * 
 * Manages developer project categories, creating, updating and getting the proper information.
 * 
 * @version	0.1-2
 * @created	February 18, 2014, 12:18 (EET)
 * @revised	November 5, 2014, 16:53 (EET)
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
	 * @param	boolean	$includePrivate
	 * 		Gives the option to include private projects types in the result. The default value is FALSE.
	 * 
	 * @return	array
	 * 		An array of all project types.
	 */
	public static function getTypes($includePrivate = FALSE)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
		if ($includePrivate)
			$dbq = new dbQuery("1100563189", "developer.projects.categories");
		else
			$dbq = new dbQuery("34161435144676", "developer.projects.categories");
		
		// Get types
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>