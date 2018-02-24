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
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\session;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \DEV\Resources\paths;

/**
 * Project Library Manager
 * 
 * Manages the applications in the generic library (paths, tokens etc.)
 * 
 * @version	5.0-2
 * @created	August 26, 2014, 16:55 (EEST)
 * @updated	May 28, 2015, 13:01 (EEST)
 */
class projectLibrary
{
	/**
	 * The inner resources folder name for the production library.
	 * 
	 * @type	string
	 */
	const RSRC_FOLDER = "/resources";
	
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
		// Check version
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
	 * Get project release info for a given version.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$version
	 * 		The project version.
	 * 
	 * @return	array
	 * 		An array of all project release information including project information (title, description) and owner team name.
	 */
	public static function getProjectReleaseInfo($projectID, $version)
	{
		// Check version
		if (empty($version))
			return NULL;
		
		// Get project version's information
		$dbc = new dbConnection();
		$dbq = new dbQuery("29038230505263", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['version'] = $version;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result);
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
	 * Get the project's icon url according to the given version.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	string	$projectVersion
	 * 		The project version.
	 * 
	 * @return	mixed
	 * 		Returns the icon url or NULL if the project doesn't have an icon.
	 */
	public static function getProjectIconUrl($projectID, $projectVersion)
	{
		// Get icon from published path
		$iconPath = self::getPublishedPath($projectID, $projectVersion).self::RSRC_FOLDER."/.assets/icon.png";
		if (file_exists(systemRoot.$iconPath))
		{
			$iconPath = str_replace(paths::getPublishedPath(), "", $iconPath);
			$iconUrl = url::resolve("lib", $iconPath);
			return $iconUrl;
		}
		
		return NULL;
	}
}
//#section_end#
?>