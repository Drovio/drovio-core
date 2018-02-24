<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "appcenter::application");
importer::import("API", "Resources", "settingsManager");

use \API\Developer\appcenter\application;
use \API\Resources\settingsManager;

/**
 * Application Settings
 * 
 * Application Settings Manager
 * 
 * @version	{empty}
 * @created	October 30, 2013, 13:36 (EET)
 * @revised	November 3, 2013, 10:28 (EET)
 */
class appSettings extends settingsManager
{
	/**
	 * The settings file type.
	 * 
	 * @type	string
	 */
	const FILE_TYPE = "xml";
	
	/**
	 * The application vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the object and calls the parent constructor.
	 * 
	 * @param	vcs	$vcs
	 * 		The application vcs manager object.
	 * 
	 * @return	void
	 */
	public function __construct($vcs)
	{
		// Initialize
		$this->vcs = $vcs;
		$settingsFileName = "Settings";
		
		// Create item (if doesn't exist)
		$itemID = $this->getItemID();
		$settingsFilePath = $this->vcs->createItem($itemID, application::CONFIG_FOLDER, $settingsFileName.".".self::FILE_TYPE, $isFolder = FALSE);
		
		// In case of item already exist, get path from trunk
		if (empty($settingsFilePath))
			$settingsFilePath = $this->vcs->getItemTrunkPath($itemID);
		
		// Construct parent
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $settingsFileName, $rootRelative = FALSE);
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
	 * @return	boolean
	 * 		Returns TRUE on success, false on failure.
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
	 * Gets the settings file content.
	 * 
	 * @return	string
	 * 		The settings xml file content.
	 */
	public function getXML()
	{
		return $this->xmlParser->getXML();
	}
	
	/**
	 * Gets the file's item id for the vcs.
	 * 
	 * @return	string
	 * 		The item id.
	 */
	private function getItemID()
	{
		return "set".md5("settings");
	}
}
//#section_end#
?>