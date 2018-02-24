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

importer::import("ESS", "Protocol", "AsCoProtocol");

use \ESS\Protocol\AsCoProtocol as newAscop;

/**
 * Asynchronous Communication Protocol
 * 
 * Responsible class (in Javascript) for handling all communication protocols with the server.
 * 
 * @version	1.0-1
 * @created	March 7, 2013, 9:18 (EET)
 * @revised	July 29, 2014, 20:56 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\AsCoProtocol instead.
 */
class AsCoProtocol extends newAscop {}
//#section_end#
?>