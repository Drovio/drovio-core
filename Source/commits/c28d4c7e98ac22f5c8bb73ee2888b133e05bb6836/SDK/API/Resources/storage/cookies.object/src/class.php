<?php
//#section#[header]
// Namespace
namespace API\Resources\storage;

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
 * @package	Resources
 * @namespace	\storage
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");

use \ESS\Environment\cookies as ENVCookies;

/**
 * Cookie's Manager
 * 
 * Manages all cookie interaction for the system
 * 
 * @version	0.1-1
 * @created	July 22, 2013, 10:49 (EEST)
 * @revised	November 4, 2014, 10:07 (EET)
 * 
 * @deprecated	Use \ESS\Environment\cookies instead.
 */
class cookies extends ENVCookies {
	public static function delete($name)
	{		
		return parent::remove($name);
	}
}
//#section_end#
?>