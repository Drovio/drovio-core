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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Webengine resource manager
 * 
 * Manages resource names for the web engine core.
 * 
 * @version	0.1-1
 * @created	December 29, 2014, 18:42 (EET)
 * @revised	December 29, 2014, 18:42 (EET)
 */
class resourceLoader
{
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
}
//#section_end#
?>