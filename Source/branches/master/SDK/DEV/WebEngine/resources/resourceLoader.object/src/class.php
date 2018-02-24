<?php
//#section#[header]
// Namespace
namespace DEV\WebEngine\resources;

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
 * @package	WebEngine
 * @namespace	\resources
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

/**
 * Webengine resource manager
 * 
 * Manages resource names for the web engine core.
 * 
 * @version	1.0-1
 * @created	December 29, 2014, 18:42 (EET)
 * @updated	May 29, 2015, 12:52 (EEST)
 */
class resourceLoader
{
	/**
	 * The css resource type.
	 * 
	 * @type	string
	 */
	const RSRC_CSS = "css";
	
	/**
	 * The js resource type.
	 * 
	 * @type	string
	 */
	const RSRC_JS = "js";
	
	/**
	 * Get a resource id given a library and a package.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @return	string
	 * 		The resource id string.
	 */
	public static function getResourceID($library, $package)
	{
		return hash("md5", "rsrcID_".$library."_".$package);
	}
	
	/**
	 * Returns the hashed file name of the resource package.
	 * 
	 * @param	string	$prefix
	 * 		The file prefix.
	 * 
	 * @param	string	$library
	 * 		The resource library.
	 * 
	 * @param	string	$package
	 * 		The resource package.
	 * 
	 * @param	string	$type
	 * 		The resource type.
	 * 		See class constants.
	 * 
	 * @return	string
	 * 		The resource file name.
	 */
	public static function getFileName($prefix, $library, $package, $type)
	{
		return $prefix.hash("md5", "rsrc_".$library."_".$package."_".$type);
	}
}
//#section_end#
?>