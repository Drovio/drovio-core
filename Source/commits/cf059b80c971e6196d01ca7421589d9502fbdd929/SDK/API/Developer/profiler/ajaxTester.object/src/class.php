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

importer::import("DEV", "Profiler", "test::ajaxTester");

use \DEV\Profiler\test\ajaxTester as DEVAjaxTester;

/**
 * Ajax File Tester Manager
 * 
 * Manages ajax file tester mode.
 * 
 * @version	{empty}
 * @created	December 20, 2013, 16:43 (EET)
 * @revised	February 10, 2014, 11:51 (EET)
 * 
 * @deprecated	Use DEV\Profiler\test\ajaxTester instead.
 */
class ajaxTester extends DEVAjaxTester {}
//#section_end#
?>