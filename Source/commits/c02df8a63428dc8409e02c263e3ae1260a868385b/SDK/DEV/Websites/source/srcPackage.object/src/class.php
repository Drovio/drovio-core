<?php
//#section#[header]
// Namespace
namespace DEV\Websites\source;

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
 * @namespace	\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Websites", "website");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");

use \DEV\Websites\website;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;

/**
 * Website Source Package Manager
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	September 19, 2014, 14:24 (EEST)
 * @revised	September 19, 2014, 14:24 (EEST)
 */
class srcPackage
{
	/**
	 * The website object.
	 * 
	 * @type	website
	 */
	private $website;
	
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
	 * @param	integer	$websiteID
	 * 		The website id.
	 * 
	 * @return	void
	 */
	public function __construct($websiteID)
	{
		// Init application
		$this->website = new website($websiteID);
		$this->vcs = new vcs($websiteID);
		
		// Load source map
		$this->loadSourceMap();
	}
	
	/**
	 * Creates a new package in the website's source.
	 * 
	 * @param	string	$library
	 * 		The library name.
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
	 * Creates a namespace in the website's source.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::" or by "/").
	 * 		It is empty by default.
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
	 * Get all packages in the website source library.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all application source packages.
	 */
	public function getList($library)
	{
		$this->loadSourceMap();
		return $this->sourceMap->getPackageList($library);
	}
	
	/**
	 * Get a list of namespaces in the website source.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::" or by "/").
	 * 		It is empty by default.
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
	 * 		The parent namespace (separated by "::" or by "/").
	 * 		It is empty by default.
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
			// Get source index file path
			$itemID = $this->website->getItemID("sourceIndex");
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
		$itemID = $this->website->getItemID("sourceIndex");
		$this->vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>