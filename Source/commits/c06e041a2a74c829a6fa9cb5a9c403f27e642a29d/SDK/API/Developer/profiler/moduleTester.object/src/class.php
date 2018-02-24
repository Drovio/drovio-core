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

importer::import("DEV", "Profiler", "test::moduleTester");

use \DEV\Profiler\test\moduleTester as DEVModuleTester;

/**
 * Module Tester Manager
 * 
 * Manages module tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 17:56 (EET)
 * @revised	February 10, 2014, 11:39 (EET)
 * 
 * @deprecated	Use DEV\Profiler\test\moduleTester instead.
 */
class moduleTester extends DEVModuleTester {}
//#section_end#
?>