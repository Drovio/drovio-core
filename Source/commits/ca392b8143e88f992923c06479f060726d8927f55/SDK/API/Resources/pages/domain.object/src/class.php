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

importer::import("SYS", "Resources", "pages::domain");

use \SYS\Resources\pages\domain as SysDomain;

/**
 * Sub-Domain Manager
 * 
 * Manages all the subdomains of the site.
 * 
 * @version	v. 0.1-0
 * @created	March 24, 2014, 11:07 (EET)
 * @revised	July 8, 2014, 19:53 (EEST)
 * 
 * @deprecated	Use \SYS\Resources\pages\domain instead.
 */
class domain extends SysDomain {}
//#section_end#
?>