<?php
//#section#[header]
// Namespace
namespace ESS\Protocol;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "http/HTTPResponse");

use \ESS\Protocol\http\HTTPResponse as newHTTPResponse;

/**
 * HTTP Response Handler
 * 
 * Manages and adapts the http response according to redback rules.
 * 
 * @version	1.0-1
 * @created	July 29, 2014, 22:20 (EEST)
 * @revised	November 5, 2014, 15:30 (EET)
 * 
 * @deprecated	Use \ESS\Protocol\http\HTTPResponse instead.
 */
class HttpResponse extends newHTTPResponse {}
//#section_end#
?>