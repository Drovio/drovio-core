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

importer::import("ESS", "Protocol", "reports::HTMLServerReport");

use \ESS\Protocol\reports\HTMLServerReport as newHTMLServerReport;

/**
 * HTML Server Report
 * 
 * Creates an asynchronous server report in HTML format according to user request.
 * 
 * @version	0.1-1
 * @created	March 8, 2013, 13:23 (EET)
 * @revised	July 29, 2014, 22:33 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\reports\HTMLServerReport instead.
 */
class HTMLServerReport extends newHTMLServerReport {}
//#section_end#
?>