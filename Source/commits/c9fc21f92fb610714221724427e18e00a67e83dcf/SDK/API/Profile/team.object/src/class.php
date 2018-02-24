<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "person");
importer::import("API", "Profile", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\person;
use \API\Profile\account;
use \API\Platform\engine;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;

/**
 * Team Manager Class
 * 
 * Manages the active team and the account's all teams.
 * 
 * @version	1.1-7
 * @created	August 1, 2014, 12:46 (EEST)
 * @revised	December 1, 2014, 11:06 (EET)
 */
class team
{
	/**
	 * All the team data.
	 * 
	 * @type	array
	 */
	private static $teamData = array();
	
	/**
	 * Gets the team info.
	 * 
	 * @return	array
	 * 		An array of the team data.
	 */
	public static function info()
	{
		if (!self::validate())
			return NULL;
		
		return self::getInfo(self::getTeamID());
	}
	
	/**
	 * Validates if the current account is member of the current team.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to validate the account.
	 * 		If empty, get the current team id.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate($teamID = "")
	{
		// Validate account
		if (!account::validate())
			return FALSE;
		
		// Get data
		$accountID = account::getAccountID();
		$teamID = (empty($teamID) ? engine::getVar("tm") : $teamID);

		// Check if the cookies exist
		if (empty($teamID))
			return FALSE;
		
		// Check if account is in team
		$dbc = new dbConnection();
		$q = new dbQuery("26485851489367", "profile.team");
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['aid'] = $accountID;
		$result = $dbc->execute($q, $attr);
		
		// Check account into team
		if ($dbc->get_num_rows($result) > 0)
		{
			// Renew cookies
			if (account::rememberme())
			{
				$duration = 30 * 24 * 60 * 60;
				cookies::set("tm", $teamID, $duration);
			}
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Logout the account from the team.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Delete all team cookies
		cookies::remove("tm");
	}
	
	/**
	 * Gets the given team's information.
	 * 
	 * @param	integer	$teamID
	 * 		The team data to get info for.
	 * 
	 * @return	array
	 * 		Array of team data.
	 */
	private static function getInfo($teamID)
	{
		$dbc = new dbConnection();
		$q = new dbQuery("3457285831128", "profile.team");
		$attr = array();
		$attr['id'] = $teamID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Switch from one team to another.
	 * The account must be valid and member of the given team id.
	 * 
	 * @param	string	$teamID
	 * 		The team id to switch to.
	 * 
	 * @param	string	$password
	 * 		The account's current password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function switchTeam($teamID, $password)
	{
		// Check if it's a valid account
		if (!account::validate())
			return FALSE;

		// Validate account member to given team
		if (!self::validate($teamID))
			return FALSE;

		// Authenticate person and switch team
		$username = person::getUsername();
		if (account::authenticate($username, $password))
		{
			// Set Cookies again
			if (account::rememberme())
				$duration = 30 * 24 * 60 * 60;
			return cookies::set("tm", $teamID, $duration);
		}
		
		return FALSE;
	}
	
	/**
	 * Get the default team for the given account.
	 * 
	 * @return	array
	 * 		The default team information.
	 */
	public static function getDefaultTeam()
	{
		// Check if it's a valid account
		if (!account::validate())
			return FALSE;
		
		$dbc = new dbConnection();
		$q = new dbQuery("27126297366609", "profile.team");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}	
	
	/**
	 * Set the default team for the given account.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to set as default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setDefaultTeam($teamID)
	{
		// Check if it's a valid account
		if (!account::validate())
			return FALSE;
		
		// Validate account member to given team
		if (!self::validate($teamID))
			return FALSE;
		
		$dbc = new dbConnection();
		$q = new dbQuery("3522699746177", "profile.team");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$attr['tid'] = $teamID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all teams of the current account.
	 * 
	 * @return	array
	 * 		An array of all teams.
	 */
	public static function getAccountTeams()
	{
		$dbc = new dbConnection();
		$q = new dbQuery("31018237733718", "profile.team");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get the current team id.
	 * 
	 * @return	integer
	 * 		The current team id.
	 */
	public static function getTeamID()
	{
		return engine::getVar("tm");
	}
	
	/**
	 * Get the current team name.
	 * 
	 * @return	string
	 * 		The current team name.
	 */
	public static function getTeamName()
	{
		return self::getTeamValue("name");
	}
	
	/**
	 * Gets a team value from the session. If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The value name.
	 * 
	 * @return	string
	 * 		The team value.
	 */
	private static function getTeamValue($name)
	{
		// Check session existance
		if (!isset(self::$teamData[$name]))
			self::$teamData = self::info();
			
		return self::$teamData[$name];
	}
	
	/**
	 * Gets the team's profile folder. The folder is created if doesn't exist.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get the folder for.
	 * 		
	 * 		NOTICE: This doesn't work when in secure mode (by importer).
	 * 
	 * @return	mixed
	 * 		The team folder path. If there is no active team, it returns FALSE.
	 */
	public static function getTeamFolder($teamID = "")
	{
		// Get team id
		if (empty($teamID) || importer::secure())
			$teamID = self::getTeamID();
		
		// Check that team exists
		if (empty($teamID))
			return FALSE;
		
		// Get the folder path
		$tmFolderID = self::getFolderID("tm", $accountID, "team");
		$teamFolder = paths::getProfilePath()."/Teams/".$tmFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$teamFolder))
			folderManager::create(systemRoot.$teamFolder);
			
		return $teamFolder;
	}
	
	/**
	 * Get the team's service root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get the service folder for.
	 * 		
	 * 		NOTICE: This doesn't work when in secure mode (by importer).
	 * 
	 * @return	string
	 * 		The team service folder path.
	 */
	public static function getServicesFolder($serviceName, $teamID = "")
	{
		// Get team folder
		$teamFolder = self::getTeamFolder($teamID);
		if (empty($teamFolder))
			return NULL;
		
		// Get service folder
		$serviceFolder = $teamFolder."/Services/".$serviceName."/";
		
		if (!file_exists(systemRoot.$serviceFolder))
			folderManager::create(systemRoot.$serviceFolder);
			
		return $serviceFolder;
	}
	
	/**
	 * Gets the unique folder id for the requested use.
	 * 
	 * @param	string	$prefix
	 * 		The prefix of the folder.
	 * 
	 * @param	string	$folderID
	 * 		The id to be hashed.
	 * 
	 * @param	string	$extension
	 * 		The extension of the folder (if any).
	 * 
	 * @return	string
	 * 		The folder name.
	 */
	private static function getFolderID($prefix, $folderID, $extension = "")
	{
		return $prefix.hash("md5", $folderID).(empty($extension) ? "" : ".".$extension);
	}
}
//#section_end#
?>