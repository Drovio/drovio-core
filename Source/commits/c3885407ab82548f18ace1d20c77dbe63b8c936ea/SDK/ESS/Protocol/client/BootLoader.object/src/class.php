<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

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
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");

use \ESS\Protocol\BootLoader as newBootLoader;

/**
 * Resource Boot Loader
 * 
 * The manager class for loading and general handling the system's resources (javascript and css).
 * 
 * @version	1.0-1
 * @created	March 27, 2013, 11:53 (EET)
 * @revised	July 29, 2014, 21:54 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\BootLoader instead.
 */
class BootLoader extends newBootLoader {}
//#section_end#
?>