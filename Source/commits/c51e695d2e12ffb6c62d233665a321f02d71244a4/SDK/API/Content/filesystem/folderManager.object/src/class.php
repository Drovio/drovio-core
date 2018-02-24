<?php
//#section#[header]
// Namespace
namespace API\Content\filesystem;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "filesystem::folderManager");

use \API\Resources\filesystem\folderManager as APIFolderManager;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 * 
 * @deprecated	Use \API\Resources\filesystem\folderManager instead.
 */
class folderManager extends APIFolderManager {}
//#section_end#
?>