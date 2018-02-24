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

importer::import("UI", "Developer", "editors/WViewEditor");

use \UI\Developer\editors\WViewEditor;

/**
 * CSS Editor control
 * 
 * This is a simple html editor with a preview and a css code part.
 * 
 * @version	2.0-2
 * @created	September 8, 2014, 15:59 (EEST)
 * @updated	July 19, 2015, 10:56 (EEST)
 * 
 * @deprecated	Use \UI\Developer\editors\CSS3Editor instead.
 */
class CSSEditor extends WViewEditor {}
//#section_end#
?>