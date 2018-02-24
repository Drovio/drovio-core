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

importer::import("API", "Resources", "filesystem::folderManager");
use \API\Resources\filesystem\folderManager;
/**
 * Abstract Account Manager
 * 
 * General account manager for all accounts (person and company) in the system.
 * 
 * @version	0.1-2
 * @created	April 19, 2013, 12:13 (EEST)
 * @revised	August 1, 2014, 13:11 (EEST)
 * 
 * @deprecated	Use \API\Profile\account instead.
 */
abstract class AccountManager
{
	/**
	 * Gets the account's folder. The folder is created if doesn't exist.
	 * 
	 * @return	mixed
	 * 		The account folder path. If there is no active account, it returns FALSE.
	 */
	public static function getAccountFolder()
	{
		// Get Account Folder
		$accountID = account::getAccountID();
		if (empty($accountID))
			return FALSE;
			
		$accFolderID = self::getFolderID("acc", $accountID, "account");
		$accountFolder = "/.profileData/Accounts/".$accFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$accountFolder))
			folderManager::create(systemRoot.$accountFolder);
			
		return $accountFolder;
	}
	
	/**
	 * Gets the company's folder. The folder is created if doesn't exist.
	 * 
	 * @return	mixed
	 * 		The company folder path. If there is no active company, return FALSE.
	 */
	public static function getCompanyFolder()
	{
		// Get Company Folder
		$companyID = account::getCompany();
		if (empty($companyID))
			return FALSE;

		$cmpFolderID = self::getFolderID("cmp", $companyID, "company");
		$companyFolder = "/.profileData/Companies/".$cmpFolderID."/";
		
		// Create folder if not exists
		if (!file_exists(systemRoot.$companyFolder))
			folderManager::create(systemRoot.$companyFolder);
			
		return $companyFolder;
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