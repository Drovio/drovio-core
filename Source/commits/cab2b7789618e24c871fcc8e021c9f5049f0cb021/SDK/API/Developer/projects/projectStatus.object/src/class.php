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

/**
 * Project Status
 * 
 * Manages project statuses.
 * 
 * @version	{empty}
 * @created	December 23, 2013, 11:22 (EET)
 * @revised	December 23, 2013, 11:22 (EET)
 */
class projectStatus
{
	/**
	 * Create a new project status.
	 * 
	 * @param	string	$name
	 * 		The status name.
	 * 
	 * @return	mixed
	 * 		The project id if success, false otherwise.
	 */
	public static function create($name)
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		
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
}
//#section_end#
?>