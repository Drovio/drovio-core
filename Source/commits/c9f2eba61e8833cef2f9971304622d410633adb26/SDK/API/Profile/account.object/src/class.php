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

importer::import("API", "Security", "account");
importer::import("DEV", "Resources", "paths");

use \API\Security\account as sAccount;
use \DEV\Resources\paths;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	0.1-3
 * @created	July 5, 2013, 12:38 (EEST)
 * @revised	August 1, 2014, 13:05 (EEST)
 */
class account
{
	/**
	 * Gets the account's folder. The folder is created if doesn't exist.
	 * 
	 * @return	mixed
	 * 		The account folder path.
	 * 		If there is no active account, it returns FALSE.
	 */
	public static function getAccountFolder()
	{
		// Get Account Folder
		$accountID = sAccount::getAccountID();
		if (empty($accountID))
			return FALSE;
			
		$accFolderID = self::getFolderID("acc", $accountID, "account");
		$accountFolder = paths::getProfilePath()."/Accounts/".$accFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$accountFolder))
			folderManager::create(systemRoot.$accountFolder);
			
		return $accountFolder;
	}
	
	/**
	 * Get the account's service root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @return	void
	 */
	public static function getServicesFolder($serviceName)
	{
		$serviceFolder = self::getAccountFolder()."/Services/".$serviceName."/";
		
		if ($serviceFolder && !file_exists(systemRoot.$serviceFolder))
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