<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

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
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "HttpResponse");

use \ESS\Protocol\HttpResponse as newHttpResponse;

/**
 * HTTP Response Handler
 * 
 * Manages and adapts the http response according to redback rules.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 9:21 (EET)
 * @revised	July 29, 2014, 22:16 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\HttpResponse instead.
 */
class HttpResponse extends newHttpResponse {}
//#section_end#
?>