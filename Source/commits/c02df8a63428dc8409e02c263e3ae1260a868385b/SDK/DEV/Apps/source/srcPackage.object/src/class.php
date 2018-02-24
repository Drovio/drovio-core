<?php
//#section#[header]
// Namespace
namespace DEV\Apps\source;

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
 * @namespace	\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Apps", "application");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");

use \DEV\Apps\application;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;

/**
 * Application Source Package
 * 
 * {description}
 * 
 * @version	0.1-2
 * @created	August 22, 2014, 12:37 (EEST)
 * @revised	September 19, 2014, 13:49 (EEST)
 */
class srcPackage
{
	/**
	 * The application's only library name.
	 * 
	 * @type	string
	 */
	const LIB_NAME = "APP";
	
	/**
	 * The application object.
	 * 
	 * @type	application
	 */
	private $app;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * The vcs manager object
	 * 
	 * @type	vcs
	 */
	private $vcs;
	
	/**
	 * Initializes the object.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	void
	 */
	public function __construct($appID)
	{
		// Init application
		$this->app = new application($appID);
		$this->vcs = new vcs($appID);
	}
	
	/**
	 * Initializes the application's source during creation of application.
	 * 
	 * @return	void
	 */
	public function init()
	{
		// Update vcs item
		$this->updateMapFile();
		
		// Create application library
		$this->loadSourceMap();
		$this->sourceMap->createLibrary(self::LIB_NAME);
	}
	
	/**
	 * Creates a new package in the application's source.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($package)
	{
		// Update vcs item
		$this->updateMapFile();
		
		// Create package
		$this->loadSourceMap();
		return $this->sourceMap->createPackage(self::LIB_NAME, $package);
	}
	
	/**
	 * Creates a namespace in the application's source.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNS($package, $namespace, $parentNs = "")
	{
		// Update vcs item
		$this->updateMapFile();
		
		// Create namespace
		$this->loadSourceMap();
		return $this->sourceMap->createNamespace(self::LIB_NAME, $package, $namespace, $parentNs);
	}
	
	/**
	 * Get all packages in the application source library.
	 * 
	 * @return	array
	 * 		An array of all application source packages
	 */
	public function getList()
	{
		$this->loadSourceMap();
		return $this->sourceMap->getPackageList(self::LIB_NAME);
	}
	
	/**
	 * Get a list of namespaces in the application source.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of namespaces by key and value.
	 */
	public function getNSList($package, $parentNs = "")
	{
		$this->loadSourceMap();
		return $this->sourceMap->getNSList(self::LIB_NAME, $package, $parentNs);
	}
	
	/**
	 * Get all objects in a package.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The parent namespace (separated by "::").
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of the object information including title, name, package, library and namespace.
	 */
	public function getObjects($package, $namespace = NULL)
	{
		if (!is_null($namespace))
			$namespace = str_replace("_", "::", $namespace);
		$this->loadSourceMap();
		return $this->sourceMap->getObjectList(self::LIB_NAME, $package, $namespace);
	}
	
	/**
	 * Initializes the source map object for getting the source information from the source index.
	 * 
	 * @return	object
	 * 		The sourceMap object.
	 */
	private function loadSourceMap()
	{
		if (empty($this->sourceMap))
		{
			// Get source index file path
			$itemID = $this->app->getItemID("sourceIndex");
			$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
			$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return $this->sourceMap;
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Get source index file path
		$itemID = $this->app->getItemID("sourceIndex");
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>