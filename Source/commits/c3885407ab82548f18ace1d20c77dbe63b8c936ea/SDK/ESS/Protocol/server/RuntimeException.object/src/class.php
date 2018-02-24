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

importer::import("ESS", "Protocol", "debug::RuntimeException");

use \ESS\Protocol\debug\RuntimeException as NewException;

/**
 * Redback Runtime Exception
 * 
 * Creates a runtime exception and provides the user all the available information (according to debugger level) for reporting the error.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 9:19 (EET)
 * @revised	July 29, 2014, 22:12 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\debug\RuntimeException instead.
 */
class RuntimeException extends NewException {}
//#section_end#
?>