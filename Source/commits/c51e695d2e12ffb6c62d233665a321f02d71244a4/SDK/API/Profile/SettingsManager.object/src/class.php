<?php
//#section#[header]
// Namespace
namespace API\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "AccountManager");
importer::import("API", "Resources", "filesystem::folderManager");

use \API\Profile\AccountManager;
use \API\Resources\filesystem\folderManager;

/**
 * Abstract Account Settings Manager
 * 
 * Manages all the settings of an account.
 * 
 * @version	{empty}
 * @created	April 19, 2013, 12:28 (EEST)
 * @revised	July 31, 2013, 14:20 (EEST)
 */
class SettingsManager extends AccountManager
{
	/**
	 * Gets the account settings folder path.
	 * 
	 * @return	string
	 * 		The account settings folder path.
	 */
	public static function getAccountFolder()
	{
		$serviceFolder = parent::getAccountFolder()."/Settings/";
		
		if (!file_exists(systemRoot.$serviceFolder))
			folderManager::create(systemRoot.$serviceFolder);
			
		return $serviceFolder;
	}
	
	/**
	 * Returns the company settings folder path.
	 * 
	 * @return	string
	 * 		The company settings folder.
	 */
	public static function getCompanyFolder()
	{
		$serviceFolder = parent::getCompanyFolder()."/Settings/";
		
		if (!file_exists(systemRoot.$serviceFolder))
			folderManager::create(systemRoot.$serviceFolder);
			
		return $serviceFolder;
	}
}
//#section_end#
?>