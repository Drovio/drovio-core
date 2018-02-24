<?php
//#section#[header]
// Namespace
namespace UI\Html\pageComponents\ribbonComponents;

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
 * @namespace	\pageComponents\ribbonComponents
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Core", "components::ribbon::rPanel");

use \UI\Core\components\ribbon\rPanel;

/**
 * Ribbon Panel
 * 
 * Builds a ribbon panel holder
 * 
 * @version	{empty}
 * @created	April 5, 2013, 10:07 (EEST)
 * @revised	June 11, 2014, 9:51 (EEST)
 * 
 * @deprecated	Use \UI\Core\components\ribbon\rPanel instead.
 */
class ribbonPanel extends rPanel {}
//#section_end#
?>