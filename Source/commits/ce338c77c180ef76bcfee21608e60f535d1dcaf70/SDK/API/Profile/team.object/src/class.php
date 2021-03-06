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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DEV", "Resources", "paths");
importer::import("SYS", "Resources", "pages/domain");

use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \ESS\Environment\session;
use \ESS\Protocol\AsCoProtocol;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Platform\engine;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\akeys\apiKey;
use \DEV\Resources\paths;
use \SYS\Resources\pages\domain;

/**
 * Team Manager Class
 * 
 * Manages the active team and the account's all teams.
 * 
 * @version	6.0-1
 * @created	August 1, 2014, 10:46 (BST)
 * @updated	October 27, 2015, 9:55 (GMT)
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
		$imageUrl = self::getProfileUrl("/media/profile.png", $teamID);
		if (!empty($imageUrl))
			$teamInfo['profile_image_url'] = $imageUrl;
		
		// Add private information if team is current
		if (self::$teamID == $teamID)
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
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		If empty or in secure mode this will be the current team.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($name, $description = "", $teamID = "")
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
			// Set local variables and return true
			self::$teamID = $teamID;
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
	 * @param	boolean	$detailed
	 * 		Get full team details.
	 * 		It is TRUE by default.
	 * 
	 * @return	array
	 * 		An array of all teams.
	 */
	public static function getAccountTeams($detailed = TRUE)
	{
		// Get account id
		$accountID = account::getInstance()->getAccountID();
		if (empty($accountID))
			return FALSE;
		
		// Get account teams from database
		$dbc = new dbConnection();
		$q = new dbQuery("31018237733718", "profile.team");
		$attr = array();
		$attr['aid'] = $accountID;
		$result = $dbc->execute($q, $attr);
		$teams =  $dbc->fetch($result, TRUE);
		if (!$detailed)
			return $teams;
			
		// Create team list
		$teamList = array();
		foreach ($teams as $team)
			$teamList[] = self::info($team['id']);
		
		return $teamList;
	}
	
	/**
	 * Get all team member accounts.
	 * 
	 * @param	boolean	$includeManagedAccounts
	 * 		Set to TRUE to include managed accounts that are children of team member accounts.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		An array of all public account information for each member.
	 */
	public static function getTeamMembers($includeManagedAccounts = FALSE)
	{
		// Get team members from database
		$dbc = new dbConnection();
		
		// Get only admins or managed accounts also
		if ($includeManagedAccounts)
			$q = new dbQuery("34537494692072", "profile.team");
		else
			$q = new dbQuery("28136702398019", "profile.team");
		
		$attr = array();
		$attr['tid'] = self::getTeamID();
		$result = $dbc->execute($q, $attr);
		$accounts =  $dbc->fetch($result, TRUE);
		
		// Create team list
		$accountList = array();
		foreach ($accounts as $accountInfo)
			$accountList[] = account::info($accountInfo['id']);
		
		return $accountList;
	}
	
	/**
	 * Get the current team's id.
	 * 
	 * @return	integer
	 * 		The current team's id.
	 */
	public static function getTeamID()
	{
		// Check cache
		if (isset(self::$teamID) && !empty(self::$teamID))
			return self::$teamID;
		
		// CHECK DROVIO DOMAIN
		$domain = url::getDomain();
		if ($domain != "drov" && $domain != "drov.io")
			return self::$teamID = self::getEngineTeamID();
			
		// Get subdomain and check that it is not taken
		if (AsCoProtocol::exists())
			$subdomain = AsCoProtocol::getSubdomain();
		else
			$subdomain = url::getSubDomain();
		
		// Check www domain
		if (empty($subdomain) || $subdomain == "www")
			return self::$teamID = self::getEngineTeamID();
		
		// Get all current subdomains
		$system_domains = session::get("domains", NULL, "platform");
		if (empty($system_domains))
		{
			$system_domains = domain::getAllDomains();
			session::set("domains", $system_domains, "platform");
		}
		
		// Check all subdomains if one is valid
		foreach ($system_domains as $domainInfo)
			if ($domainInfo['domain'] == $subdomain)
				return self::$teamID = self::getEngineTeamID();

		// Get teams from session
		$teams = session::get("teams", NULL, "profile");
		if (empty($teams) || empty($teams[$subdomain]))
		{
			// Get all account teams
			$teamList = self::getAccountTeams(FALSE);
			$teams = array();
			foreach ($teamList as $teamInfo)
				if (!empty($teamInfo['uname']))
					$teams[strtolower($teamInfo['uname'])] = $teamInfo['id'];

			// Store teams to session
			if (!empty($teams))
				session::set("teams", $teams, "profile");
		}

		// Check if it is a valid team
		if (isset($teams[$subdomain]))
			return self::$teamID = $teams[$subdomain];
		
		// Get team id from engine (dev or api)
		return self::$teamID = self::getEngineTeamID();
	}
	
	/**
	 * Get the team id from engine variables.
	 * Check the current cookie first ('tm', for dev) and then api key.
	 * 
	 * @return	mixed
	 * 		The team id or NULL if no active team.
	 */
	private static function getEngineTeamID()
	{
		// Get team from engine
		$engineTeamID = engine::getVar("tm");
		if (!empty($engineTeamID))
			return $engineTeamID;
		
		// Set team id from api
		$akey = engine::getVar("akey");
		if (!empty($akey))
		{
			$keyInfo = apiKey::info($akey);
			return $keyInfo['team_id'];
		}
		
		return NULL;
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
	 * @return	mixed
	 * 		The team folder path. If there is no active team, it returns FALSE.
	 */
	public static function getTeamFolder($teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
		
		// Get the folder path
		$tmFolderID = self::getFolderID("tm", $teamID, "team");
		$teamFolder = paths::getProfilePath()."/Teams/".$tmFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$teamFolder))
			folderManager::create(systemRoot.$teamFolder);
			
		return $teamFolder;
	}
	
	/**
	 * Get a file url relative to team's profile url.
	 * 
	 * @param	string	$innerPath
	 * 		The inner file path.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 		Leave empty for current team id.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The profile's file url.
	 * 		NULL if the file doesn't exist.
	 */
	public static function getProfileUrl($innerPath, $teamID = "")
	{
		// Get team id
		$teamID = (empty($teamID) ? self::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
		
		// Get full path and resolve
		$fullPath = self::getTeamFolder($teamID)."/".$innerPath;
		if (!file_exists(systemRoot.$fullPath))
			return NULL;
		$fullPath = str_replace(paths::getProfilePath(), "", $fullPath);
		
		// Create profile url
		return url::resolve("profile", $fullPath);
	}
	
	/**
	 * Get a service's folder inside the team root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get the service folder for.
	 * 
	 * @param	boolean	$systemAppData
	 * 		This indicates the service folder as System App and will be placed in a special folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The team service folder path.
	 */
	public static function getServicesFolder($serviceName, $teamID = "", $systemAppData = FALSE)
	{
		// Get team folder
		$teamFolder = self::getTeamFolder($teamID);
		if (empty($teamFolder))
			return NULL;
		
		// Get service folder
		$oldFolder = $teamFolder."/Services/".$serviceName."/";
		$newFolder = $teamFolder."/".($systemAppData ? "SystemAppData/" : "").$serviceName."/";
		
		// Create folder if not exists
		if ($newFolder && !file_exists(systemRoot.$newFolder))
			folderManager::create(systemRoot.$newFolder);
		
		// COMPATIBILITY - copy old folder to new
		if (file_exists(systemRoot.$oldFolder))
		{
			folderManager::copy(systemRoot.$oldFolder, systemRoot.$newFolder, $contents_only = TRUE);
			folderManager::remove(systemRoot.$oldFolder, $name = "", $recursive = TRUE);
		}
			
		return $newFolder;
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