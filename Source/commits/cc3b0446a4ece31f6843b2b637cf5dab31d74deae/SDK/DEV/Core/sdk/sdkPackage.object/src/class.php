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

importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "Prototype", "sourceMap");
importer::import("DEV", "Core", "sdk::sdkLibrary");
importer::import("DEV", "Core", "sdk::sdkObject");
importer::import("DEV", "Profiler", "test::sdkTester");
importer::import("DEV", "Projects", "project");
importer::import("DEV", "Version", "vcs");

use \ESS\Protocol\client\BootLoader;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Prototype\sourceMap;
use \DEV\Core\sdk\sdkLibrary;
use \DEV\Core\sdk\sdkObject;
use \DEV\Profiler\test\sdkTester;
use \DEV\Projects\project;
use \DEV\Version\vcs;

/**
 * Core SDK Package Manager
 * 
 * Handles all operations with Core SDK packages.
 * 
 * @version	{empty}
 * @created	April 1, 2014, 14:16 (EEST)
 * @revised	April 1, 2014, 14:16 (EEST)
 */
class sdkPackage
{
	/**
	 * The sourceMap object.
	 * 
	 * @type	sourceMap
	 */
	private $sourceMap;
	
	/**
	 * Constructor method. Initializes the object variables.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Init project
		$project = new project(1);
		$repository = $project->getRepository();
		
		// Get map file trunk path
		$this->vcs = new vcs($repository);
		$itemID = sdkLibrary::getMapfileID();
		$mapFilePath = $this->vcs->getItemTrunkPath($itemID);
		$this->sourceMap = new sourceMap(dirname($mapFilePath), basename($mapFilePath));
	}
	
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
		return $this->sourceMap->createPackage($library, $package);
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
		return $this->sourceMap->createNamespace($library, $package, $namespace, $parentNs);
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
		// If packageName not given (compatibility reasons), load library (to be avoided)
		if (empty($package))
		{
			// Get All Packages and load them
			$packages = $this->sourceMap->getPackageList($library);
			foreach($packages as $package)
				$this->load($library, $package);
		}

		// Check tester status
		if (sdkTester::libPackageStatus($library, $package))
		{
			// Load from repositories
			$pkgObjects = $this->classMap->getObjectList($library, $package);
			foreach ($pkgObjects as $object)
			{
				$sdkObject = new sdkObject($library, $package, $object["namespace"], $object["name"]);
				$sdkObject->loadSourceCode();
			}
		}
		else
		{
			// Load from exported
			$sourceMap = new sourceMap(sdkLibrary::RELEASE_PATH, "map.xml");
			$pkgObjects = $sourceMap->getObjectList($library, $package);
			foreach ($pkgObjects as $objectName)
				importer::incl(self::getReleaseObjectPath($library, $package, $objectName), TRUE, TRUE);
		}
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
		$objects = $this->sourceMap->getObjectList($library, $package);

		// Import package objects
		$packageCSS = "";
		foreach ($objects as $object)
		{
			$sdkObj = new sdkObject($library, $package, $object['namespace'], $object['name']);
			$packageCSS .= $sdkObj->getCSSCode()."\n";
		}
		
		// Replace resource vars
		$project = new project(1);
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
		$objects = $this->sourceMap->getObjectList($library, $package);

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
		if (!is_null($namespace))
			$namespace = str_replace("_", "::", $namespace);
		return $this->sourceMap->getObjectList($library, $package, $namespace);
	}
}
//#section_end#
?>