<?php
//#section#[header]
// Namespace
namespace ESS\Environment;

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
 * @package	Environment
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Drovio Local Storage manager
 * 
 * Manages browsers local storage.
 * It has only javascript interface.
 * 
 * @version	0.1-2
 * @created	November 20, 2015, 17:04 (GMT)
 * @updated	November 20, 2015, 17:08 (GMT)
 */
class dlocalStorage {}
//#section_end#
?>