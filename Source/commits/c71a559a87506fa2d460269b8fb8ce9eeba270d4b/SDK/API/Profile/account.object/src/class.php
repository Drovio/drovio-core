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
importer::import("API", "Platform", "engine");
importer::import("API", "Profile", "accountSession");
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Resources", "paths");
importer::import("DRVC", "Profile", "account");

use \ESS\Environment\cookies;
use \ESS\Environment\url;
use \API\Platform\engine;
use \API\Profile\accountSession;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;
use \DRVC\Profile\account as IDAccount;

/**
 * Account Manager Class
 * 
 * Singleton class for managing the drovio account.
 * 
 * @version	13.0-1
 * @created	July 5, 2013, 10:38 (BST)
 * @updated	November 10, 2015, 16:43 (GMT)
 */
class account extends IDAccount
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
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
	 * Create a new account instance for the platform identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(self::ID_TEAM_NAME);
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
		$status = $this->getParentInstance()->login($username, $password, $rememberme);
		if ($status)
		{
			// Set cookies
			$duration = ($rememberme ? 30 * 24 * 60 * 60 : 0);
			cookies::set("__DRVID_ACC", $this->getAccountID(), $duration, TRUE);
			cookies::set("__DRVID_PRS", $this->getPersonID(), $duration, TRUE);
			cookies::set("__DRVID_MX", $this->getMX(), $duration, TRUE);
		}
		
		return $status;
	}
	
	/**
	 * Update the current account session and renew cookies if necessary.
	 * 
	 * @return	void
	 */
	public function updateSession()
	{
		// Update session
		parent::updateSession();
		
		// Set session info
		$sessionInfo = $this->getAccountSessionInstance()->info($this->getAccountID());
		$lastAccess = $sessionInfo['lastAccess'];
		$rememberme = $sessionInfo['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if cookies need to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
		{
			// Set cookies
			$duration = 30 * 24 * 60 * 60;
			cookies::set("__DRVID_ACC", $this->getAccountID(), $duration, TRUE);
			cookies::set("__DRVID_PRS", $this->getPersonID(), $duration, TRUE);
			cookies::set("__DRVID_MX", $this->getMX(), $duration, TRUE);
		}
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
		
		// Get mx from parent
		$accountID = parent::getAccountID();
		if (!empty($accountID))
			return $accountID;

		// Get value from cookies
		$accountID = engine::getVar("__DRVID_ACC");
		$accountID = (empty($accountID) ? engine::getVar("acc") : $accountID);
		if ($accountID == 'deleted')
			return NULL;
		
		return $accountID;
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
		$mx = parent::getMX();
		if (!empty($mx))
			return $mx;

		// Get value from cookies
		$mx = engine::getVar("__DRVID_MX");
		$mx = (empty($mx) ? engine::getVar("mx") : $mx);
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
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
		$personID = parent::getPersonID();
		if (!empty($personID))
			return $personID;
		
		// Get value from cookies
		$personID = engine::getVar("__DRVID_PRS");
		$personID = (empty($personID) ? engine::getVar("person") : $personID);
		if ($personID == 'deleted')
			return NULL;
		
		return $personID;
	}
	
	/**
	 * Logout the account from the system.
	 * Delete active session.
	 * Delete cookies.
	 * 
	 * @return	void
	 */
	public function logout()
	{
		// Logout account id
		parent::logout();
		
		// Delete all account cookies
		cookies::remove("__DRVID_ACC");
		cookies::remove("__DRVID_PRS");
		cookies::remove("__DRVID_MX");
		
		// COMPATIBILITY
		cookies::remove("acc");
		cookies::remove("person");
		cookies::remove("mx");
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
		
		return $this->getParentInstance()->updatePasswordByReset($resetID, $newPassword);
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
			
		$accFolderID = $this->getFolderID("acc", $accountID, "account");
		$accountFolder = paths::getProfilePath()."/Accounts/".$accFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$accountFolder))
			folderManager::create(systemRoot.$accountFolder);
			
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
		$accountInfo = $this->getParentInstance()->info($accountID);
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
		return $this->getParentInstance()->removeAccount($accountID);
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
	
	/**
	 * Get an IDAccount instance to act as 'parent'.
	 * This function is for compatibility reasons, to get from static to singleton.
	 * 
	 * @return	IDAccount
	 * 		The IDAccount instance.
	 */
	private function getParentInstance()
	{
		return IDAccount::getInstance(account::ID_TEAM_NAME);
	}
	
	/**
	 * Get an accountSession instance for the current account.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	protected function getAccountSessionInstance()
	{
		return accountSession::getInstance($this->getMX());
	}
}
//#section_end#
?>