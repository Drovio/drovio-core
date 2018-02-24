<?php
//#section#[header]
// Namespace
namespace DEV\BugTracker;

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
 * @package	BugTracker
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "team");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;

/**
 * Bug Tracker Library Manager
 * 
 * {description}
 * 
 * @version	0.1-2
 * @created	October 18, 2014, 12:14 (EEST)
 * @revised	October 20, 2014, 13:04 (EEST)
 */
class btLibrary
{
	/**
	 * Get all public projects.
	 * 
	 * @return	array
	 * 		An array of all public project information.
	 */
	public static function getPublicProjects()
	{
		$dbc = new dbConnection();
		$dbq = new dbQuery("34204816559147", "developer.projects.library");
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all projects of the current active team.
	 * 
	 * @return	array
	 * 		An array of all team project information.
	 */
	public static function getTeamProjects()
	{
		// Get current teamID
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		$dbc = new dbConnection();
		$dbq = new dbQuery("33687915740638", "developer.projects.library");
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>