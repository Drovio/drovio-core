<?php
//#section#[header]
// Namespace
namespace API\Developer\content\resources;

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
 * @namespace	\content\resources
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * SDK Developer Mapping
 * 
 * Handles all developer's mapping of the SDK objects.
 * 
 * @version	{empty}
 * @created	March 22, 2013, 11:23 (EET)
 * @revised	March 22, 2013, 11:23 (EET)
 * 
 * @deprecated	This class is no longer used. Operations have been merged with sdkLibrary, sdkPackage and sdkObject classes.
 */
class mapping
{
	/**
	 * Create an SDK Library Index file
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @return	boolean
	 * 
	 * @deprecated	Merged with sdkLibrary::create() function.
	 */
	public static function create_library($libName)
	{
		return NULL;
	}
	
	/**
	 * Create a package index into the given library
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @return	boolean
	 * 
	 * @deprecated	Merged with sdkPackage-&gt;create() function.
	 */
	public static function create_package($libName, $packageName)
	{
		return NULL;
	}
	
	/**
	 * Create a package namespace
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @param	string	$nsName
	 * 		The namespace name
	 * 
	 * @param	string	$parentNs
	 * 		The parent's namespace. Multiple namespaces can be separated with "\" or "::".
	 * 
	 * @return	boolean
	 * 
	 * @deprecated	Merged with sdkPackage-&gt;createNS() function.
	 */
	public static function create_namespace($libName, $packageName, $nsName, $parentNs = "")
	{
		return NULL;
	}
	
	/**
	 * Create a package object index
	 * 
	 * @param	string	$libName
	 * 		The library name
	 * 
	 * @param	string	$packageName
	 * 		The package name
	 * 
	 * @param	string	$nsName
	 * 		The namespace. Namespaces separated by "::".
	 * 
	 * @param	string	$objectName
	 * 		The object's name
	 * 
	 * @param	string	$objectTitle
	 * 		The object's title
	 * 
	 * @return	void
	 * 
	 * @deprecated	Merged with sdkObject-&gt;create() function.
	 */
	public static function create_object($libName, $packageName, $nsName, $objectName, $objectTitle)
	{
		return NULL;
	}
}
//#section_end#
?>