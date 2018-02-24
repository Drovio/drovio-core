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
 * @namespace	{empty}
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
 * @version	{empty}
 * @created	October 23, 2013, 10:54 (EEST)
 * @revised	July 8, 2014, 12:52 (EEST)
 * 
 * @deprecated	Use \SYS\Resources\url instead.
 */
class url extends SySurl {}
//#section_end#
?>