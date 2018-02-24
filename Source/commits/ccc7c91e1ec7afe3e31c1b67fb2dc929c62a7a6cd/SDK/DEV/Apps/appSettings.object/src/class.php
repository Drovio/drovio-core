<?php
//#section#[header]
// Namespace
namespace DEV\Apps;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Apps
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Apps", "application");
importer::import("DEV", "Version", "vcs");

use \API\Resources\settingsManager;
use \DEV\Apps\application;
use \DEV\Version\vcs;

/**
 * Application Settings
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	August 22, 2014, 13:55 (EEST)
 * @revised	August 22, 2014, 13:55 (EEST)
 */
class appSettings extends settingsManager
{
	/**
	 * The application vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the object and calls the parent constructor.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public function __construct($appID)
	{
		// Init vcs and variables
		$app = new application($appID);
		$this->vcs = new vcs($appID);
		
		// Create item (if doesn't exist)
		$itemID = $this->getItemID();
		$itemPath = "/";
		$itemName = "settings";
		$settingsFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName.".xml", $isFolder = FALSE);
		
		// In case of item already exist, get path from trunk
		if (empty($settingsFilePath))
			$settingsFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Construct parent
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
	}
	
	/**
	 * Sets the value of a property given by name.
	 * 
	 * @param	string	$name
	 * 		The property name.
	 * 
	 * @param	string	$value
	 * 		The property new value.
	 * 
	 * @param	string	$scope
	 * 		The property scope, as defined in the settingsManager class.
	 * 
	 * @return	void
	 */
	public function set($name, $value = "", $scope = parent::SCOPE_SYSTEM)
	{
		// Update settings item first
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Set value
		return parent::set($name, $value, $scope);
	}
	
	/**
	 * Gets the file's item id for the vcs.
	 * 
	 * @return	string
	 * 		The vcs item hash id.
	 */
	private function getItemID()
	{
		return "set".md5("settings");
	}
}
//#section_end#
?>