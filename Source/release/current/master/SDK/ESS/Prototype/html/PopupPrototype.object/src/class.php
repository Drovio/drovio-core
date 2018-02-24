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

importer::import("UI", "Prototype", "PopupPrototype");

use \UI\Prototype\PopupPrototype as newPopupPrototype;

/**
 * Popup Prototype
 * 
 * This is the prototype for building any kind of popup.
 * 
 * @version	1.0-1
 * @created	March 7, 2013, 12:05 (EET)
 * @updated	July 28, 2015, 12:29 (EEST)
 * 
 * @deprecated	Use \UI\Prototype\PopupPrototype instead.
 */
class PopupPrototype extends newPopupPrototype {}
//#section_end#
?>