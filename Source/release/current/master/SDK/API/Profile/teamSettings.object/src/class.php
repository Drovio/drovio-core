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

importer::import("API", "Profile", "team");
importer::import("API", "Resources", "settingsManager");

use \API\Profile\team;
use \API\Resources\settingsManager;

/**
 * Team Settings Manager
 * 
 * Manages the settings of a team.
 * It can be used only for the current team when in secure mode.
 * 
 * @version	0.1-1
 * @created	March 31, 2015, 16:28 (EEST)
 * @updated	March 31, 2015, 16:28 (EEST)
 */
class teamSettings extends settingsManager
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
	 * @param	integer	$teamID
	 * 		The team id to get initialize the settings for.
	 * 		
	 * 		NOTICE: This doesn't work when in secure mode.
	 * 
	 * @return	void
	 */
	public function __construct($teamID = "")
	{
		// Get Team Settings folder
		$settingsFolder = team::getTeamFolder($teamID);
		parent::__construct($settingsFolder, $fileName = "settings", $rootRelative = TRUE);
		
		// Create team settings (in case it doesn't exist)
		$this->create();
	}
	
	/**
	 * Sets the value for a team settings property.
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