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

importer::import("ESS", "Environment", "url");
importer::import("API", "Login", "account");
importer::import("API", "Login", "team");
importer::import("API", "Platform", "engine");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Resources", "paths");

use \ESS\Environment\url;
use \API\Login\account as LoginAccount;
use \API\Login\team as LoginTeam;
use \API\Platform\engine;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;

/**
 * Account Manager Class
 * 
 * Singleton class for managing the drovio account.
 * 
 * @version	14.0-3
 * @created	July 5, 2013, 10:38 (BST)
 * @updated	December 13, 2015, 18:57 (GMT)
 */
class account extends LoginAccount
{
	/**
	 * The platform account instance.
	 * 
	 * @type	account
	 */
	private static $instance;
	
	/**
	 * Get an account instance.
	 * 
	 * @return	account
	 * 		The account instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new account();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Authenticates an account with the given username and password.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 		Email is also supported.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function authenticate($username, $password)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->authenticate($username, $password);
		
		// Authenticate account
		return parent::authenticate($username, $password);
	}
	
	/**
	 * Login the account using the drovio identity.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 
	 * @param	string	$password
	 * 		The account password.
	 * 
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user or not.
	 * 		Duration: 1 month.
	 * 
	 * @return	boolean
	 * 		True on success, false on authentication failure.
	 */
	public function login($username, $password, $rememberme = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->login($username, $password, $rememberme);
		
		// Login account id
		return parent::login($username, $password, $rememberme);
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public function getAccountID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getAccountID();
		
		// Get account id from parent
		return parent::getAccountID();
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	string
	 * 		The current mx id.
	 */
	public function getMX()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getMX();
		
		// Get mx from parent
		return parent::getMX();
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	string
	 * 		The account session id.
	 */
	public function getSessionID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getSessionID();
		
		// Return parent session id
		return parent::getSessionID();
	}
	
	/**
	 * Gets the person id of the logged in account.
	 * 
	 * @return	integer
	 * 		The person id.
	 */
	public function getPersonID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getPersonID();
		
