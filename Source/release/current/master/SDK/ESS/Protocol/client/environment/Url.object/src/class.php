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

importer::import("ESS", "Protocol", "environment::Url");

use \ESS\Protocol\environment\Url as newURL;

/**
 * URL Resolver
 * 
 * Resolves urls according to static and subdomain
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 11:39 (EET)
 * @revised	July 29, 2014, 19:42 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\environment\Url instead.
 */
class Url extends newURL {}
//#section_end#
?>