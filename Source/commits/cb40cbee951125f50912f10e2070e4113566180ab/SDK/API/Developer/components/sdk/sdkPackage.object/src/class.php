<?php
//#section#[header]
// Namespace
namespace API\Developer\components\sdk;

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
 * @package	Developer
 * @namespace	\components\sdk
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "components::prime::classMap");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "components::sdk::sdkObject");
importer::import("API", "Developer", "profiler::sdkTester");
importer::import("API", "Developer", "resources::paths");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\components\prime\classMap;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\projects\project;
use \API\Developer\components\sdk\sdkObject;
use \API\Developer\profiler\sdkTester;
use \API\Developer\resources\paths;

/**
 * SDK Package Manager
 * 
 * Handles all operations with SDK packages.
 * 
 * @version	{empty}
 * @created	March 21, 2013, 12:52 (EET)
 * @revised	January 20, 2014, 14:24 (EET)
 */
class sdkPackage
{
	/**
	 * The classMap Object.
	 * 
	 * @type	classMap
	 */
	private $classMap;
	
	/**
	 * Constructor method. Initializes the object variables.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Create Developer Index
		$repository = project::getRepository(1);
		$this->classMap = new classMap($repository, FALSE, "SDK");
	}
	
	/**
	 * Create a new package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($libName, $packageName)
	{
		return $this->classMap->createPackage($libName, $packageName);
	}
	
	/**
	 * Create a namespace in the given package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (if any, separated by "::").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function createNS($libName, $packageName, $nsName, $parentNs = "")
	{
		return $this->classMap->createNamespace($libName, $packageName, $nsName, $parentNs);
	}
	
	/**
	 * Gets all namespaces in a given package, with an optional parent namespace.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		A nested array of namespaces.
	 */
	public function getNSList($libName, $packageName, $parentNs = "")
	{
		return $this->classMap->getNSList($libName, $packageName, $parentNs);
	}
	
	/**
	 * Loads all objects in the given package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public function load($libName, $packageName = "")
	{
		// If packageName not given (compatibility reasons), load library (to be avoided)
		if (empty($packageName))
			$this->loadLibrary($libName);

		// Check tester status
		if (sdkTester::libPackageStatus($libName, $packageName))
		{
			// Load from repositories
			$pkgObjects = $this->classMap->getObjectList($libName, $packageName);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($object["library"], $object["package"], $object["namespace"], $object["name"]);
				$sdkObject->loadSourceCode();
			}
		}
		else
		{
			// Load from exported
			$pkgObjects = packageIndex::getReleasePackageObjects("/System/Library/SDK/", $libName, $packageName);
			foreach ($pkgObjects as $objectName)
				importer::incl(self::getReleaseObjectPath($libName, $packageName, $objectName), TRUE, TRUE);
		}
	}
	
	/**
	 * Gets the release object path.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$objectName
	 * 		The object name (including namespace separated by "::").
	 * 
	 * @return	string
	 * 		The object path.
	 */
	private static function getReleaseObjectPath($libName, $packageName, $objectName)
	{
		return systemSDK."/".$libName."/".$packageName."/".str_replace("::", "/", $objectName).".php";
	}
	
	/**
	 * Load all packages of a given library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	void
	 */
	private function loadLibrary($libName)
	{
		// Get All Packages
		$packages = $this->classMap->getPackageList($libName);

		// Load Each Package
		foreach($packages as $packageName)
			$this->load($libName, $packageName);
	}
	
	/**
	 * Loads a style package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public function loadCSS($libName, $packageName)
	{
		// Get package objects
		$objects = $this->classMap->getObjectList($libName, $packageName);

		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($libName, $packageName, $object['namespace'], $object['name']);
			$sdkObj->loadCSSCode();
			echo "\n";
		}
	}
	
	/**
	 * Loads a javascript package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 */
	public function loadJS($libName, $packageName)
	{
		// Get package objects
		$objects = $this->classMap->getObjectList($libName, $packageName);

		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($libName, $packageName, $object['namespace'], $object['name']);
			$sdkObj->loadJSCode();
			echo "\n";
		}
	}
	
	/**
	 * Exports an entire package (including source code, css and javascript) to latest.
	 * Performs an inner release.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Only project manager can deploy.
	 */
	public function export($libName, $packageName)
	{/*
		// Get all package objects
		$packageObjects = $this->classMap->getObjectList($libName, $packageName);

		// Initialize outputs
		$cssContent = "";
		$jsContent = "";
		
		// Scan all objects
		foreach ($packageObjects as $object)
		{
			// Initialize sdkObject
			$sdkObj = new sdkObject($libName, $packageName, $object['namespace'], $object['name']);
			
			// Export Source Code
			$sdkObj->export();

			// Gather CSS Code
			$cssContent .= trim($sdkObj->getHeadCSSCode())."\n";
			
			// Gather JS Code
			$jsContent .= trim($sdkObj->getHeadJSCode())."\n";
		}

		// Export CSS Package
		$cssContent = cssParser::format($cssContent);
		BootLoader::exportCSS("Packages", $libName, $packageName, $cssContent);
		
		// Export JS Package
		$jsContent = jsParser::format($jsContent);
		BootLoader::exportJS("Packages", $libName, $packageName, $jsContent);
		
		*/
	}
	
	/**
	 * Get all objects in a package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::", if any).
	 * 
	 * @return	array
	 * 		An array of the object information including title, name, package, library and namespace.
	 */
	public function getPackageObjects($libName, $packageName, $parentNs = NULL)
	{
		if (!is_null($parentNs))
			$parentNs = str_replace("_", "::", $parentNs);
		return $this->classMap->getObjectList($libName, $packageName, $parentNs);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getPackageObjects() instead.
	 */
	public function getNSObjects($libName, $packageName, $parentNs = "")
	{
		$parentNs = str_replace("_", "::", $parentNs);
		return $this->classMap->getObjectList($libName, $packageName, $parentNs);
	}
	
	/**
	 * Get all objects from the release index map.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	array
	 * 		An array of object information including title, name, package, library and namespace.
	 */
	public static function getReleasePackageObjects($libName, $packageName)
	{
		return packageIndex::getReleasePackageObjects("/System/Library/SDK/", $libName, $packageName);
	}
}
//#section_end#
?>