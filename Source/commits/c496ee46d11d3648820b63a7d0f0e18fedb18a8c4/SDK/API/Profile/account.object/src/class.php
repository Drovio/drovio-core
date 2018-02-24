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

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("DEV", "Resources", "paths");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Security\account as sAccount;
use \API\Resources\filesystem\folderManager;
use \DEV\Resources\paths;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	1.0-2
 * @created	July 5, 2013, 12:38 (EEST)
 * @revised	August 11, 2014, 13:02 (EEST)
 */
class account
{
	/**
	 * All the stored account data.
	 * 
	 * @type	array
	 */
	private static $accountData = array();
	
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
	 * Checks whether the account is admin.
	 * 
	 * @return	boolean
	 * 		True if admin, false otherwise (shared).
	 */
	public static function isAdmin()
	{
		return (self::getAccountValue("administrator") == TRUE);
	}
	
	/**
	 * Gets the account title for the logged in account.
	 * 
	 * @return	string
	 * 		The account display title.
	 */
	public static function getAccountTitle()
	{
		return self::getAccountValue("accountTitle");
	}
	
	/**
	 * Gets an account value from the session.
	 * If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The value name.
	 * 
	 * @return	string
	 * 		The account value.
	 */
	private static function getAccountValue($name)
	{
		// Check session existance
		if (!isset(self::$accountData[$name]))
			self::$accountData = self::info();
			
		return self::$accountData[$name];
	}
	
	/**
	 * Gets the account info.
	 * 
	 * @return	array
	 * 		Returns an array of the account information.
	 */
	public static function info()
	{
		if (!sAccount::validate())
			return NULL;
		
		return self::getInfo(sAccount::getAccountID());
	}
	
	/**
	 * Gets an account's information.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get information for.
	 * 
	 * @return	array
	 * 		Array of account data.
	 */
	private static function getInfo($accountID)
	{
		$dbc = new dbConnection();
		$q = new dbQuery("177361907", "profile.account");
		$attr = array();
		$attr['id'] = $accountID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
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