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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @version	3.0-1
 * @created	September 12, 2014, 22:06 (EEST)
 * @revised	October 11, 2014, 16:21 (EEST)
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
	 * The contructor method
	 * 
	 * @param	integer	$id
	 * 		The project id
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		// Init vcs and variables
		$this->website = new website($id);
		$this->vcs = new vcs($id);
		
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
		
		//$this->create();
	}
	
	/**
	 * Defines the meta keywords object / tag for the website.
	 * 
	 * @param	string	$value
	 * 		The meta keywords value
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	public function setMetaKeywords($value = "")
	{
		// Set value
		return $this->set("keywords", $value);
	}
	
	/**
	 * Defines the meta description object / tag for the website.
	 * 
	 * @param	string	$value
	 * 		The meta description value
	 * 
	 * @return	boolean
	 * 		True on success, false elsewhere
	 */
	public function setMetaDescription($value = "")
	{
		// Set value
		return $this->set("description", $value);
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
	 * 		{description}
	 */
	private function getItemID()
	{
		return $this->website->getItemID("settings");
	}
}
//#section_end#
?>