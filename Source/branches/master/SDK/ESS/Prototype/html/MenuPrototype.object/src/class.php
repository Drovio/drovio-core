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

importer::import("UI", "Prototype", "MenuPrototype");

use \UI\Prototype\MenuPrototype as newMenuPrototype;

/**
 * Menu Prototype
 * 
 * This is the menu prototype (implements the composite pattern).
 * All menus (and extensions of it) must inherit this prototype and build a proper menu object.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 12:04 (EET)
 * @updated	July 28, 2015, 12:43 (EEST)
 * 
 * @deprecated	Use \UI\Prototype\MenuPrototype instead.
 */
class MenuPrototype extends newMenuPrototype {}
//#section_end#
?>