<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\toolbarComponents;

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
 * @namespace	\pageComponents\toolbarComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Core", "components::toolbar::toolbarItem");

use \UI\Core\components\toolbar\toolbarItem as cToolbarItem;

/**
 * Toolbar Menu Item
 * 
 * Builds a toolbar menu item
 * 
 * @version	{empty}
 * @created	April 11, 2013, 10:39 (EEST)
 * @revised	June 11, 2014, 9:46 (EEST)
 * 
 * @deprecated	Use \UI\Core\components\toolbar\toolbarItem instead.
 */
class toolbarItem extends cToolbarItem {}
//#section_end#
?>