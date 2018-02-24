<?php
//#section#[header]
// Namespace
namespace BSS\Market;

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
 * @package	Market
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "apps/application");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");
importer::import("API", "Profile", "account");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Projects", "projectLibrary");

use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\apps\application;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Profile\account;
use \API\Resources\DOMParser;
use \DEV\Projects\projectLibrary;

/**
 * BOSS' Application Market Manager
 * 
 * Manages the applications in the BOSS' library (market) for a given team (or the current team).
 * 
 * @version	3.0-1
 * @created	April 3, 2015, 10:42 (EEST)
 * @updated	October 8, 2015, 20:25 (EEST)
 */
class appMarket
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
	 * 		It is empty by default.
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
		
		// Check version
		if (empty($version))
			return FALSE;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("17071773969738", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$attr['version'] = $version;
		$attr['time'] = time();
		$attr['trial'] = 1;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get all applications purchased by a team.
	 * 
	 * @return	array
	 * 		Include all application information including last application version (in case there is a newer version) and icon_url for current and newer (if any) version.
	 */
	public static function getTeamApplications()
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;

		// Get team applications
		$dbc = new dbConnection();
		$dbq = new dbQuery("19032090695951", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		
		// Fetch applications and add application icon url
		$teamApplications = array();
		while ($appInfo = $dbc->fetch($result))
		{
			// Get icon and add to information
			$iconUrl = application::getApplicationIconUrl($appInfo['application_id'], $appInfo['version']);
			if (!empty($iconUrl))
				$appInfo['icon_url'] = $iconUrl;
			
			// Get icon of last version (if different)
			if ($appInfo['lastVersion'] != $appInfo['version'])
			{
				// Get icon and add to information
				$iconUrl = application::getApplicationIconUrl($appInfo['application_id'], $appInfo['lastVersion']);
				if (!empty($iconUrl))
					$appInfo['last_version_icon_url'] = $iconUrl;
			}
			
			// Add to list
			$teamApplications[] = $appInfo;
		}
		
		// Return application list
		return $teamApplications;
	}
	
	/**
	 * Get application information for a given version for the BOSS market.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 
	 * @return	array
	 * 		An array of all application information including release title, project description, icon url and more.
	 */
	public static function getApplicationInfo($applicationID, $version)
	{
		// Get application info
		return application::getApplicationInfo($applicationID, $version);
	}
	
	/**
	 * Gets the last version of a given application.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The last application version for the BOSS platform.
	 */
	public static function getLastApplicationVersion($applicationID)
	{
		// Return last project version from project library
		return projectLibrary::getLastProjectVersion($applicationID);
	}
	
	/**
	 * Get the team's current application version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	boolean	$live
	 * 		If set to true, get the live version from the database and skip session storage.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The application version string.
	 */
	public static function getTeamAppVersion($applicationID, $live = FALSE)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;

		// Check session
		$sessionValue = session::get("pver_".$teamID."_".$applicationID, NULL, "project_library");
		if (!$live && !empty($sessionValue))
			return $sessionValue;

		// Get team project version
		$dbc = new dbConnection();
		$dbq = new dbQuery("28973263415624", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		$appVersion = $dbc->fetch($result);
		
		// Check if account has a different version
		$accountValue = self::getAccountAppVersion($applicationID);
		if (!empty($appVersion) && !empty($accountValue) && $appVersion['version'] != $accountValue)
			return $accountValue;

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
		// Get current app version
		$currentAppVersion = self::getTeamAppVersion($applicationID, $live = TRUE);
		
		// Get account project version
		$accountVersion = self::getAccountAppVersion($applicationID);

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
	 * 		It is empty by default.
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
		
		// Check version
		if (empty($version))
			return FALSE;
			
		// Get version token
		$dbc = new dbConnection();
		$dbq = new dbQuery("28755780032182", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['pid'] = $applicationID;
		$attr['tid'] = $teamID;
		$attr['version'] = $version;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Get all application manifest permissions information.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @param	string	$version
	 * 		The application version.
	 * 
	 * @return	array
	 * 		An array of all manifest information of the application.
	 */
	public static function getApplicationPermissions($applicationID, $version)
	{
		// Get application manifests
		$appManifests = application::getApplicationManifests($applicationID, $version);
		
		// Get only manifest information
		$appPermissions = array();
		foreach ($appManifests as $mfID => $mfInfo)
			$appPermissions[$mfID] = $mfInfo['info'];
		
		// Return permissions
		return $appPermissions;
	}
	
	/**
	 * Get boss public applications.
	 * 
	 * @param	integer	$start
	 * 		For pagination purposes, indicates the start index from 0.
	 * 		It is 0 by default.
	 * 
	 * @param	integer	$count
	 * 		For pagination purposes, in combination with the $start attribute, it marks the number of results.
	 * 		It is 30 by default.
	 * 
	 * @return	array
	 * 		An array of all application info including release title, project description, icon url and more.
	 */
	public static function getBossApplications($start = 0, $count = 30)
	{
		// Get all boss apps
		$dbc = new dbConnection();
		$dbq = new dbQuery("24888225218029", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['start'] = (empty($start) ? 0 : $start);
		$attr['count'] = (empty($count) ? 30 : $count);
		$result = $dbc->execute($dbq, $attr);
		
		$bossApps = array();
		while ($appInfo = $dbc->fetch($result))
		{
			// Get and check icon
			$iconUrl = application::getApplicationIconUrl($appInfo['application_id'], $appInfo['version']);
			if (!empty($iconUrl))
				$appInfo['icon_url'] = $iconUrl;
			
			// Append to list
			$bossApps[] = $appInfo;
		}
		
		// Return apps
		return $bossApps;
	}
	
	/**
	 * Get all team private applications as marked from the project settings.
	 * 
	 * @param	integer	$start
	 * 		For pagination purposes, indicates the start index from 0.
	 * 		It is 0 by default.
	 * 
	 * @param	integer	$count
	 * 		For pagination purposes, in combination with the $start attribute, it marks the number of results.
	 * 		It is 30 by default.
	 * 
	 * @return	array
	 * 		An array of all application info including release title, project description, icon url and more.
	 */
	public static function getTeamPrivateApplications($start = 0, $count = 30)
	{
		// Check current team
		$teamID = team::getTeamID();
		if (empty($teamID))
			return NULL;
		
		// Get all team private apps
		$dbc = new dbConnection();
		$dbq = new dbQuery("28733898137546", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['start'] = (empty($start) ? 0 : $start);
		$attr['count'] = (empty($count) ? 30 : $count);
		$attr['tid'] = $teamID;
		$result = $dbc->execute($dbq, $attr);
		
		$teamApps = array();
		while ($appInfo = $dbc->fetch($result))
		{
			// Get and check icon
			$iconUrl = application::getApplicationIconUrl($appInfo['application_id'], $appInfo['version']);
			if (!empty($iconUrl))
				$appInfo['icon_url'] = $iconUrl;
			
			// Append to list
			$teamApps[] = $appInfo;
		}
		
		// Return apps
		return $teamApps;
	}
	
	/**
	 * Get the application version for the current account for the current team from the account's folder.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	string
	 * 		The account's application version.
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
	 * 		The application version string.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	private static function setAccountAppVersion($applicationID, $version)
	{
		// Check version
		if (empty($version))
			return FALSE;
			
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
	
	/**
	 * Get market statistics for a given application per version.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	array
	 * 		An array of all team counts for each active application version.
	 */
	public static function getApplicationMarketStatistics($applicationID)
	{
		// Get application 'purchases' in the app store
		$dbc = new dbConnection();
		$dbq = new dbQuery("18530862611456", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $applicationID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all application market purchases.
	 * 
	 * @param	integer	$applicationID
	 * 		The application id.
	 * 
	 * @return	array
	 * 		An array of all application market purchases.
	 */
	public static function getApplicationMarket($applicationID)
	{
		// Get application 'purchases' in the app store
		$dbc = new dbConnection();
		$dbq = new dbQuery("16239491615699", "enterprise.market");
		
		// Set attributes and execute
		$attr = array();
		$attr['id'] = $applicationID;
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>