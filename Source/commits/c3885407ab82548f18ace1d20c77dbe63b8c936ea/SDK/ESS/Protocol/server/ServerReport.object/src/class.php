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

importer::import("ESS", "Protocol", "reports::ServerReport");

use \ESS\Protocol\reports\ServerReport as newServerReport;

/**
 * Server Report Protocol
 * 
 * Creates an asynchronous server report according to user request.
 * It can be html or json.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 9:12 (EET)
 * @revised	July 29, 2014, 22:22 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\reports\ServerReport instead.
 */
abstract class ServerReport extends newServerReport {}
//#section_end#
?>