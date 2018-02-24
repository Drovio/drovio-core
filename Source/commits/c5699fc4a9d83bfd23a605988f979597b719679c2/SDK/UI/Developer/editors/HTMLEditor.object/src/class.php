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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Developer", "editors::CSSEditor");

use \UI\Developer\editors\CSSEditor;

/**
 * WYSIWYG HTML Editor.
 * 
 * A simple HTML Editor.
 * 
 * @version	0.1-1
 * @created	September 8, 2014, 16:09 (EEST)
 * @revised	September 8, 2014, 16:09 (EEST)
 */
class HTMLEditor extends CSSEditor {}
//#section_end#
?>