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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Developer", "codeEditor");

use \UI\Developer\codeEditor as newEditor;

/**
 * Code Editor
 * 
 * Object for code editing purposes.
 * 
 * @version	0.1-1
 * @created	April 26, 2013, 13:23 (EEST)
 * @updated	January 22, 2015, 10:05 (EET)
 * 
 * @deprecated	Use \UI\Developer\codeEditor instead.
 */
class codeEditor extends newEditor {}
//#section_end#
?>