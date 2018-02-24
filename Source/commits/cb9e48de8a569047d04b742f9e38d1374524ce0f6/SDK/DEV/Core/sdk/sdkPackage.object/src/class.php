<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
 * @package	Core
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");
importer::import("ESS", "Environment", "url");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Core", "sdk::sdkLibrary");
importer::import("DEV", "Core", "sdk::sdkObject");
importer::import("DEV", "Profiler", "test::sdkTester");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");
importer::import("DEV", "Resources", "paths");

use \ESS\Protocol\BootLoader;
use \ESS\Environment\url;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Prototype\sourceMap;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Core\sdk\sdkObject;
use \DEV\Profiler\test\sdkTester;
use \DEV\Projects\project;
use \DEV\Version\vcs;
use \DEV\Resources\paths;

/**
 * Core SDK Package Manager
 * 
 * Handles all operations with Core SDK packages.
 * 
 * @version	0.1-3
 * @created	April 1, 2014, 14:16 (EEST)
 * @revised	November 6, 2014, 12:52 (EET)
 */
class sdkPackage
{
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private static $sourceMap;
	
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
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Update map file vcs item
		$this->updateMapFile();
		
		// Create package in map.xml
		$this->loadSourceMap();
		return self::$sourceMap->createPackage($library, $package);
	}
	
	/**
	 * Delete a package in the SDK.
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
			$status = self::$sourceMap->deletePackage($library, $package);
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
	 * Create a namespace in the given package.
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
		// Library name must be capitals
		$library = strtoupper($library);
		
		// Update map file vcs item
		$this->updateMapFile();
		
		// Create namespace in map.xml
		$this->loadSourceMap();
		return self::$sourceMap->createNamespace($library, $package, $namespace, $parentNs);
	}
	
	/**
	 * Delete a namespace in the core SDK.
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
			$status = self::$sourceMap->deleteNamespace($library, $package, $namespace);
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
	 * 		A nested array of namespaces.
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
		return self::$sourceMap->getNSList($library, $package, $parentNs);
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

		// Check tester status
		if (sdkTester::libPackageStatus($library, $package))
		{
			// Load from repositories
			$this->loadSourceMap();
			$pkgObjects = self::$sourceMap->getObjectList($library, $package);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($library, $package, $object["namespace"], $object["name"]);
				$sdkObject->loadSourceCode();
			}
		}
		else
		{
			// Load from exported
			$sourceMap = new sourceMap(systemRoot.sdkLibrary::RELEASE_PATH, "map.xml");
			$pkgObjects = $sourceMap->getObjectList($library, $package);
			foreach ($pkgObjects as $object)
			{
				$objectName = (empty($object['namespace']) ? "" : $object['namespace']."::").$object['name'];
				importer::incl(self::getReleaseObjectPath($library, $package, $objectName), TRUE, TRUE);
			}
		}
	}
	
	
	/**
	 * Initializes the source map object for getting the source information from the source index.
	 * 
	 * @return	object
	 * 		The sourceMap object.
	 */
	private function loadSourceMap()
	{
		if (empty(self::$sourceMap))
		{
			// Get map file trunk path
			$vcs = new vcs(1);
			$itemID = sdkLibrary::getMapfileID();
			$mapFilePath = $vcs->getItemTrunkPath($itemID);
			self::$sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
		}
		
		return self::$sourceMap;
	}
	
	/**
	 * Updates the source map index file in the vcs.
	 * 
	 * @return	void
	 */
	private function updateMapFile()
	{
		// Update map file
		$vcs = new vcs(1);
		$itemID = sdkLibrary::getMapfileID();
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
	 * 		The release system object path.
	 */
	private static function getReleaseObjectPath($library, $package, $object)
	{
		return systemSDK."/".$library."/".$package."/".str_replace("::", "/", $object).".php";
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
		$objects = self::$sourceMap->getObjectList($library, $package);

		// Import package objects
		$packageCSS = "";
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($library, $package, $object['namespace'], $object['name']);
			$packageCSS .= $sdkObj->getCSSCode()."\n";
		}
		
		// Replace resource vars
		$project = new project(1);
		$resourcePath = $project->getResourcesFolder()."/media/";
		$resourcePath = str_replace(paths::getRepositoryPath(), "", $resourcePath);
		$resourceUrl = url::resolve("repo", $resourcePath);
		$packageCSS = str_replace("%resources%", $resourceUrl, $packageCSS);
		
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
		$objects = self::$sourceMap->getObjectList($library, $package);

		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($library, $package, $object['namespace'], $object['name']);
			$sdkObj->loadJSCode();
			echo "\n";
		}
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
		return self::$sourceMap->getObjectList($library, $package, $namespace);
	}
}
//#section_end#
?>