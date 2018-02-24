<?php
//#section#[header]
// Namespace
namespace BT\Projects;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BT
 * @package	Projects
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;

/**
 * Bug Tracker Library Manager
 * 
 * Gets But Tracker projects.
 * It includes open projects and team projects.
 * 
 * @version	0.1-1
 * @created	January 5, 2015, 11:13 (EET)
 * @updated	January 5, 2015, 11:13 (EET)
 */
class btLibrary
{
	/**
	 * Get all public projects.
	 * These projects allow creating issues from any user.
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
	 * These projects may be open or not. They can be managed by team administrator.
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