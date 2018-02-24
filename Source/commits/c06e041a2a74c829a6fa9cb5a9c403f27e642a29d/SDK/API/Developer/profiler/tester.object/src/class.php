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

importer::import("DEV", "Profiler", "tester");

use \DEV\Profiler\tester as DEVTester;

/**
 * Tester Profile
 * 
 * Manages all the testing configuration.
 * 
 * @version	{empty}
 * @created	March 28, 2013, 13:45 (EET)
 * @revised	December 20, 2013, 13:08 (EET)
 */
/**
 * Tester Profile
 * 
 * Manages all the testing configuration.
 * 
 * @version	{empty}
 * @created	March 28, 2013, 13:45 (EET)
 * @revised	February 10, 2014, 11:16 (EET)
 * 
 * @deprecated	Use \DEV\Profiler\tester instead.
 */
abstract class tester extends DEVTester {}
//#section_end#
?>