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
importer::import("API", "Developer", "resources::paths");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "team");

use \SYS\Comm\db\dbConnection;
use \API\Developer\resources\paths;
use \API\Model\sql\dbQuery;
use \API\Profile\team;

/**
 * Project Library Manager
 * 
 * {description}
 * 
 * @version	0.1-3
 * @created	August 26, 2014, 16:55 (EEST)
 * @revised	August 28, 2014, 17:02 (EEST)
 */
class projectLibrary
{
	/**
	 * Get the project's publish library folder path.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$version
	 * 		The project version.
	 * 
	 * @return	string
	 * 		The published folder path.
	 */
	public static function getPublishedPath($projectID, $version)
	{
		if (empty($version))
			return NULL;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("26891842467991", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['version'] = $version;
		$result = $dbc->execute($dbq, $attr);
		$projectVersion = $dbc->fetch($result);
		$token = $projectVersion['token'];
		
		// Get published library path
		$projectFolder = "r".md5("project_".$projectID).".red/".$token."/";
		return paths::getPublishedPath()."/".$projectFolder;
	}
	
	/**
	 * Get the project's last published version by the time created.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	string
	 * 		The project version string.
	 */
	public static function getLastProjectVersion($projectID)
	{
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("19968015445834", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$result = $dbc->execute($dbq, $attr);
		$projectVersion = $dbc->fetch($result);
		return $projectVersion['version'];
	}
	
	/**
	 * Get the team's project version.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	string
	 * 		The project version string.
	 */
	public static function getTeamProjectVersion($projectID)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("34163115325986", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$projectVersion = $dbc->fetch($result);
		return $projectVersion['version'];
	}
}
//#section_end#
?>