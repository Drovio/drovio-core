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

importer::import("API", "Profile", "account");
importer::import("API", "Resources", "settingsManager");

use \API\Profile\account;
use \API\Resources\settingsManager;

/**
 * Account Settings Manager
 * 
 * Manages the settings of the current account.
 * 
 * @version	0.1-1
 * @created	October 17, 2013, 15:53 (EEST)
 * @revised	August 1, 2014, 13:03 (EEST)
 */
class accountSettings
{
	/**
	 * The resource settingsManager object.
	 * 
	 * @type	SettingsManager
	 */
	private static $settingsManager;
	
	/**
	 * Initializes the settings manager.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Get Account Settings folder
		$settingsFolder = account::getAccountFolder();
		$this->settingsManager = new settingsManager($settingsFolder, $fileName = "settings", $rootRelative = TRUE);
		
		// Create account settings (in case it doesn't exist)
		$this->settingsManager->create();
	}
	
	/**
	 * Gets the value of an account property.
	 * 
	 * @param	string	$name
	 * 		The settings name.
	 * 
	 * @return	string
	 * 		The settings value.
	 */
	public function getSettingsValue($name)
	{
		if (empty($name))
			return NULL;
		
		return $this->settingsManager->get($name);
	}
	
	/**
	 * Sets the account's settings value by name.
	 * 
	 * @param	string	$name
	 * 		The settings name.
	 * 
	 * @param	string	$value
	 * 		The settings value.
	 * 
	 * @return	void
	 */
	public function setSettingsValue($name, $value)
	{
		return $this->settingsManager->set($name, $value, settingsManager::SCOPE_USER);
	}
}
//#section_end#
?>