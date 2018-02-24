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
 * Abstract Service Manager
 * 
 * Manages all account's service operations.
 * 
 * @version	{empty}
 * @created	April 19, 2013, 12:50 (EEST)
 * @revised	July 31, 2013, 14:20 (EEST)
 */
class ServiceManager extends AccountManager
{
	/**
	 * Returns the account's service root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @return	string
	 * 		The account service folder path.
	 */
	public static function getAccountFolder($serviceName)
	{
		$serviceFolder = parent::getAccountFolder()."/Services/".$serviceName."/";
		
		if (!file_exists(systemRoot.$serviceFolder))
			folderManager::create(systemRoot.$serviceFolder);
			
		return $serviceFolder;
	}
	
	/**
	 * Returns the copmany's service root folder.
	 * 
	 * @param	string	$serviceName
	 * 		The service name.
	 * 
	 * @return	string
	 * 		The company service folder path.
	 */
	public static function getCompanyFolder($serviceName)
	{
		$serviceFolder = parent::getCompanyFolder()."/Services/".$serviceName."/";
		
		if (!file_exists(systemRoot.$serviceFolder))
			folderManager::create(systemRoot.$serviceFolder);
			
		return $serviceFolder;
	}
}
//#section_end#
?>