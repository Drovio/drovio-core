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

/**
 * Developer Project Status Manager
 * 
 * Manages project status.
 * 
 * @version	{empty}
 * @created	February 18, 2014, 12:22 (EET)
 * @revised	February 18, 2014, 12:22 (EET)
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
	
	/**
	 * Get all project status.
	 * 
	 * @return	array
	 * 		An array of all status information.
	 */
	public static function getStatus()
	{
		// Initialize database connection
		$dbc = new interDbConnection();
		$dbq = new dbQuery("15820415350028", "developer.projects");
		
		// Set attributes and execute
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>