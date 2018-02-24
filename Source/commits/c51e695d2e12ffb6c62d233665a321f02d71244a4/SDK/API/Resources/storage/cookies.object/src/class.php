<?php
//#section#[header]
// Namespace
namespace API\Resources\storage;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\storage
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Cookies");

use \ESS\Protocol\client\environment\Cookies as ESSCookies;

/**
 * Cookie's Manager
 * 
 * Manages all cookie interaction for the system
 * 
 * @version	{empty}
 * @created	July 22, 2013, 10:49 (EEST)
 * @revised	October 23, 2013, 10:50 (EEST)
 */
class cookies extends ESSCookies {}
//#section_end#
?>