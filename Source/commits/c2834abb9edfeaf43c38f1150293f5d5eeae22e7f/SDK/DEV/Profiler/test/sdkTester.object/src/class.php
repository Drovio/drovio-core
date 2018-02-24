<?php
//#section#[header]
// Namespace
namespace DEV\Profiler\test;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profiler
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Core", "test::sdkTester");

use \DEV\Core\test\sdkTester as newSdkTester;

/**
 * SDK Package Tester Manager
 * 
 * Manages sdk packages tester mode.
 * 
 * @version	0.1-1
 * @created	February 10, 2014, 11:45 (EET)
 * @revised	September 17, 2014, 10:55 (EEST)
 * 
 * @deprecated	Use \DEV\Core\test\sdkTester instead.
 */
class sdkTester extends newSdkTester {}
//#section_end#
?>