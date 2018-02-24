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

importer::import("SYS", "Resources", "pages::sitemap");

use \SYS\Resources\pages\sitemap as SysSitemap;

/**
 * Sitemap Manager
 * 
 * Generates the Redback Sitemap file
 * 
 * @version	v. 0.1-0
 * @created	April 4, 2014, 11:01 (EEST)
 * @revised	July 9, 2014, 11:01 (EEST)
 * 
 * @deprecated	Use \SYS\Resources\pages\sitemap instead.
 */
class sitemap extends SysSitemap {}
//#section_end#
?>