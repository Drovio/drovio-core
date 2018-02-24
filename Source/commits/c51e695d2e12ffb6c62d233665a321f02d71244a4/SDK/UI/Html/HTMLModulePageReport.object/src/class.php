<?php
//#section#[header]
// Namespace
namespace UI\Html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Html
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "HTMLPageReport");

use \UI\Html\HTMLPageReport;

/**
 * Module Report
 * 
 * Builds a report for module pages.
 * 
 * @version	{empty}
 * @created	March 12, 2013, 11:28 (EET)
 * @revised	March 12, 2013, 12:16 (EET)
 * 
 * @deprecated	Use \UI\Html\HTMLPageReport instead.
 */
class HTMLModulePageReport extends HTMLPageReport {}
//#section_end#
?>