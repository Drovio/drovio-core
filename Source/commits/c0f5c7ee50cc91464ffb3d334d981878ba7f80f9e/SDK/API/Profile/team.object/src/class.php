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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "url");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Platform\engine;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;

/**
 * Team Manager Class
 * 
 * Manages the active team and the account's all teams.
 * 
 * @version	3.0-3
 * @created	August 1, 2014, 12:46 (EEST)
 * @updated	May 19, 2015, 11:19 (EEST)
 */
class team
{
	/**
	 * Indicates whether the team is logged in for this run.
	 * 
	 * @type	boolean
	 */
	private static $loggedIn = FALSE;
	
	/**
	 * All the team data.
	 * 
	 * @type	array
	 */
	private static $teamData = array();
	
	/**
	 * The current team id.
	 * 
	 * @type	integer
	 */
	private static $teamID = NULL;
	
	/**
	 * Get all the public information about a team.
	 * If the team is the current account team, include private information.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		Leave empty for current team id.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of the team data.
	 */
	public static function info($teamID = "")
	{
		// Get current team id if empty
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);
		
		// Get public information
		$dbc = new dbConnection();
		$q = new dbQuery("3457285831128", "profile.team");
		$attr = array();
		$attr['id'] = $teamID;
		$result = $dbc->execute($q, $attr);
		$teamInfo =  $dbc->fetch($result);
		
		// Get profile image (if any)
		$imagePath = self::getTeamFolder($teamID)."/media/profile.png";
		if (file_exists(systemRoot.$imagePath))
		{
			$imageUrl = str_replace(paths::getProfilePath(), "", $imagePath);
			$imageUrl = url::resolve("profile", $imageUrl);
			$teamInfo['profile_image_url'] = $imageUrl;
		}
		
		// Add private information if team is current
		if (self::getTeamID() == $teamID)
		{
			// Add private info
		}
		
		// Return all info
		return $teamInfo;
	}
	
	/**
	 * Update team information.
	 * 
	 * @param	string	$name
	 * 		The team name.
	 * 		It cannot be empty.
	 * 
	 * @param	string	$description
	 * 		The team description.
	 * 
	 * @param	string	$uname
	 * 		The team unique name.
	 * 		This is used for friendly urls and unique identification of the team in the system.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		If empty or in secure mode this will be the current team.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($name, $description = "", $uname = "", $teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) || importer::secure() ? self::getTeamID() : $teamID);
		
		// Check name
		if (empty($name))
			return FALSE;
		
		// Update Information
		$dbc = new dbConnection();
		$q = new dbQuery("27746548581825", "profile.team");
		$attr = array();
		$attr['id'] = $teamID;
		$attr['name'] = $name;
		$attr['description'] = $description;
		$attr['uname'] = $uname;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Update the team profile image.
	 * 
	 * @param	data	$image
	 * 		The image data.
	 * 		The image should be in png format.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		If empty or in secure mode this will be the current team.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateProfileImage($image, $teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) || importer::secure() ? self::getTeamID() : $teamID);
		
		// Get profile image path
		$imagePath = self::getTeamFolder($teamID)."/media/profile.png";
		
		// Remove image if empty
		if (is_null($image))
			fileManager::remove(systemRoot.$imagePath);
		
		// If image is empty other than null, return false
		if (empty($image))
			return FALSE;
		
		// Update image
		return fileManager::create(systemRoot.$imagePath, $image);
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
		{
			// Logout from team
			self::logout();
			return FALSE;
		}
		
		// Get data
		$accountID = account::getAccountID();
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);

		// Check if there is a valid team
		if (empty($teamID))
		{
			// Logout from team
			self::logout();
			return FALSE;
		}
		
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;
		
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
				self::$teamID = $teamID;
				cookies::set("tm", $teamID, $duration, TRUE);
			}
			
			self::$loggedIn = TRUE;
			return TRUE;
		}
		
		// Logout and return false
		self::logout();
		return FALSE;
	}
	
	/**
	 * Logout the account from the team.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Set the current team id as null
		self::$teamID = NULL;
		
		// Set logged in as false
		self::$loggedIn = FALSE;
		
		// Delete all team cookies
		cookies::remove("tm");
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

		// Authenticate account and switch team
		$username = account::getUsername(TRUE);
		if (account::authenticate($username, $password))
		{
			// Set Cookies again
			if (account::rememberme())
				$duration = 30 * 24 * 60 * 60;
			
			self::$teamID = $teamID;
			self::$loggedIn = TRUE;
			return cookies::set("tm", $teamID, $duration, TRUE);
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
		// Get account teams from database
		$dbc = new dbConnection();
		$q = new dbQuery("31018237733718", "profile.team");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		$teams =  $dbc->fetch($result, TRUE);
		
		// Create team list
		$teamList = array();
		foreach ($teams as $team)
			$teamList[] = self::info($team['id']);
		
		return $teamList;
	}
	
	/**
	 * Get the current team's id.
	 * 
	 * @return	integer
	 * 		The current team's id.
	 */
	public static function getTeamID()
	{
		if (isset(self::$teamID))
			return self::$teamID;
			
		return engine::getVar("tm");
	}
	
	/**
	 * Get the current team's name.
	 * 
	 * @return	string
	 * 		The current team's name.
	 */
	public static function getTeamName()
	{
		return self::getTeamValue("name");
	}
	
	/**
	 * Get the current team's unique name.
	 * 
	 * @return	string
	 * 		The current team's unique name.
	 */
	public static function getTeamUname()
	{
		return self::getTeamValue("uname");
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
	 * 		NOTICE: This doesn't work when in secure mode.
	 * 
	 * @return	mixed
	 * 		The team folder path. If there is no active team, it returns FALSE.
	 */
	public static function getTeamFolder($teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) || importer::secure() ? self::getTeamID() : $teamID);
		
		// Check that team exists
		if (empty($teamID))
			return FALSE;
		
		// Get the folder path
		$tmFolderID = self::getFolderID("tm", $teamID, "team");
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