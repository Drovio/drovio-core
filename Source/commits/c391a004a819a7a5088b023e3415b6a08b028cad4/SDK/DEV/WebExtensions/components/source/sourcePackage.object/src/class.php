<?php
//#section#[header]
// Namespace
namespace DEV\WebExtensions\components\source;

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
 * @package	WebExtensions
 * @namespace	\components\source
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "WebExtensions", "extension");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Version", "vcs");

use \DEV\WebExtensions\extension;
use \DEV\Prototype\sourceMap;
use \DEV\Version\vcs;

/**
 * Extension Source Package
 * 
 * Extension Source Package Manager
 * 
 * @version	{empty}
 * @created	May 22, 2014, 19:01 (EEST)
 * @revised	May 22, 2014, 19:01 (EEST)
 */
class sourcePackage
{
	/**
	 * The extension object.
	 * 
	 * @type	extension
	 */
	private $ext;
	
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Initializes the object.
	 * 
	 * @param	integer	$extID
	 * 		The extension id.
	 * 
	 * @return	void
	 */
	public function __construct($extID)
	{
		// Init application
		$this->ext = new extension($extID);
	}
	
	/**
	 * Creates a new package in the extension's source.
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
	 * Creates a namespace in the extension's source.
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
	 * Get a list of namespaces in the extension source.
	 * 
	 * @param	string	$library
	 * 		The library name.
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
			$vcs = new vcs($this->ext->getID());
			
			// Get source index file path
			$itemID = $this->ext->getItemID("sourceIndex");
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
		$vcs = new vcs($this->ext->getID());
		
		// Get source index file path
		$itemID = $this->ext->getItemID("sourceIndex");
		$vcs->updateItem($itemID, TRUE);
	}
}
//#section_end#
?>