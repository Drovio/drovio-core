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
importer::import("API", "Profile", "team");
importer::import("API", "Profile", "account");
importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "storage::session");
importer::import("DEV", "Resources", "paths");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Profile\account;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;
use \DEV\Resources\paths;

/**
 * Project Library Manager
 * 
 * Manages the applications in the BOSS' library.
 * 
 * @version	2.1-1
 * @created	August 26, 2014, 16:55 (EEST)
 * @revised	October 5, 2014, 1:27 (EEST)
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
	 */
	public static function getTeamProjectVersion($projectID)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		// Check session
		$sessionValue = session::get("pver_".$teamID."_".$projectID, NULL, "project_library");
		if (!empty($sessionValue))
			return $sessionValue;
		
		// Check account file
		$accountValue = self::getAccountProjectVersion($projectID);
		if (!empty($accountValue))
			return $accountValue;
			
		// Get team project version
		$dbc = new dbConnection();
		$dbq = new dbQuery("34163115325986", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$projectVersion = $dbc->fetch($result);
		
		// Update session and return version
		self::setAccountProjectVersion($projectID, $projectVersion['version']);
		session::set("pver_".$teamID."_".$projectID, $projectVersion['version'], "project_library");
		return $projectVersion['version'];
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
	 */
	public static function updateTeamProjectVersion($projectID)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		// Get team project version
		$dbc = new dbConnection();
		$dbq = new dbQuery("34163115325986", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$data = $dbc->fetch($result);
		$currentProjectVersion = $data['version'];
		
		// Get account project version
		$accountVersion = self::getAccountProjectVersion($projectID);
		
		// If versions are not the same, update account
		if (!version_compare($currentProjectVersion, $accountVersion, "=="))
		{
			self::setAccountProjectVersion($projectID, $currentProjectVersion);
			session::set("pver_".$teamID."_".$projectID, $currentProjectVersion, "project_library");
		}
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
	 */
	public static function setTeamProjectVersion($projectID, $version)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("27650800158073", "developer.projects.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['tid'] = $teamID;
		$attr['version'] = $version;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get the project version for the current account for the current team from the account's folder.
	 * 
	 * @param	integer	$projectID
	 * 		The project/application id to get the version for.
	 * 
	 * @return	string
	 * 		The project's version.
	 * 		NULL if project version is not set.
	 */
	private static function getAccountProjectVersion($projectID)
	{
		// Get dashboard service folder
		$serviceFolder = account::getServicesFolder("/BossApps/");

		$teamID = team::getTeamID();
		$indexFile = $serviceFolder."/t".$teamID."_apps.xml";
		
		// Load index
		$parser = new DOMParser();
		try
		{
			$parser->load($indexFile);
		}
		catch (Exception $ex)
		{
			return NULL;
		}
		
		// Get application element
		$appElement = $parser->find($projectID, $nodeName = "app");
		if (empty($appElement))
			return NULL;
		
		// Return the version
		return $parser->attr($appElement, "version");
	}
	
	/**
	 * Set the project version for the current account for the current team from the account's folder.
	 * 
	 * @param	integer	$projectID
	 * 		The project/application id to get the version for.
	 * 
	 * @param	string	$version
	 * 		The project/application version string.
	 * 
	 * @return	void
	 */
	private static function setAccountProjectVersion($projectID, $version)
	{
		// Get dashboard service folder
		$accountFolder = account::getServicesFolder("/BossApps/");
		$teamID = team::getTeamID();
		$indexFile = $accountFolder."/t".$teamID."_apps.xml";
		
		// Load index
		$parser = new DOMParser();
		try
		{
			$parser->load($indexFile);
			$root = $parser->evaluate("/apps")->item(0);
		}
		catch (Exception $ex)
		{
			// Create file
			$root = $parser->create("apps");
			$parser->append($root);
			
			$parser->save(systemRoot.$indexFile);
		}
		
		// Set application id version
		$appElement = $parser->find($projectID, $nodeName = "app");
		if (empty($appElement))
		{
			$appElement = $parser->create("app", "", $projectID);
			$parser->append($root, $appElement);
		}
		
		// Set version number
		$parser->attr($appElement, "version", $version);
		
		// Update file
		return $parser->update();
	}
}
//#section_end#
?>