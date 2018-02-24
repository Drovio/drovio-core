<?php
//#section#[header]
// Namespace
namespace API\Developer\appcenter\appComponents;

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
 * @namespace	\appcenter\appComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::indexing::packageIndex");
importer::import("API", "Developer", "components::prime::indexing::libraryIndex");

use \API\Developer\components\prime\indexing\packageIndex;
use \API\Developer\components\prime\indexing\libraryIndex;

/**
 * Application Source Package
 * 
 * Application Source Package Manager
 * 
 * @version	{empty}
 * @created	June 3, 2013, 16:12 (EEST)
 * @revised	April 6, 2014, 1:48 (EEST)
 * 
 * @deprecated	Use \DEV\Apps\components\source\sourcePackage instead.
 */
class appSrcPackage
{
	/**
	 * The application's library name.
	 * 
	 * @type	string
	 */
	const LIB_NAME = "Source";
	/**
	 * The developer's application source root path.
	 * 
	 * @type	string
	 */
	private $devPath;
	
	/**
	 * Initializes the application's source root path.
	 * 
	 * @param	string	$devPath
	 * 		The developer's path.
	 * 
	 * @return	void
	 */
	public function __construct($devPath)
	{
		// Set Developer's Application Path
		$this->devPath = $devPath;
	}
	
	/**
	 * Creates a new package in the application's index.
	 * 
	 * @param	string	$packageName
	 * 		The name of the package.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($packageName)
	{
		// Create library if doesn't exist
		libraryIndex::createLibraryIndex($this->devPath."/.app/", self::LIB_NAME);
		
		// Create Map Index 
		$result = packageIndex::createMapIndex($this->devPath."/.app/", self::LIB_NAME, $packageName);
		
		return $result;
	}
	
	/**
	 * Creates a namespace in the application's index.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$nsName
	 * 		The namespace name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function createNS($packageName, $nsName, $parentNs = "")
	{
		// Create Index 
		$result = packageIndex::createNSIndex($this->devPath."/.app/", self::LIB_NAME, $packageName, $nsName, $parentNs);
			
		return $result;
	}
	
	/**
	 * Gets all packages of the application's source.
	 * 
	 * @param	boolean	$fullNames
	 * 		Packages in fullname.
	 * 
	 * @return	array
	 * 		An array of all packages.
	 */
	public function getPackages($fullNames = TRUE)
	{
		return libraryIndex::getPackageList($this->devPath."/.app/", self::LIB_NAME, $fullNames);
	}
	
	/**
	 * Get a list of namespaces in the application source.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The parent namespace (separated by "::").
	 * 
	 * @return	array
	 * 		An array of namespaces by key and value.
	 */
	public function getNSList($packageName, $parentNs = "")
	{
		return packageIndex::getNSList($this->devPath."/.app/", self::LIB_NAME, $packageName, $parentNs);
	}
	
	/**
	 * Get all the objects in the given namespace.
	 * 
	 * @param	string	$packageName
	 * 		The package name.
	 * 
	 * @param	string	$parentNs
	 * 		The namespace (separated by "::").
	 * 
	 * @return	array
	 * 		An array of objects.
	 */
	public function getObjects($packageName, $parentNs = "")
	{
		return packageIndex::getNSObjects($this->devPath."/.app/", self::LIB_NAME, $packageName, $parentNs);
	}
}
//#section_end#
?>