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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
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
 * @version	2.0-1
 * @created	October 17, 2013, 15:53 (EEST)
 * @updated	March 31, 2015, 9:50 (EEST)
 */
class accountSettings extends settingsManager
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
		parent::__construct($settingsFolder, $fileName = "settings", $rootRelative = TRUE);
		
		// Create account settings (in case it doesn't exist)
		$this->create();
	}
	
	/**
	 * Sets the value for an account settings property.
	 * 
	 * @param	string	$name
	 * 		The settings name.
	 * 
	 * @param	string	$value
	 * 		The settings value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($name, $value)
	{
		return parent::set($name, $value, parent::SCOPE_USER);
	}
}
//#section_end#
?>