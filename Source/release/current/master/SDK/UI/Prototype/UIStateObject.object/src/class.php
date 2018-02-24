<?php
//#section#[header]
// Namespace
namespace UI\Prototype;

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
 * @package	Prototype
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * UI State Object
 * 
 * Saves and gets the state of a ui object during execution and after page reload.
 * 
 * @version	1.0-1
 * @created	July 28, 2015, 9:56 (BST)
 * @updated	November 20, 2015, 16:57 (GMT)
 */
class UIStateObject {}
//#section_end#
?>