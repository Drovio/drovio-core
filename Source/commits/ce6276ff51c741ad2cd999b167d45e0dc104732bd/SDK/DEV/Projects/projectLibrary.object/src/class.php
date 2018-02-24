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
importer::import("API", "Resources", "storage::session");
importer::import("DEV", "Resources", "paths");
importer::import("BSS", "Dashboard", "appLibrary");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\storage\session;
use \DEV\Resources\paths;
use \BSS\Dashboard\appLibrary;

/**
 * Project Library Manager
 * 
 * Manages the applications in the generic library (paths, tokens etc.)
 * 
 * @version	2.2-1
 * @created	August 26, 2014, 16:55 (EEST)
 * @revised	October 29, 2014, 10:46 (EET)
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
		
		// Check session
		$token = session::get("ptoken_".$projectID."_".$version, NULL, "project_library");
		if (empty($token))
		{
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
			
			// Update session token
			session::set("ptoken_".$projectID."_".$version, $token, "project_library");
		}
		
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
	 * 
	 * @deprecated	Function moved to \BSS\Dashboard\appLibrary.
	 */
	public static function getTeamProjectVersion($projectID)
	{
		return appLibrary::getTeamAppVersion($projectID);
	}
	
	/**
	 * Updates the team's project version to the next declared version (if any).
	 * 
	 * Returning true doesn't mean that there was an update.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to update the version.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Function moved to \BSS\Dashboard\appLibrary.
	 */
	public static function updateTeamProjectVersion($projectID)
	{
		return appLibrary::updateTeamAppVersion($projectID);
	}
	
	/**
	 * Set the next version of a project for a team.
	 * The project must be in the team's library.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$version
	 * 		The project's version selected by the team.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Function moved to \BSS\Dashboard\appLibrary.
	 */
	public static function setTeamProjectVersion($projectID, $version)
	{
		return appLibrary::setTeamAppVersion($projectID, $version);
	}
}
//#section_end#
?>