<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents;

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
 * @namespace	\pageComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Core", "components::RTRibbon");

use \UI\Core\components\RTRibbon;

/**
 * HTML Toolbar Ribbon Object
 * 
 * Builds the system's ribbon.
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:13 (EEST)
 * @revised	June 11, 2014, 9:38 (EEST)
 * 
 * @deprecated	Use \UI\Core\components\RTRibbon instead.
 */
class HTMLRibbon extends RTRibbon {}
//#section_end#
?>