<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client\environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client\environment
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "environment::Cookies");

use \ESS\Protocol\environment\Cookies as newCookies;

/**
 * Cookie's Manager
 * 
 * Manages all cookie interaction for the system
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 11:41 (EET)
 * @revised	July 29, 2014, 20:28 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\environment\Cookies instead.
 */
class cookies extends newCookies {}
//#section_end#
?>