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

importer::import("ESS", "Protocol", "reports::MIMEServerReport");

use \ESS\Protocol\reports\MIMEServerReport as newMIMEServerReport;

/**
 * Multipurpose Internet Mail Extensions Server Report
 * 
 * Returns an http response and performs a download of a server file.
 * 
 * @version	0.1-1
 * @created	April 16, 2013, 14:51 (EEST)
 * @revised	July 29, 2014, 22:46 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\reports\MIMEServerReport instead.
 */
class MIMEServerReport extends newMIMEServerReport {}
//#section_end#
?>