<?php
//#section#[header]
// Namespace
namespace API\Developer\profiler;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\profiler
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "debugger");

use \DEV\Profiler\debugger as DEVDebugger;

/**
 * System Debugger
 * 
 * Reports and handles all system errors
 * 
 * @version	{empty}
 * @created	March 29, 2013, 11:02 (EET)
 * @revised	February 10, 2014, 10:45 (EET)
 * 
 * @deprecated	Use \DEV\Profiler\debugger instead.
 */
class debugger extends DEVDebugger {}
//#section_end#
?>