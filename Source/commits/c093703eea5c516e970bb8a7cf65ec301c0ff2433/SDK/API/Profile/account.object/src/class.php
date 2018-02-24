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
importer::import("API", "Resources", "filesystem/fileManager");
importer::import("API", "Resources", "filesystem/folderManager");
importer::import("DEV", "Resources", "paths");
importer::import("DRVC", "Profile", "account");

use \ESS\Environment\url;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;
use \DRVC\Profile\account as IDAccount;

/**
 * Account Manager Class
 * 
 * Singleton class for managing the drovio account.
 * 
 * @version	10.0-2
 * @created	July 5, 2013, 10:38 (BST)
 * @updated	October 22, 2015, 14:10 (BST)
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
		$imagePath = $this->getAccountFolderPath($accountID)."/media/profile.png";
		if (file_exists(systemRoot.$imagePath))
		{
			$imageUrl = str_replace(paths::getProfilePath(), "", $imagePath);
			$imageUrl = url::resolve("profile", $imageUrl);
			$publicInfo['profile_image_url'] = $imageUrl;
		}
		
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
}
//#section_end#
?>