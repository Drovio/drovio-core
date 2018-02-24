<?php
//#section#[header]
// Namespace
namespace AEL\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Resources
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Resources", "appManager");
importer::import("API", "Resources", "settingsManager");

use \AEL\Resources\appManager;
use \API\Resources\settingsManager;

/**
 * Application settings manager
 * 
 * Manages settings for an application.
 * 
 * @version	0.1-1
 * @created	October 6, 2015, 2:29 (EEST)
 * @updated	October 6, 2015, 2:29 (EEST)
 */
class appSettings extends settingsManager
{
	/**
	 * The account settings mode.
	 * 
	 * @type	integer
	 */
	const ACCOUNT_MODE = 1;
	
	/**
	 * The team settings mode.
	 * 
	 * @type	integer
	 */
	const TEAM_MODE = 2;
	
	/**
	 * Initialize the settings manager.
	 * 
	 * @param	integer	$mode
	 * 		The settings mode.
	 * 		See class constants for options.
	 * 		It is in account mode by default.
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, the appSettings will have access to the shared application data folder.
	 * 		It is FALSE by default.
	 * 
	 * @param	string	$settingsFolder
	 * 		The settings' file folder path.
	 * 		Default value is '/Settings/'.
	 * 
	 * @param	string	$filename
	 * 		The settings file name.
	 * 		Default value is 'Settings'.
	 * 
	 * @return	void
	 */
	public function __construct($mode = self::ACCOUNT_MODE, $shared = FALSE, $settingsFolder = "/Settings/", $filename = "Settings")
	{
		// Set settings path
		$rootFolder = appManager::getRootFolder($mode, $shared);
		if (empty($rootFolder))
			return;

		// Construct settingsManager
		parent::__construct($rootFolder."/".$settingsFolder, $filename, TRUE);
		
		// Create (if not exist)
		parent::create();
	}
}
//#section_end#
?>