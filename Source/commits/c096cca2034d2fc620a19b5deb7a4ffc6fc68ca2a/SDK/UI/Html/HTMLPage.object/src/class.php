<?php
//#section#[header]
// Namespace
namespace UI\Html;

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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Core", "RCPage");
use \UI\Core\RCPage;

/**
 * System HTML Page
 * 
 * Builds the spine of an HTML page and sets the events to fill the blanks (modules).
 * 
 * @version	{empty}
 * @created	March 17, 2013, 11:18 (EET)
 * @revised	June 11, 2014, 9:05 (EEST)
 * 
 * @deprecated	Use \UI\Core\RCPage instead.
 */
class HTMLPage extends RCPage {}
//#section_end#
?>