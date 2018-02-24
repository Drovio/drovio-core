<?php
//#section#[header]
// Namespace
namespace DEV\Websites\settings;

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
 * @package	Websites
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "settingsManager");
importer::import("DEV", "Websites", "website");
importer::import("DEV", "Version", "vcs");

use \API\Resources\settingsManager;
use \DEV\Websites\website;
use \DEV\Version\vcs;

/**
 * Website Meta Settings
 * 
 * Manages meta website settings like keywords, description and open graph (meta_settings.xml).
 * 
 * @version	0.1-1
 * @created	January 2, 2015, 12:28 (EET)
 * @revised	January 2, 2015, 12:28 (EET)
 */
class metaSettings extends settingsManager
{
	/**
	 * The website object.
	 * 
	 * @type	website
	 */
	private $website;
	
	/**
	 * The website vcs manager object.
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the website meta settings..
	 * 
	 * @param	integer	$id
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		// Init vcs and variables
		$this->website = new website($id);
		$this->vcs = new vcs($id);
		
		// Get path from trunk
		$itemID = $this->getItemID();
		$settingsFilePath = $this->vcs->getItemTrunkPath($itemID);
		if (empty($settingsFilePath))
			return;
		
		// Construct parent
		$itemName = "meta_settings";
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
	}
	
	/**
	 * Create the website settings file.
	 * 
	 * @return	boolean
	 * 		True on success or if the file already exists, false on failure.
	 */
	public function create()
	{
		// Check if item already exists
		$itemID = $this->getItemID();
		$settingsFilePath = $this->vcs->getItemTrunkPath($itemID);
		if (!empty($settingsFilePath))
			return;
			
		// Create item
		$itemPath = website::SETTINGS_FOLDER;
		$itemName = "meta_settings";
		$settingsFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName.".xml", $isFolder = FALSE);
		
		// Construct parent to initialize paths
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
		
		// Initialize settings file
		return parent::create();
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
	 * 		True on success, false elsewhere.
	 */
	public function set($name, $value = "", $scope = parent::SCOPE_SYSTEM)
	{
		// Update settings item first
		$itemID = $this->getItemID();
		$this->vcs->updateItem($itemID);
		
		// Set value
		$value = (empty($value) ? NULL : $value);
		return parent::set($name, $value, $scope);
	}
	
	/**
	 * Gets the file's item id for the vcs.
	 * 
	 * @return	string
	 * 		The vcs item id value.
	 */
	private function getItemID()
	{
		return $this->website->getItemID("meta_settings");
	}
}
//#section_end#
?>