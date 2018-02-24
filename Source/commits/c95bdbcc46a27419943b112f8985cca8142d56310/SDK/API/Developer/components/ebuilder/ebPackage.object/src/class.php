<?php
//#section#[header]
// Namespace
namespace API\Developer\components\ebuilder;

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
 * @namespace	\components\ebuilder
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
importer::import("API", "Resources", "filesystem::directory");
importer::import("API", "Resources", "archive::zipManager");

use \ESS\Protocol\client\BootLoader;
use \API\Developer\content\document\parsers\cssParser;
use \API\Developer\content\document\parsers\jsParser;
use \API\Developer\components\prime\classMap;
use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\projects\project;
use \API\Developer\components\sdk\sdkObject;
use \API\Developer\profiler\sdkTester;
use \API\Developer\resources\paths;
use \API\Resources\filesystem\directory;
use \API\Resources\archive\zipManager;

/**
 * Ebuilder Package Manager
 * 
 * Handles all operations with eBuilder packages.
 * 
 * @version	{empty}
 * @created	May 28, 2013, 15:03 (EEST)
 * @revised	April 4, 2014, 11:35 (EEST)
 * 
 * @deprecated	Use \DEV\WebEngine\sdk\webPackage instead.
 */
class ebPackage
{
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private $classMap;
	
	/**
	 * Create a new package.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Create Developer Index
		$repository = project::getRepository(3);
		$this->classMap = new classMap($repository, FALSE, "");
	}
	
	/**
	 * Activates or deactivates the tester status for the given eBuilder packages.
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function create($libName, $packageName)
	{
		return $this->classMap->createPackage($libName, $packageName);
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
	 * @param	{type}	$nsName
	 * 		{description}
	 * 
	 * @param	{type}	$parentNs
	 * 		{description}
	 * 
	 * @return	void
	 */
	public static function createNS($libName, $packageName, $nsName, $parentNs = "")
	{
		return $this->classMap->createNamespace($libName, $packageName, $nsName, $parentNs);
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
		if (webTester::libPackageStatus($libName, $packageName))
		{
			// Load from repositories
			$pkgObjects = $this->classMap->getObjectList($libName, $packageName);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new ebObject($object["library"], $object["package"], $object["namespace"], $object["name"]);
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
	 * {description}
	 * 
	 * @param	{type}	$libName
	 * 		{description}
	 * 
	 * @param	{type}	$packageName
	 * 		{description}
	 * 
	 * @param	{type}	$objectName
	 * 		{description}
	 * 
	 * @return	void
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
			$sdkObj = new ebObject($libName, $packageName, $object['namespace'], $object['name']);
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
			$sdkObj = new ebObject($libName, $packageName, $object['namespace'], $object['name']);
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
	public function export($libName, $packageName)
	{
		// Get all package objects
		$packageObjects = $this->classMap->getObjectList($libName, $packageName);
		
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
	 */
	public function getNSObjects($libName, $packageName, $parentNs = "")
	{
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
	 * @return	void
	 */
	public static function getReleasePackageObjects($libName, $packageName)
	{
		return packageIndex::getReleasePackageObjects("/System/Library/devKit/WebEngine/", $libName, $packageName);
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