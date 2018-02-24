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

importer::import("UI", "Interactive", "forms::formValidator");

use \UI\Interactive\forms\formValidator as UIFormValidator;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	March 11, 2013, 11:02 (EET)
 * @revised	March 11, 2013, 11:02 (EET)
 * 
 * @deprecated	Use \UI\Interactive\forms\formValidator instead.
 */
class formValidator extends UIFormValidator {}
//#section_end#
?>