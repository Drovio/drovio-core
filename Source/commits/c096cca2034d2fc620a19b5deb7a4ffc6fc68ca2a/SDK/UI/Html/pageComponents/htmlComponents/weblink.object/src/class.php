<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\htmlComponents;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\pageComponents\htmlComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "components::weblink");

use \UI\Html\components\weblink as wlink;

/**
 * Hyperlink Factory
 * 
 * It is used for building fast weblinks
 * 
 * @version	{empty}
 * @created	March 12, 2013, 16:01 (EET)
 * @revised	June 11, 2014, 10:00 (EEST)
 * 
 * @deprecated	Use \UI\Html\components\weblink instead.
 */
class weblink extends wlink {}
//#section_end#
?>