		// Get person id
		return parent::getPersonID();
	}
	
	/**
	 * Validates if the user is logged in.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function validate()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->validate();
		
		return parent::validate();
	}
	
	/**
	 * Update the account's password given the reset id token.
	 * 
	 * @param	string	$resetID
	 * 		The reset id.
	 * 
	 * @param	string	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function resetPassword($resetID, $newPassword)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->resetPassword($resetID, $newPassword);
		
		return parent::updatePasswordByReset($resetID, $newPassword);
	}
	
	/**
	 * Gets the account's folder. The folder is created if doesn't exist.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the folder for.
	 * 		
	 * 		NOTICE: This doesn't work when in secure mode.
	 * 
	 * @return	mixed
	 * 		The account folder path.
	 * 		If there is no active account, it returns FALSE.
	 */
	public function getAccountFolder($accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getAccountFolder($accountID);
		
		// Get account id
		$accountID = (empty($accountID) || importer::secure() ? $this->getAccountID() : $accountID);
		if (empty($accountID))
			return NULL;
		
		return $this->getAccountFolderPath($accountID);
	}
	
	/**
	 * Get a file's url relative to the account's profile.
	 * 
	 * @param	string	$innerPath
	 * 		The inner file path.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		Leave empty for the current account.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The profile file url.
	 * 		NULL if the file doesn't exist.
	 */
	public function getProfileUrl($innerPath, $accountID = "")
	{
		// Get account id
		$accountID = (empty($accountID) || importer::secure() ? $this->getAccountID() : $accountID);
		if (empty($accountID))
			return NULL;
		
		// Get full path and resolve
		$fullPath = $this->getAccountFolderPath($accountID)."/".$innerPath;
		if (!file_exists(systemRoot.$fullPath))
			return NULL;
		$fullPath = str_replace(paths::getProfilePath(), "", $fullPath);
		
		// Create profile url
		return url::resolve("profile", $fullPath);
	}
	
	/**
	 * Get the account folder path for any account.
	 * 
	 * @param	integer	$accountID
	 * 		The account to get the folder path for.
	 * 
	 * @return	string
	 * 		The folder url path.
	 */
	private function getAccountFolderPath($accountID)
	{
		if (empty($accountID))
			return NULL;
		
		// Get identity team name (for reference)
		$identityTeamName = LoginTeam::getIdentityTeamName();
		$identityTeamName = (empty($identityTeamName) ? "drovio" : $identityTeamName);
		$identityTeamName = strtolower($identityTeamName).".id";
		
		// Get account folder	
		$accFolderID = $this->getFolderID("acc", $accountID, "account");
		$accountFolder = paths::getProfilePath().$identityTeamName."/acc/".$accFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$accountFolder))
			folderManager::create(systemRoot.$accountFolder);
		
		// COMPATIBILITY
		// Check if there is the old folder and move all things
		$accountFolder_old = paths::getProfilePath()."/Accounts/".$accFolderID."/";
		if (file_exists(systemRoot.$accountFolder_old))
		{
			// Copy files
			folderManager::copy(systemRoot.$accountFolder_old, systemRoot.$accountFolder, $contents_only = TRUE);
			
			// Remove old folder
			folderManager::remove(systemRoot.$accountFolder_old, $name = "", $recursive = TRUE);
		}
			
		return $accountFolder;
	}
	
	/**
	 * Get a service's folder inside the account root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @param	boolean	$systemAppData
	 * 		This indicates the service folder as System App and will be placed in a special folder.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function getServicesFolder($serviceName, $systemAppData = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->getServicesFolder($serviceName, $systemAppData);
		
		// Get account folder
		$accountFolder = $this->getAccountFolder();
		if (empty($accountFolder))
			return NULL;
			
		// Get service folder
		$oldFolder = $accountFolder."/Services/".$serviceName."/";
		$newFolder = $accountFolder."/".($systemAppData ? "SystemAppData/" : "").$serviceName."/";
		
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
	 * Gets the account info.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the information for.
	 * 		Leave empty for the current account.
	 * 		It is empty by default.
	 * 		If it's not the current account, only public information will be returned.
	 * 
	 * @return	array
	 * 		Returns an array of the account information.
	 */
	public function info($accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->info($accountID);
		
		// Get current account id if empty
		$accountID = (empty($accountID) ? $this->getAccountID() : $accountID);
		
		// Get all account info
		$publicInfo = array();
		$accountInfo = parent::info($accountID);
		$accountInfo['accountID'] = $accountInfo['id'];
		$accountInfo['accountTitle'] = $accountInfo['title'];
		
		// Get public profile page
		if (empty($accountInfo['username']))
		{
			$params = array();
			$params['id'] = $accountID;
			$profileUrl = url::resolve("www", "/profile/index.php", $params);
		}
		else
			$profileUrl = url::resolve("www", "/profile/".$accountInfo['username']);
		$publicInfo['profile_url'] = $profileUrl;
		
		// Get profile image (if any)
		$imageUrl = $this->getProfileUrl("/media/profile.png", $accountID);
		if (!empty($imageUrl))
			$publicInfo['profile_image_url'] = $imageUrl;
		
		// Check for public or private information
		if (!$this->validate() || $accountID != $this->getAccountID())
		{
			// Set public info only
			$publicInfo['id'] = $accountInfo['id'];
			$publicInfo['accountID'] = $accountInfo['id'];
			$publicInfo['title'] = $accountInfo['title'];
			$publicInfo['accountTitle'] = $accountInfo['title'];
			$publicInfo['username'] = $accountInfo['username'];
			
			// Add extra info for inner platform use
			if (!importer::secure())
			{
				$publicInfo['mail'] = $accountInfo['mail'];
			}

			return $publicInfo;
		}
		
		// Get all account info
		$accountInfo = array_merge($accountInfo, $publicInfo);
		unset($accountInfo['password']);
		return $accountInfo;
	}
	
	/**
	 * Update the account profile image.
	 * 
	 * @param	data	$image
	 * 		The image data.
	 * 		The image should be in png format.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		If empty or in secure mode this will be the current account.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateProfileImage($image, $accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance()->updateProfileImage($image, $accountID);
		
		// Get team id
		$accountID = (empty($accountID) || importer::secure() ? $this->getAccountID() : $accountID);
		
		// Get profile image path
		$imagePath = $this->getAccountFolder($accountID)."/media/profile.png";
		
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
	 * Remove the current account from the system.
	 * The application will not run in secure mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeAccount()
	{
		// Check secure mode
		if (importer::secure())
			return FALSE;
		
		// Get current account id
		$accountID = $this->getAccountID();
		return parent::removeAccount($accountID);
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
	private function getFolderID($prefix, $folderID, $extension = "")
	{
		return $prefix.hash("md5", $folderID).(empty($extension) ? "" : ".".$extension);
	}
}
//#section_end#
?>