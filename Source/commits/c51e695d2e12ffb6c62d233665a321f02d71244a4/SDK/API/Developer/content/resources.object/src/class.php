<?php
//#section#[header]
// Namespace
namespace API\Developer\content;

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
 * @namespace	\content
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Resource Manager
 * 
 * Developer's Resource Manager
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:47 (EEST)
 * @revised	October 7, 2013, 13:51 (EEST)
 * 
 * @deprecated	This class is no longer used.
 */
class resources
{
	/**
	 * The Developer's Resource Root
	 * 
	 * @type	string
	 */
	const PATH = "/.developer/Resources/";
}
//#section_end#
?>