<?php
//#section#[header]
// Namespace
namespace UI\Forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Forms", "special::formCaptcha");

use \UI\Forms\special\formCaptcha as SpecialFormCaptcha;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	March 11, 2013, 12:09 (EET)
 * @revised	March 11, 2013, 12:09 (EET)
 * 
 * @deprecated	Use \UI\Forms\special\formCaptcha instead.
 */
class formCaptcha extends SpecialFormCaptcha {}
//#section_end#
?>