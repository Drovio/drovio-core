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

importer::import("DEV", "Core", "test::ajaxTester");

use \DEV\Core\test\ajaxTester as newAjaxTester;

/**
 * Ajax File Tester Manager
 * 
 * Manages ajax file tester mode.
 * 
 * @version	1.0-1
 * @created	February 10, 2014, 11:49 (EET)
 * @revised	September 17, 2014, 10:50 (EEST)
 * 
 * @deprecated	Use \DEV\Core\test\ajaxTester instead.
 */
class ajaxTester extends newAjaxTester {}
//#section_end#
?>