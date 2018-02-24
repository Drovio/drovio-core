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

importer::import("DEV", "Profiler", "logger");

use \DEV\Profiler\logger as DEVLogger;

/**
 * System Logger
 * 
 * Logs all messages for any priority and category.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:49 (EEST)
 * @revised	February 10, 2014, 10:53 (EET)
 * 
 * @deprecated	Use \DEV\Profiler\logger instead.
 */
class logger extends DEVLogger {}
//#section_end#
?>