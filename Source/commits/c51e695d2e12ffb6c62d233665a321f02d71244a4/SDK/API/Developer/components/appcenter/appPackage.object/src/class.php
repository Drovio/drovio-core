<?php
//#section#[header]
// Namespace
namespace API\Developer\components\appcenter;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components\appcenter
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "content::resources");
importer::import("API", "Developer", "content::document::parsers::cssParser");
importer::import("API", "Developer", "content::document::parsers::jsParser");
importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "resources::paths");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\resources;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\resources\paths;

/**
 * Application Center Package Manager
 * 
 * Manages all application center's library packages.
 * 
 * @version	{empty}
 * @created	May 30, 2013, 14:13 (EEST)
 * @revised	November 3, 2013, 13:23 (EET)
 */
class appPackage
{
	/**
	 * Creates a package index inside the application center library SDK.
	 * 
	 * @param	string	$libName
	 * 		The library name to create the package.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		TRUE on success, FALSE on failure.
	 */
	public function create($libName, $packageName)
	{
		return packageIndex::createMapIndex(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", $libName, $packageName);
	}
	
	/**
	 * Gets all the namespaces in the given package.
	 * 
	 * @param	string	$libName
	 * 		The given library.
	 * 
	 * @param	string	$packageName
	 * 		The given package.
	 * 
	 * @param	string	$parentNS
	 * 		The parent namespace (if any).
	 * 
	 * @return	array
	 * 		An array of all namespaces.
	 */
	public function getNSList($libName, $packageName, $parentNS = "")
	{
		return packageIndex::getNSList(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", $libName, $packageName, $parentNs);
	}
	
	/**
	 * Gets all the objects in the given namespace.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The namespace (if any).
	 * 
	 * @return	array
	 * 		An array of objects.
	 */
	public function getNSObjects($libName, $packageName, $parentNs = "")
	{
		return packageIndex::getNSObjects(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", $libName, $packageName, $parentNs);
	}
	
	/**
	 * Exports the given package to release location.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name to export.
	 * 
	 * @return	void
	 */
	public static function export($libName, $packageName)
	{	
		// Create Documentation index and library entry		
		packageIndex::createReleaseIndex(systemRoot."/System/Resources/Documentation/devKit/appCenter/", $libName, $packageName);
		packageIndex::addLibraryEntry("/System/Resources/Documentation/devKit/appCenter/", $libName, $packageName);
		
		// Get all package objects
		$packageObjects = packageIndex::getPackageObjects(paths::getDevRsrcPath()."/Mapping/Library/appCenter/", $libName, $packageName);

		// Initialize outputs
		$cssContent = "";
		$jsContent = "";
		
		// Scan all objects
		foreach ($packageObjects as $object)
		{
			// Initialize sdkObject
			$sdkObj = new appObject($libName, $packageName, $object['ns'], $object['name']);
			
			// Export Object (Source + Documentation + Model)
			$sdkObj->export();

			// Gather CSS Code
			$cssContent .= trim($sdkObj->getCSSCode())."\n";
			
			// Gather JS Code
			$jsContent .= trim($sdkObj->getJSCode())."\n";
		}

		// Export CSS Package
		$cssContent = cssParser::format($cssContent);
		BootLoader::exportCSS("appCenter", $libName, $packageName, $cssContent);
		
		// Export JS Package
		$jsContent = jsParser::format($jsContent);
		BootLoader::exportJS("appCenter", $libName, $packageName, $jsContent);
	}
}
//#section_end#
?>