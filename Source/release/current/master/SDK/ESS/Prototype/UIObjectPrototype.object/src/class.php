<?php
//#section#[header]
// Namespace
namespace ESS\Prototype;

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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("UI", "Prototype", "UIObjectPrototype");

use \UI\Prototype\UIObjectPrototype as newUIObjectPrototype;

/**
 * UI Object Prototype
 * 
 * It is the prototype for all ui objects.
 * All ui objects must inherit this class and implement the "build" method to build the object.
 * 
 * @version	1.0-1
 * @created	March 7, 2013, 12:07 (EET)
 * @updated	July 28, 2015, 11:48 (EEST)
 * 
 * @deprecated	Use \UI\Prototype\UIObjectPrototype instead.
 */
abstract class UIObjectPrototype extends newUIObjectPrototype {}
//#section_end#
?>