<?php
//#section#[header]
// Namespace
namespace API\Resources;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Resources", "url");

use \SYS\Resources\url as SySurl;

/**
 * Redback URL Manager
 * 
 * Manages all url resolving for navigation and resources.
 * 
 * @version	0.1-1
 * @created	October 23, 2013, 10:54 (EEST)
 * @revised	October 23, 2014, 14:22 (EEST)
 * 
 * @deprecated	Use \ESS\Environment\url instead.
 */
class url extends SySurl {}
//#section_end#
?>