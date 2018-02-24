<?php
//#section#[header]
// Namespace
namespace UI\Developer\editors;

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
 * @package	Developer
 * @namespace	\editors
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("UI", "Developer", "editors/HTML5Editor");

use \UI\Developer\editors\HTML5Editor;

/**
 * HTML Editor
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	July 16, 2015, 14:33 (EEST)
 * @updated	July 16, 2015, 14:33 (EEST)
 * 
 * @deprecated	USe \UI\Developer\editors\HTML5Editor instead.
 */
class HTMLEditor extends HTML5Editor {}
//#section_end#
?>