<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\sdk;

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
 * @package	WebEngine
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "WebEngine", "sdk::webLibrary");
importer::import("DEV", "WebEngine", "sdk::webObject");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");

use \ESS\Protocol\client\BootLoader;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Prototype\sourceMap;
use \DEV\WebEngine\sdk\webLibrary;
use \DEV\WebEngine\sdk\webObject;
use \DEV\Projects\project;
use \DEV\Version\vcs;

/**
 * Web SDK Package Manager
 * 
 * Handles all operations with Web SDK packages.
 * 
 * @version	{empty}
 * @created	April 4, 2014, 11:34 (EEST)
 * @revised	May 29, 2014, 11:46 (EEST)
 */
class webPackage
{
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Create a new SDK package.
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
		// Update map file vcs item
		$this->updateMapFile();
		
		// Create package in map.xml
		$this->loadSourceMap();
		return $this->sourceMap->createPackage($library, $package);
	}
	
	/**
	 * Remove a package from the web SDK.
	 * The package must be empty.
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
	public function remove($library, $package)
	{
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		$status = FALSE;
		try
		{
			$status = $this->sourceMap->deletePackage($library, $package);
		}
		catch (Exception $ex)
		{
			$status = FALSE;
		}
		
		// Update vcs map file
		if ($status)
			$this->updateMapFile();
		
		return $status;
	}
	
	/**
	 * Create a new namespace in the web SDK.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNS($library, $package, $namespace, $parentNs = "")
	{
		// Update map file vcs item
		$this->updateMapFile();
		
		// Create namespace in map.xml
		$this->loadSourceMap();
		return $this->sourceMap->createNamespace($library, $package, $namespace, $parentNs);
	}
	
	/**
	 * Remove a namespace from the web SDK.
	 * The namespace must be empty.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$namespace
	 * 		The namespace, separated by "::".
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function removeNS($library, $package, $namespace)
	{
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Create index
		$this->loadSourceMap();
		$status = FALSE;
		try
		{
			$status = $this->sourceMap->deleteNamespace($library, $package, $namespace);
		}
		catch (Exception $ex)
		{
			$status = FALSE;
		}
		
		// Update vcs map file
		if ($status)
			$this->updateMapFile();
		
		return $status;
	}
	
	/**
	 * Gets all namespaces in a given package, with an optional parent namespace.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		A nested array of namespaces.
	 */
	public function getNSList($library, $package, $parentNs = "")
	{
		$this->loadSourceMap();
		return $this->sourceMap->getNSList($library, $package, $parentNs);
	}
	
	/**
	 * Loads all objects in the given package.
	 * If the package is tester, it loads the objects from the repository trunk.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public function load($library, $package = "")
	{
		// If packageName not given return;
		if (empty($package))
			return;
/*
		// Check tester status
		if (sdkTester::libPackageStatus($library, $package))
		{
			// Load from repositories
			$this->loadSourceMap();
			$pkgObjects = $this->sourceMap->getObjectList($library, $package);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($library, $package, $object["namespace"], $object["name"]);
				$sdkObject->loadSourceCode();
			}
		}
		else
		{*/
			// Load from exported
			$sourceMap = new sourceMap(systemRoot.webLibrary::RELEASE_PATH, "map.xml");
			$pkgObjects = $sourceMap->getObjectList($library, $package);
			foreach ($pkgObjects as $object)
			{
				$objectName = (empty($object['namespace']) ? "" : $object['namespace']."::").$object['name'];
				importer::incl(self::getReleaseObjectPath($library, $package, $objectName), TRUE, TRUE);
			}
		//}
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
			// Get map file trunk path
			$vcs = new vcs(3);
			$itemID = webLibrary::getMapfileID();
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
		// Update map file
		$vcs = new vcs(3);
		$itemID = webLibrary::getMapfileID();
		$vcs->updateItem($itemID, TRUE);
	}
	
	/**
	 * Gets the release object path.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @param	string	$object
	 * 		The object name (including namespace separated by "::").
	 * 
	 * @return	string
	 * 		The release object path.
	 */
	private static function getReleaseObjectPath($library, $package, $object)
	{
		return systemRoot."/System/Library/Web/SDK/".$library."/".$package."/".str_replace("::", "/", $object).".php";
	}
	
	/**
	 * Loads a style package.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	string
	 * 		The entire package css code.
	 */
	public function loadCSS($library, $package)
	{
		// Get package objects
		$this->loadSourceMap();
		$objects = $this->sourceMap->getObjectList($library, $package);

		// Import package objects
		$packageCSS = "";
		foreach ($objects as $object)
		{
			$webObj = new webObject($library, $package, $object['namespace'], $object['name']);
			$packageCSS .= $webObj->getCSSCode()."\n";
		}
		
		// Replace resource vars
		$project = new project(3);
		$resourcePath = $project->getResourcesFolder();
		$packageCSS = str_replace("%resources%", $resourcePath, $packageCSS);
		
		return $packageCSS;
	}
	
	/**
	 * Loads a javascript package.
	 * 
	 * @param	string	$library
	 * 		The library name.
	 * 
	 * @param	string	$package
	 * 		The package name.
	 * 
	 * @return	string
	 * 		The entire package javascript code.
	 */
	public function loadJS($library, $package)
	{
		// Get package objects
		$this->loadSourceMap();
		$objects = $this->sourceMap->getObjectList($library, $package);

		// Import package objects
		$packageJS = "";
		foreach ($objects as $object)
		{
			$webObj = new webObject($library, $package, $object['namespace'], $object['name']);
			$packageJS .= $webObj->loadJSCode();
		}
		
		return $packageJS;
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
	public function getPackageObjects($library, $package, $namespace = NULL)
	{
		$this->loadSourceMap();
		if (!is_null($namespace))
			$namespace = str_replace("_", "::", $namespace);
		return $this->sourceMap->getObjectList($library, $package, $namespace);
	}
}
//#section_end#
?>