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

importer::import("DEV", "Profiler", "log::errorLogger");

use \DEV\Profiler\log\errorLogger as DEVErrorLogger;

/**
 * System Error Logger
 * 
 * Logs all errors and exceptions of the system.
 * 
 * @version	{empty}
 * @created	January 27, 2014, 14:44 (EET)
 * @revised	February 11, 2014, 11:09 (EET)
 * 
 * @deprecated	Use DEV\Profiler\log\errorLogger instead.
 */
class errorLogger extends DEVErrorLogger {}
//#section_end#
?>