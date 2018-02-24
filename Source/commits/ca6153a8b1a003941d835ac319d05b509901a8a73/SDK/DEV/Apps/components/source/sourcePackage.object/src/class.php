<?php
//#section#[header]
// Namespace
namespace DEV\Apps\components\source;

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
 * @namespace	\components\source
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
 * Application Source Package Manager
 * 
 * @version	{empty}
 * @created	April 6, 2014, 1:11 (EEST)
 * @revised	April 6, 2014, 1:11 (EEST)
 */
class sourcePackage
{
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
	 * Initializes the object..
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
	}
	
	/**
	 * Creates a new package in the application's source.
	 * 
	 * @param	string	$library
	 * 		The library name
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($library, $package)
	{
		// Update vcs item
		$this->updateMapFile();
		
		// Create package
		$this->loadSourceMap();
		return $this->sourceMap->createPackage($library, $package);
	}
	
	/**
	 * Creates a namespace in the application's source.
	 * 
	 * @param	string	$library
	 * 		The library name
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNS($library, $package, $namespace, $parentNs = "")
	{
		// Update vcs item
		$this->updateMapFile();
		
		// Create namespace
		$this->loadSourceMap();
		return $this->sourceMap->createNamespace($library, $package, $namespace, $parentNs);
	}
	
	/**
	 * Get a list of namespaces in the application source.
	 * 
	 * @param	string	$library
	 * 		The package name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 
	 * @return	array
	 * 		An array of namespaces by key and value.
	 */
	public function getNSList($library, $package, $parentNs = "")
	{
		$this->loadSourceMap();
		return $this->sourceMap->getNSList($library, $package, $parentNs);
	}
	
	/**
	 * Get all objects in a package.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		An array of the object information including title, name, package, library and namespace.
	 */
	public function getObjects($library, $package, $namespace = NULL)
	{
		if (!is_null($namespace))
			$namespace = str_replace("_", "::", $namespace);
		$this->loadSourceMap();
		return $this->sourceMap->getObjectList($library, $package, $namespace);
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
			// Init vcs
			$repository = $this->app->getRepository();
			$vcs = new vcs($repository);
			
			// Get source index file path
			$itemID = $this->app->getItemID("sourceIndex");
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
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
		// Init vcs
		$repository = $this->app->getRepository();
		$vcs = new vcs($repository);
		
		// Get source index file path
		$itemID = $this->app->getItemID("sourceIndex");
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>