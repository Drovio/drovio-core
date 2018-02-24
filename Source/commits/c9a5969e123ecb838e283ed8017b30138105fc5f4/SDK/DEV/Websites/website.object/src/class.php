<?php
//#section#[header]
// Namespace
namespace DEV\Websites;

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
 * @package	Websites
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Projects", "project");

use \DEV\Projects\project;

/**
 * Website Manager Class
 * 
 * This is the main class for managing a website project.
 * 
 * @version	{empty}
 * @created	June 30, 2014, 12:58 (EEST)
 * @revised	June 30, 2014, 12:58 (EEST)
 */
class website extends project {}
//#section_end#
?>