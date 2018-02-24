<?php
//#section#[header]
// Namespace
namespace API\Developer\resources;

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
 * @namespace	\resources
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Resources", "paths");

use \DEV\Resources\paths as DEVPaths;

/**
 * Redback developer paths.
 * 
 * Keeps all the developer paths.
 * 
 * @version	{empty}
 * @created	October 7, 2013, 12:32 (EEST)
 * @revised	July 3, 2014, 16:33 (EEST)
 * 
 * @deprecated	Use \DEV\Resources\paths instead.
 */
class paths extends DEVPaths {}
//#section_end#
?>