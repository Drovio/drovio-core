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

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\sdk
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "components::sdk::sdkObject");
importer::import("API", "Developer", "profiler::sdkTester");
importer::import("API", "Developer", "resources::paths");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\components\prime\indexing\libraryIndex;
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
 * @revised	December 20, 2013, 19:16 (EET)
 */
class sdkPackage
{
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
		// Create Map Index 
		$proceed = packageIndex::createMapIndex(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName);
		
		// If package already exists, abort
		if (!$proceed)
			return FALSE;
		
		// Create Package index and library entry
		packageIndex::createReleaseIndex(systemRoot."/System/Library/SDK/", $libName, $packageName);
		packageIndex::addLibraryEntry("/System/Library/SDK/", $libName, $packageName);
		
		return TRUE;
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
		// Create Index 
		return packageIndex::createNSIndex(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName, $nsName, $parentNs);
	}
	
	/**
	 * Activates or deactivates the tester status for the given SDK packages.
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
	 * @return	boolean
	 * 		{description}
	 */
	public static function getNSList($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getNSList(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName, $parentNs);
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
	public static function load($libName, $packageName = "")
	{
		// If packageName not given (compatibility reasons), load library (to be avoided)
		if (empty($packageName))
			self::loadLibrary($libName);

		// Check tester status
		if (sdkTester::libPackageStatus($libName, $packageName))
		{
			// Load from repositories
			$pkgObjects = packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($object["lib"], $object["pkg"], $object["ns"], $object["name"]);
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
	private static function loadLibrary($libName)
	{
		// Get All Packages
		$packages = libraryIndex::getPackageList(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName);

		// Load Each Package
		foreach($packages as $packageName)
			self::load($libName, $packageName);
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
	public static function loadCSS($libName, $packageName)
	{
		// Get package objects
		$objects = packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName);

		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($libName, $packageName, $object['ns'], $object['name']);
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
	public static function loadJS($libName, $packageName)
	{
		// Get package objects
		$objects = packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName);
		
		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($libName, $packageName, $object['ns'], $object['name']);
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
	 */
	public static function export($libName, $packageName)
	{	
		// Create Documentation index and library entry		
		packageIndex::createReleaseIndex(systemRoot."/System/Resources/Documentation/SDK/", $libName, $packageName);
		packageIndex::addLibraryEntry("/System/Resources/Documentation/SDK/", $libName, $packageName);
		
		// Get all package objects
		$packageObjects = packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName);

		// Initialize outputs
		$cssContent = "";
		$jsContent = "";
		
		// Scan all objects
		foreach ($packageObjects as $object)
		{
			// Initialize sdkObject
			$sdkObj = new sdkObject($libName, $packageName, $object['ns'], $object['name']);
			
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
	 */
	public static function getPackageObjects($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName, $parentNs);
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
	 */
	public static function getNSObjects($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getNSObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName, $packageName, $parentNs);
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
	 * @return	void
	 */
	public static function getReleasePackageObjects($libName, $packageName)
	{
		return packageIndex::getReleasePackageObjects("/System/Library/SDK/", $libName, $packageName);
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
	 * @param	{type}	$namespace
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function addObjectReleaseEntry($libName, $packageName, $namespace, $objectName)
	{
		return packageIndex::addObjectReleaseEntry("/System/Library/SDK/", $libName, $packageName, $namespace, $objectName);
	}
}
//#section_end#
?>