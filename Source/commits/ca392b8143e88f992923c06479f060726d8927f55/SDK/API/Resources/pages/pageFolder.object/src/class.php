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

importer::import("SYS", "Resources", "pages::pageFolder");

use \SYS\Resources\pages\pageFolder as SysPageFolder;

/**
 * Folder Manager
 * 
 * The system's folder manager.
 * 
 * @version	v. 0.1-0
 * @created	March 24, 2014, 10:58 (EET)
 * @revised	July 9, 2014, 10:51 (EEST)
 * 
 * @deprecated	Use \SYS\Resources\pages\pageFolder instead.
 */
class pageFolder extends SysPageFolder {}
//#section_end#
?>