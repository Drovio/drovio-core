<?php
//#section#[header]
// Namespace
namespace BSS\Dashboard;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BSS
 * @package	Dashboard
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

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Profile\account;
use \API\Resources\DOMParser;
use \API\Resources\storage\session;

/**
 * BOSS' Application Library Manager
 * 
 * Manages the applications in the BOSS' library for a given team (or the current team).
 * 
 * @version	0.1-1
 * @created	October 29, 2014, 10:46 (EET)
 * @revised	October 29, 2014, 10:46 (EET)
 */
class appLibrary
{
	/**
	 * Buy an application for a team.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The version to buy.
	 * 
	 * @param	integer	$teamID
	 * 		The team id or empty to get the current team.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function buyApplication($applicationID, $version, $teamID = "")
	{
		// Check current team
		$teamID = (empty($teamID) ? team::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("30594174154022", "boss.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$attr['version'] = $version;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get the team's current application version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The application version string.
	 */
	public static function getTeamAppVersion($applicationID)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		// Check session
		$sessionValue = session::get("pver_".$teamID."_".$applicationID, NULL, "project_library");
		if (!empty($sessionValue))
			return $sessionValue;
		
		// Check account file
		$accountValue = self::getAccountAppVersion($applicationID);
		if (!empty($accountValue))
			return $accountValue;
			
		// Get team project version
		$dbc = new dbConnection();
		$dbq = new dbQuery("34009531249309", "boss.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$appVersion = $dbc->fetch($result);
		
		// Update session and return version
		self::setAccountAppVersion($applicationID, $appVersion['version']);
		session::set("pver_".$teamID."_".$applicationID, $appVersion['version'], "project_library");
		return $appVersion['version'];
	}
	
	/**
	 * Updates the team's application version for the current account to the next declared version (if any).
	 * 
	 * Returning true doesn't mean that there was an update.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateTeamAppVersion($applicationID)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		// Get team project version
		$dbc = new dbConnection();
		$dbq = new dbQuery("34009531249309", "boss.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$data = $dbc->fetch($result);
		$currentAppVersion = $data['version'];
		
		// Get account project version
		$accountVersion = self::setAccountAppVersion($applicationID);
		
		// If versions are not the same, update account
		if (!version_compare($currentAppVersion, $accountVersion, "=="))
		{
			self::setAccountAppVersion($applicationID, $currentAppVersion);
			session::set("pver_".$teamID."_".$applicationID, $currentAppVersion, "project_library");
		}
	}
	
	/**
	 * Set the next version of a project for a team.
	 * The project must be in the team's library.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The app's version selected by the team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id or empty to get the current team.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setTeamAppVersion($applicationID, $version, $teamID = "")
	{
		// Check current team
		$teamID = (empty($teamID) ? team::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("34183374197003", "boss.library");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$attr['version'] = $version;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get the application version for the current account for the current team from the account's folder.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	private static function getAccountAppVersion($applicationID)
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
		$appElement = $parser->find($applicationID, $nodeName = "app");
		if (empty($appElement))
			return NULL;
		
		// Return the version
		return $parser->attr($appElement, "version");
	}
	
	/**
	 * Set the project version for the current account for the current team from the account's folder.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The project/application version string.
	 * 
	 * @return	void
	 */
	private static function setAccountAppVersion($applicationID, $version)
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
		$appElement = $parser->find($applicationID, $nodeName = "app");
		if (empty($appElement))
		{
			$appElement = $parser->create("app", "", $applicationID);
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