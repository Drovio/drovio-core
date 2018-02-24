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

importer::import("DEV", "Profiler", "test::sdkTester");

use \DEV\Profiler\test\sdkTester as DEVSDKTester;

/**
 * SDK Query Tester Manager
 * 
 * Manages sdk class tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 17:34 (EET)
 * @revised	February 10, 2014, 11:45 (EET)
 * 
 * @deprecated	Use DEV\Profiler\test\sdkTester instead.
 */
class sdkTester extends DEVSDKTester {}
//#section_end#
?>