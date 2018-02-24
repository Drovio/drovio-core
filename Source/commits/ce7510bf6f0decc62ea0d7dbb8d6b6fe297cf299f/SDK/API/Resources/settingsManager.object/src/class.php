<?php
//#section#[header]
// Namespace
namespace API\Resources;

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
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "DOMParser");
importer::import("API", "Resources", "filesystem/fileManager");

use \API\Resources\DOMParser;
use \API\Resources\filesystem\fileManager;

/**
 * System Settings Manager
 * 
 * Handles all settings files.
 * 
 * @version	0.1-3
 * @created	April 23, 2013, 14:04 (EEST)
 * @revised	January 2, 2015, 11:45 (EET)
 */
class settingsManager
{
	/**
	 * The system's scope.
	 * 
	 * @type	string
	 */
	const SCOPE_SYSTEM = "System";
	/**
	 * The user's scope.
	 * 
	 * @type	string
	 */
	const SCOPE_USER = "User";
	
	/**
	 * The local DOMParser
	 * 
	 * @type	DOMParser
	 */
	protected $xmlParser;
	/**
	 * The settings filepath
	 * 
	 * @type	string
	 */
	protected $settingsFile;
	
	/**
	 * Whether the settings file contains the systemRoot.
	 * 
	 * @type	boolean
	 */
	private $rootRelative;
	
	/**
	 * Constructor method.
	 * Initializes the DOMParser.
	 * 
	 * @param	string	$path
	 * 		The settings' file folder path.
	 * 
	 * @param	string	$fileName
	 * 		The settings file name.
	 * 
	 * @param	boolean	$rootRelative
	 * 		Whether the folder path contains the systemRoot.
	 * 
	 * @return	void
	 */
	public function __construct($path, $fileName = "settings", $rootRelative = TRUE)
	{
		// Initialize dom parser
		$this->xmlParser = new DOMParser();
		
		// Set class variables
		$fileName = (empty($fileName) ? "settings" : $fileName);
		$this->settingsFile = $path."/".$fileName.".xml";
		$this->rootRelative = $rootRelative;

		// If file exists, load settings
		$fileName = ($rootRelative ? systemRoot.$this->settingsFile : $this->settingsFile);
		if (file_exists($fileName))
			$this->load();
	}
	
	/**
	 * Creates the settings file.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure or if settings file already exists.
	 */
	public function create()
	{
		// Set filename
		$fileName = ($this->rootRelative ? systemRoot.$this->settingsFile : $this->settingsFile);
		
		// Check if the file already exists
		if (file_exists($fileName))
			return FALSE;
		
		// Create settings file
		fileManager::create($fileName, "", TRUE);
		
		// Init settings File
		$this->xmlParser = new DOMParser();
		$root = $this->xmlParser->create("Settings");
		$this->xmlParser->append($root);
		
		// Save file
		return $this->xmlParser->save($fileName, "", TRUE);
	}
	
	/**
	 * Loads the settings file.
	 * 
	 * @return	void
	 */
	public function load()
	{
		$this->xmlParser->load($this->settingsFile, $this->rootRelative);
	}
	
	/**
	 * Updates the settings file.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success, false on failure.
	 */
	public function update()
	{
		return $this->xmlParser->update();
	}
	
	/**
	 * Get the value of all properties or for a given name.
	 * 
	 * @param	string	$name
	 * 		The property's name.
	 * 
	 * @return	string
	 * 		The property value or an associative array of all properties.
	 */
	public function get($name = "")
	{
		// Normalize name
		$name = strtoupper($name);
		
		// Get settings
		$settings = $this->xmlParser->evaluate("//PropertyValue");
		
		// Form result
		$result = array();
		foreach ($settings as $set)
			$result[$this->xmlParser->attr($set, "name")] = $this->xmlParser->attr($set, "value");
		
		// Return the given key's value or all settings
		if (empty($name))
			return $result;
		else
			return $result[$name];
	}
	
	/**
	 * Set a value of a property. If the property doesn't exist, it will be created.
	 * 
	 * @param	string	$name
	 * 		The property's name.
	 * 
	 * @param	string	$value
	 * 		The property's new value. If NULL, the property will be removed. If empty
	 * 
	 * @param	string	$scope
	 * 		The property's scope.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success, false on failure.
	 */
	public function set($name, $value = "", $scope = self::SCOPE_SYSTEM)
	{
		// Normalize name
		$name = strtoupper($name);

		// Get root
		$root = $this->xmlParser->evaluate("/Settings")->item(0);
		
		// Get property
		$property = $this->xmlParser->evaluate("PropertyValue[@name='".$name."']")->item(0);
		if (is_null($property) && !empty($value))
		{
			$property = $this->xmlParser->create("PropertyValue");
			$this->xmlParser->attr($property, "name", $name);
			$this->xmlParser->attr($property, "value", $value);
			$this->xmlParser->attr($property, "scope", $scope);
			$this->xmlParser->append($root, $property);
			
			// Update file
			return $this->update();
		}
		
		// Delete property if value is null
		if (!is_null($property) && is_null($value))
		{
			$this->xmlParser->replace($property, NULL);
			$property = NULL;
		}

		// Update property value
		if (!is_null($property))
		{
			// Update the new value and scope
			$this->xmlParser->attr($property, "value", $value);
			$this->xmlParser->attr($property, "scope", $scope);
		}

		// Update file
		return $this->update();
	}
}
//#section_end#
?>