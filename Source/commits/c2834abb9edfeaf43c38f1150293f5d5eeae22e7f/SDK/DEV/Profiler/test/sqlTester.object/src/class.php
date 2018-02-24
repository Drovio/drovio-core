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

importer::import("DEV", "Core", "test::sqlTester");

use \DEV\Core\test\sqlTester as newSqlTester;

/**
 * SQL Query Tester Manager
 * 
 * Manages sql query tester mode.
 * 
 * @version	0.1-3
 * @created	February 10, 2014, 11:54 (EET)
 * @revised	September 17, 2014, 11:58 (EEST)
 * 
 * @deprecated	Use \DEV\Core\test\sqlTester instead.
 */
class sqlTester extends newSqlTester {}
//#section_end#
?>