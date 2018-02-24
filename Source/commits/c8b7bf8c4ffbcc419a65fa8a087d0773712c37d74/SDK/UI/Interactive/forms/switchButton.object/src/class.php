<?php
//#section#[header]
// Namespace
namespace UI\Interactive\forms;

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
 * @package	Interactive
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Forms", "interactive/switchButtonForm");

use \UI\Forms\interactive\switchButtonForm;

/**
 * Switch Button
 * 
 * Displays an interactive switch button.
 * It is an autonomous form that works separately from other forms.
 * 
 * @version	4.0-2
 * @created	March 28, 2013, 11:11 (GMT)
 * @updated	November 16, 2015, 15:05 (GMT)
 * 
 * @deprecated	Use \UI\Forms\interactive\switchButtonForm instead.
 */
class switchButton extends switchButtonForm {}
//#section_end#
?>