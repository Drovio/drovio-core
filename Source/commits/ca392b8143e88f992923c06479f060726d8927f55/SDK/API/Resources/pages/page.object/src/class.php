<?php
//#section#[header]
// Namespace
namespace API\Resources\pages;

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
 * @package	Resources
 * @namespace	\pages
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Resources", "pages::page");

use \SYS\Resources\pages\page as SysPage;

/**
 * System Page Manager
 * 
 * Manages all platform pages.
 * 
 * @version	v. 0.1-0
 * @created	March 24, 2014, 10:50 (EET)
 * @revised	July 8, 2014, 20:05 (EEST)
 * 
 * @deprecated	Use \SYS\Resources\pages\page instead.
 */
class page extends SysPage {}
//#section_end#
?>