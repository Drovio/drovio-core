<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\debug;

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
 * @namespace	\debug
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Debugger class.
 * 
 * Contains only the prime javascript code.
 * 
 * @version	{empty}
 * @created	December 16, 2013, 10:11 (EET)
 * @revised	January 27, 2014, 13:46 (EET)
 * 
 * @deprecated	Use API\Developer\profiler\debugger instead.
 */
class debugger {}
//#section_end#
?>