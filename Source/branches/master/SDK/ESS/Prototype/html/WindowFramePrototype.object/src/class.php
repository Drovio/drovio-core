<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("UI", "Prototype", "WindowFramePrototype");

use \UI\Prototype\WindowFramePrototype as newWindowFramePrototype;

/**
 * Window Frame Prototype
 * 
 * It's the window frame prototype for building frames (windows, dialogs etc.).
 * 
 * @version	2.0-1
 * @created	May 9, 2013, 15:51 (EEST)
 * @updated	July 28, 2015, 12:18 (EEST)
 * 
 * @deprecated	Use \UI\Prototype\WindowFramePrototype instead.
 */
class WindowFramePrototype extends newWindowFramePrototype {}
//#section_end#
?>