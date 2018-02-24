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
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::classMap");
importer::import("API", "Developer", "projects::project");
importer::import("API", "Developer", "resources::paths");

use \API\Developer\components\prime\classMap;
use \API\Developer\projects\project;
use \API\Developer\resources\paths;

/**
 * SDK Library Manager
 * 
 * Handles all operations with sdk libraries.
 * 
 * @version	{empty}
 * @created	March 21, 2013, 12:05 (EET)
 * @revised	January 13, 2014, 16:51 (EET)
 */
class sdkLibrary
{
	/**
	 * The classMap object.
	 * 
	 * @type	classMap
	 */
	private $classMap;
	
	/**
	 * Constructor method. Initializes class variables.
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
	 * Creates a new library.
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($libName)
	{
		// Library name must be capitals
		$libName = strtoupper($libName);
		
		// Create index
		$this->classMap->createLibrary($libName);
		
		// If library already exists, abort
		if (!$proceed)
			return FALSE;
		
		// Create Library Index
		return TRUE;
	}
	
	/**
	 * Gets a list of all libraries.
	 * 
	 * @return	array
	 * 		A list of all libraries in the SDK.
	 */
	public function getList()
	{
		return $this->classMap->getLibraryList();
	}
	
	/**
	 * Get all packages in the given library
	 * 
	 * @param	string	$libName
	 * 		The library name.
	 * 
	 * @return	array
	 * 		An array of all packages.
	 */
	public function getPackageList($libName = "")
	{
		return $this->classMap->getPackageList($libName);
	}
}
//#section_end#
?>