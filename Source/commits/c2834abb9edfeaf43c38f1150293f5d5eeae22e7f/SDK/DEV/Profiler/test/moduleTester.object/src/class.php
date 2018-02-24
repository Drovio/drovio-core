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

importer::import("DEV", "Modules", "test::moduleTester");

use \DEV\Modules\test\moduleTester as newModuleTester;

/**
 * Module Tester Manager
 * 
 * Manages module tester mode.
 * 
 * @version	0.1-1
 * @created	February 10, 2014, 11:37 (EET)
 * @revised	September 17, 2014, 11:01 (EEST)
 * 
 * @deprecated	Use \DEV\Modules\test\moduleTester instead.
 */
class moduleTester extends newModuleTester {}
//#section_end#
?>