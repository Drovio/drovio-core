<?php
//#section#[header]
// Namespace
namespace SYS\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "url");

use \ESS\Environment\url as ESSUrl;

/**
 * Redback URL Manager
 * 
 * Manages all url resolving for navigation and resources.
 * 
 * @version	0.1-2
 * @created	July 8, 2014, 12:51 (EEST)
 * @revised	January 2, 2015, 10:48 (EET)
 * 
 * @deprecated	Use \ESS\Environment\url instead.
 */
class url extends ESSUrl {}
//#section_end#
?>