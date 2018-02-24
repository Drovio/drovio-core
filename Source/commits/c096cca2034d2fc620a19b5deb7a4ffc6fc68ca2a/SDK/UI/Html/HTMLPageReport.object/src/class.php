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

importer::import("UI", "Core", "RCPageReport");

use \UI\Core\RCPageReport;

/**
 * HTML Page Report
 * 
 * Generates page reports.
 * 
 * @version	{empty}
 * @created	August 12, 2013, 11:22 (EEST)
 * @revised	June 11, 2014, 9:08 (EEST)
 * 
 * @deprecated	Use \UI\Core\RCPageReport instead.
 */
class HTMLPageReport extends RCPageReport {}
//#section_end#
?>