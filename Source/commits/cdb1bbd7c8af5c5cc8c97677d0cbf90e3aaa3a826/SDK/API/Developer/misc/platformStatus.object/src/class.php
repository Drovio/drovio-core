<?php
//#section#[header]
// Namespace
namespace API\Developer\misc;

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
 * @namespace	\misc
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "status");

use \DEV\Profiler\status;

/**
 * Platform status generator
 * 
 * Sets and gets the platform status data.
 * 
 * @version	{empty}
 * @created	January 31, 2014, 11:55 (EET)
 * @revised	February 11, 2014, 12:12 (EET)
 * 
 * @deprecated	Use DEV\Profiler\status instead.
 */
class platformStatus extends status {}
//#section_end#
?>