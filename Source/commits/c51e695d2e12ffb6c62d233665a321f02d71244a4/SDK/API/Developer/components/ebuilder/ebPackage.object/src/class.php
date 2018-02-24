<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ebuilder;

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
 * @namespace	\components\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::packageObject");
importer::import("API", "Developer", "components::ebuilder::ebLibrary");
importer::import("API", "Developer", "components::ebuilder::ebObject");
importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \API\Developer\components\prime\packageObject;
use \API\Developer\components\ebuilder\ebLibrary;
use \API\Developer\components\ebuilder\ebObject;
use \API\Developer\profiler\tester;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;

importer::import("API", "Developer", "resources::paths");
use \API\Developer\resources\paths;

/**
 * Ebuilder Package Manager
 * 
 * Handles all operations with eBuilder packages.
 * 
 * @version	{empty}
 * @created	May 28, 2013, 15:03 (EEST)
 * @revised	July 23, 2013, 17:24 (EEST)
 */
class ebPackage extends packageObject
{
	/**
	 * Create a new package.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Set Repository
		$this->setRepository(paths::getDevPath()."/Repositories/", "/Library/devKit/eBuilder/");
		
		// Set Release Folder
		$this->setReleaseFolder("/System/Library/devKit/eBuilder/");
		
		// Set Domain
		$this->setDomain("eBuilder");
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
		if (self::getTesterStatus($libName, $packageName))
		{
			// Load from repositories
			$pkgObjects = $this->getPackageObjects($libName, $packageName);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($object["lib"], $object["pkg"], $object["ns"], $object["name"]);
				$sdkObject->loadSourceCode();
			}
		}
		else
		{
			// Load from exported
			$pkgObjects = $this->getReleasePackageObjects($libName, $packageName);
			foreach ($pkgObjects as $objectName)
				importer::incl($this->getReleaseObjectPath($libName, $packageName, $objectName), TRUE, TRUE);
		}
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
		$ebLib = new ebLibrary();
		$packages = $ebLib->getPackageList($libName);
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
		$objects = $this->getPackageObjects($libName, $packageName);
		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new ebObject($libName, $packageName, $object['ns'], $object['name']);
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
		$objects = $this->getPackageObjects($libName, $packageName);
		
		// Import package objects
		foreach ($objects as $object)
		{
			$sdkObj = new ebObject($libName, $packageName, $object['ns'], $object['name']);
			$sdkObj->loadJSCode();
			echo "\n";
		}
	}
	
	/**
	 * Activates or deactivates the tester status for the given eBuilder packages.
	 * 
	 * @param	array	$pkgList
	 * 		The array of packages as [libName][] {package1, package2, etc.}.
	 * 
	 * @return	void
	 */
	public static function setTesterPackages($pkgList)
	{
		$toActivate = array();
		foreach ($pkgList as $libName => $packages)
			foreach ($packages as $packageName)
				$toActivate[] = "ebld_".$libName."_".$packageName;
			
		return tester::setPackages($toActivate);
	}
	
	/**
	 * Get the current tester status for the given eBuilder package.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function getTesterStatus($libName, $packageName)
	{
		return tester::packageStatus("ebld_".$libName."_".$packageName);
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
	public function export($libName, $packageName)
	{
		// Get all package objects
		$packageObjects = $this->getPackageObjects($libName, $packageName);
		// Initialize outputs
		$cssContent = "";
		$jsContent = "";
		
		// Scan all objects
		foreach ($packageObjects as $object)
		{
			// Initialize sdkObject
			$ebObj = new ebObject($libName, $packageName, $object['ns'], $object['name']);
			
			// Export Source Code
			$ebObj->export();
			// Gather CSS Code
			$cssContent .= trim($ebObj->getHeadCSSCode())."\n";
			
			// Gather JS Code
			$jsContent .= trim($ebObj->getHeadJSCode())."\n";
		}
		// Export CSS Package
		//BootLoader::exportCSS("Packages", $libName, $packageName, $cssContent);
		
		// Export JS Package
		//BootLoader::exportJS("Packages", $libName, $packageName, $jsContent);
	}
	
	/**
	 * Pack an entire package in an archive, new or existing.
	 * 
	 * @param	string	$libName
	 * 		Name of the library
	 * 
	 * @param	string	$packageName
	 * 		Name of the package to pack.
	 * 
	 * @param	string	$archivePath
	 * 		Directory of the new or existing archive
	 * 
	 * @param	string	$archiveName
	 * 		Name of the archive to pack into. If empty, a new archive will be created in $destination.
	 * 
	 * @param	string	$innerArchiveDirectory
	 * 		Inner directory inside the existing archive. Ignored if no archive is provided.
	 * 
	 * @return	boolean
	 * 		Status of the process
	 */
	public function pack($libName, $packageName, $archivePath, $archiveName = "", $innerArchiveDirectory = "")
	{
		$packageRoot = $this->releaseFolder."/".$libName."/".$packageName;
		$contents = directory::getContentList(systemRoot.$packageRoot, TRUE);
		
		if (!empty($archiveName))
			return zipManager::append($archivePath."/".$archiveName, $contents, $innerArchiveDirectory);
		
		$archiveName = "/".$libName."_".$packageName.".zip";
		return zipManager::create($archivePath."/".$archiveName, $contents);
	}
}
//#section_end#
?>