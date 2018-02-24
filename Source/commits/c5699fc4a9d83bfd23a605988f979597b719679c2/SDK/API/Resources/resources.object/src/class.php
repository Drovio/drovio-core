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

importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "filesystem::fileManager");

use \API\Resources\filesystem\folderManager;
use \API\Resources\filesystem\fileManager;

/**
 * Resource Manager/Helper
 * 
 * Handles some basic file resources of the system. It includes the resource paths.
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 14:03 (EEST)
 * @revised	September 8, 2014, 16:00 (EEST)
 * 
 * @deprecated	This class is deprecated and no longer used. Use \DEV\Resources\paths instead.
 */
class resources
{
	/**
	 * The resources' path
	 * 
	 * @type	string
	 */
	const PATH = "/System/Resources/";
}
//#section_end#
?>