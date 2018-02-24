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

importer::import("UI", "Interactive", "forms::formAutoSuggest");

use \UI\Interactive\forms\formAutoSuggest;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	March 11, 2013, 11:34 (EET)
 * @revised	March 11, 2013, 11:34 (EET)
 * 
 * @deprecated	Use \UI\Interactive\forms\formAutoSuggest instead.
 */
class autoSuggest extends formAutoSuggest {}
//#section_end#
?>