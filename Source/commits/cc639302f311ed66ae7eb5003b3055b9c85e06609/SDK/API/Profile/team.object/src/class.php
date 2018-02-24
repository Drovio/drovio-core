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

importer::import("API", "Login", "team");
importer::import("API", "Profile", "account");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DEV", "Resources", "paths");
importer::import("DRVC", "Profile", "team");
importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "url");
importer::import("ESS", "Environment", "session");
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("SYS", "Resources", "pages/domain");

use \API\Login\team as LoginTeam;
use \API\Profile\account;
use \API\Platform\engine;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Security\akeys\apiKey;
use \DEV\Resources\paths;
use \DRVC\Profile\team as IDTeam;
use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \ESS\Environment\session;
use \ESS\Protocol\AsCoProtocol;
use \SYS\Resources\pages\domain;

/**
 * Team Manager Class
 * 
 * Manages the active team and the account's all teams.
 * 
 * @version	7.0-3
 * @created	August 1, 2014, 10:46 (BST)
 * @updated	December 16, 2015, 18:41 (GMT)
 */
class team extends LoginTeam
{
	/**
	 * All the team data.
	 * 
	 * @type	array
	 */
	private static $teamData = array();
	
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
		// Get public information
		$teamInfo = parent::info($teamID);
		
		// Get profile image (if any)
		$imageUrl = static::getProfileUrl("/media/profile.png", $teamID);
		if (!empty($imageUrl))
			$teamInfo['profile_image_url'] = $imageUrl;
		
		// Return all info
		return $teamInfo;
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
		$teamID = (empty($teamID) || importer::secure() ? static::getTeamID() : $teamID);
		
		// Get profile image path
		$imagePath = static::getTeamFolder($teamID)."/media/profile.png";
		
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
	 * Get the current team's name.
	 * 
	 * @return	string
	 * 		The current team's name.
	 */
	public static function getTeamName()
	{
		return static::getTeamValue("name");
	}
	
	/**
	 * Get the current team's unique name.
	 * 
	 * @return	string
	 * 		The current team's unique name.
	 */
	public static function getTeamUname()
	{
		return static::getTeamValue("uname");
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
			self::$teamData = static::info();
			
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
		$teamID = (empty($teamID) ? static::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
		
		// Get identity team name (for reference)
		$identityTeamName = static::getIdentityTeamName();
		$identityTeamName = (empty($identityTeamName) ? "drovio" : $identityTeamName);
		$identityTeamName = strtolower($identityTeamName).".id";
		
		// Get the folder path
		$tmFolderID = static::getFolderID("tm", $teamID, "team");
		$teamFolder = paths::getProfilePath().$identityTeamName."/tm/".$tmFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$teamFolder))
			folderManager::create(systemRoot.$teamFolder);
		
		// COMPATIBILITY
		// Check if there is the old folder and move all things
		$teamFolder_old = paths::getProfilePath()."/Teams/".$tmFolderID."/";
		if (file_exists(systemRoot.$teamFolder_old))
		{
			// Copy files
			folderManager::copy(systemRoot.$teamFolder_old, systemRoot.$teamFolder, $contents_only = TRUE);
			
			// Remove old folder
			folderManager::remove(systemRoot.$teamFolder_old, $name = "", $recursive = TRUE);
		}
			
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
		$teamID = (empty($teamID) ? static::getTeamID() : $teamID);
		if (empty($teamID))
			return NULL;
		
		// Get full path and resolve
		$fullPath = static::getTeamFolder($teamID)."/".$innerPath;
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
		$teamFolder = static::getTeamFolder($teamID);
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