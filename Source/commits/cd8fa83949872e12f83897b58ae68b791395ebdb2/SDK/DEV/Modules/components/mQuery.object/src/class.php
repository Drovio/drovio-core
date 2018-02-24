<?php
//#section#[header]
// Namespace
namespace DEV\Modules\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Modules
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "resources::paths");
importer::import("API", "Resources", "filesystem::fileManager");
importer::import("API", "Resources", "filesystem::folderManager");
importer::import("API", "Resources", "DOMParser");
importer::import("DEV", "Tools", "parsers::phpParser");
importer::import("DEV", "Tools", "parsers::cssParser");
importer::import("DEV", "Tools", "parsers::jsParser");
importer::import("DEV", "Tools", "coders::phpCoder");

use \API\Developer\resources\paths;
use \API\Resources\filesystem\fileManager;
use \API\Resources\filesystem\folderManager;
use \API\Resources\DOMParser;
use \DEV\Tools\parsers\phpParser;
use \DEV\Tools\parsers\cssParser;
use \DEV\Tools\parsers\jsParser;
use \DEV\Tools\coders\phpCoder;

/**
 * Module Query Manager
 * 
 * Manages the module's sql queries.
 * 
 * @version	{empty}
 * @created	April 2, 2014, 11:47 (EEST)
 * @revised	April 2, 2014, 11:47 (EEST)
 */
class mQuery {}
//#section_end#
?>