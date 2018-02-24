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

importer::import("UI", "Core", "components::RNavToolbar");
use \UI\Core\components\RNavToolbar;

/**
 * HTML Navigation Toolbar
 * 
 * Builds the system's top navigation toolbar
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:02 (EEST)
 * @revised	June 11, 2014, 9:16 (EEST)
 * 
 * @deprecated	Use \UI\Core\components\RNavToolbar instead.
 */
class HTMLNavToolbar extends RNavToolbar {}
//#section_end#
?>