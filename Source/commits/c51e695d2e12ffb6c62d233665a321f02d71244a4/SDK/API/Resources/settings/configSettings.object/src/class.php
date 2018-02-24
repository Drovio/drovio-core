<?php
//#section#[header]
// Namespace
namespace API\Resources\settings;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\settings
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "settingsManager");
importer::import("API", "Developer", "profiler::logger");

use \API\Resources\settingsManager;
use \API\Developer\profiler\logger;

/**
 * Configuration Settings Handler
 * 
 * Manages all system's configuration settings
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:01 (EEST)
 * @revised	April 23, 2013, 14:01 (EEST)
 */
class configSettings extends settingsManager
{
	/**
	 * Configuration Path
	 * 
	 * @type	string
	 */
	const PATH = "/System/Configuration";
	
	/**
	 * System's settings file path
	 * 
	 * @type	string
	 */
	const SETTINGS_PATH = "/System/Configuration/Settings/";
	/**
	 * The domain's folder settings root path
	 * 
	 * @type	string
	 */
	const DOMAIN_FOLDER = "Domains/";
	
	/**
	 * Constructor method.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function load($scope = "", $file = "Settings")
	{
	}
	
	/**
	 * Loads the settings file and returns all the values stored.
	 * 
	 * @param	string	$scope
	 * 		The settings' scope
	 * 
	 * @param	string	$file
	 * 		The settings' filename
	 * 
	 * @return	array
	 */
	public function load_settings($scope = "", $file = "Settings")
	{
		// Normalize domain
		$scope = str_replace(".", "/", $scope);
		
		// Set xml file
		$filename = $file.".xml";
		
		// Log activity
		logger::log("Loading settings from '".$scope."/".$filename."' ...");
		
		// Set path (global or domain's)
		$path = self::SETTINGS_PATH.$filename;

		$this->settingsFile = $path;

		// Load the file
		$this->load_file();
		
		return $this->get($scope);
	}
	
	/**
	 * Load domain settings file
	 * 
	 * @param	string	$domain
	 * 		The domain
	 * 
	 * @param	string	$file
	 * 		The domain settings' filename
	 * 
	 * @return	void
	 */
	public function load_domainSettings($domain, $file = "Settings")
	{
		// Normalize domain
		$filename = self::DOMAIN_FOLDER."/".$domain."/".$file.".xml";
		
		// Log activity
		logger::log("Loading settings from '".$filename."' ...");
		
		// Set path (global or domain's)
		$path = self::SETTINGS_PATH.$filename;
		$this->settingsFile = $path;
		
		// Load the file
		$this->load_file($path, $rootRelative = true);
	}
}
//#section_end#
?>