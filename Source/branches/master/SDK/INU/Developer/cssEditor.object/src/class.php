<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Developer", "editors::CSSEditor");

use \UI\Developer\editors\CSSEditor as newCSSEditor;

/**
 * CSS Editor
 * 
 * This is a simple html editor with a preview and a css code part.
 * 
 * @version	1.0-1
 * @created	April 26, 2013, 14:13 (EEST)
 * @revised	September 8, 2014, 15:40 (EEST)
 * 
 * @deprecated	Use \UI\Developer\editors\CSSEditor instead.
 */
class cssEditor extends newCSSEditor {}
//#section_end#
?>