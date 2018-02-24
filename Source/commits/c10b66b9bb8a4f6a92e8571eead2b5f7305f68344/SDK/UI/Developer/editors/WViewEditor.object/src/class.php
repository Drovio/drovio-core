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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Developer", "editors/CSSEditor");
importer::import("UI", "Developer", "editors/HTMLEditor");

use \UI\Developer\editors\CSSEditor;
use \UI\Developer\editors\HTMLEditor;

/**
 * Web View Editor
 * 
 * It creates a complete editor for web views as Redback sees it.
 * It includes an HTMLEditor for the web view content and a CSSEditor and the content's looks.
 * 
 * @version	0.1-1
 * @created	May 2, 2015, 10:48 (EEST)
 * @updated	May 2, 2015, 10:48 (EEST)
 */
class WViewEditor extends CSSEditor {}
//#section_end#
?>