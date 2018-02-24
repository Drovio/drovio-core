<?php
//#section#[header]
// Namespace
namespace API\Developer\components\sdk;

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
 * @namespace	\components\sdk
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::indexing::libraryIndex");
importer::import("API", "Developer", "resources::paths");

use \API\Developer\components\prime\indexing\libraryIndex;
use \API\Developer\resources\paths;

/**
 * SDK Library Manager
 * 
 * Handles all operations with sdk libraries.
 * 
 * @version	{empty}
 * @created	March 21, 2013, 12:05 (EET)
 * @revised	November 27, 2013, 9:47 (EET)
 */
class sdkLibrary
{
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
		
		// Create Developer Index 
		$proceed = libraryIndex::createMapIndex(paths::getDevRsrcPath()."/Mapping/Library/SDK/", "SDK", $libName);
		
		// If library already exists, abort
		if (!$proceed)
			return FALSE;
		
		// Create Library Index
		return libraryIndex::createReleaseIndex("/System/Library/SDK/", $libName);
	}
	
	/**
	 * Returns a list of all libraries in the given domain.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getList() instead.
	 */
	public function getList()
	{
		return libraryIndex::getList(paths::getDevRsrcPath()."/Mapping/Library/SDK");
	}
	
	/**
	 * Returns a list of all objects in the developer's library.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getLibraryObjects() instead.
	 */
	public static function getLibraryObjects($libName)
	{
		return libraryIndex::getLibraryObjects(paths::getDevRsrcPath()."/Mapping/Library/SDK/", $libName);
	}
	
	/**
	 * Returns a list of all released objects in the library.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getReleaseLibraryObjects() instead.
	 */
	public static function getReleaseLibraryObjects($libName)
	{
		return libraryIndex::getReleaseLibraryObjects("/System/Library/SDK/", $libName);
	}
	
	/**
	 * Returns a list of all the packages in the given library.
	 * 
	 * @param	string	$libName
	 * 		The library name. If not given, gets for all libraries.
	 * 
	 * @param	boolean	$fullNames
	 * 		Indicates full names or nested arrays.
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Developer\components\prime\indexing\libraryIndex::getPackageList() instead.
	 */
	public static function getPackageList($libName = "", $fullNames = TRUE)
	{
		return libraryIndex::getPackageList(paths::getDevRsrcPath()."/Mapping/Library/SDK", $libName, $fullNames);
	}
}
//#section_end#
?>