<?php
//#section#[header]
// Namespace
namespace DEV\Websites;

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
 * @namespace	\
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
 * Website Settigs / Info manager class
 * 
 * Manages the content of the setting.xml file
 * 
 * @version	5.0-1
 * @created	September 12, 2014, 22:06 (EEST)
 * @revised	January 1, 2015, 20:14 (EET)
 */
class wsSettings extends settingsManager
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
	 * Initializes the object and calls the parent constructor.
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
		$itemName = "settings";
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
	}
	
	/**
	 * Create the website settings file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if settings file already exists.
	 */
	public function create()
	{
		// Check if item already exists
		$itemID = $this->getItemID();
		$settingsFilePath = $this->vcs->getItemTrunkPath($itemID);
		if (!empty($settingsFilePath))
			return;
			
		// Create item
		$itemPath = "/";
		$itemName = "settings";
		$settingsFilePath = $this->vcs->createItem($itemID, $itemPath, $itemName.".xml", $isFolder = FALSE);
		
		// Construct parent to initialize paths
		$settingsFolder = dirname($settingsFilePath);
		parent::__construct($settingsFolder, $itemName, $rootRelative = FALSE);
		
		// Initialize settings file
		return parent::create();
	}
	
	/**
	 * Gets the default meta keywords value for the website.
	 * 
	 * @return	string
	 * 		The default meta keywords value.
	 */
	public function getMetaKeywords()
	{
		// Set value
		return $this->get("meta_keywords");
	}
	
	/**
	 * Sets the default meta keywords value for the website.
	 * 
	 * @param	string	$value
	 * 		The new default meta keywords value.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	public function setMetaKeywords($value = "")
	{
		// Set value
		return $this->set("meta_keywords", $value);
	}
	
	/**
	 * Gets the default meta description value for the website.
	 * 
	 * @return	string
	 * 		The default meta description value.
	 */
	public function getMetaDescription()
	{
		// Set value
		return $this->get("meta_description");
	}
	
	/**
	 * Sets the default meta description value for the website.
	 * 
	 * @param	string	$value
	 * 		The new default meta description value.
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	public function setMetaDescription($value = "")
	{
		// Set value
		return $this->set("meta_description", $value);
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
	 * 		True on success, false elsewhere
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
	 * 		The vcs item id value.
	 */
	private function getItemID()
	{
		return $this->website->getItemID("settings");
	}
}
//#section_end#
?>