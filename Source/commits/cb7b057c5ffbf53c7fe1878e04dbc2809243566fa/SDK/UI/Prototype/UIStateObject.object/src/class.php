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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

/**
 * UI State Object
 * 
 * Saves and gets the state of a ui object during execution and after page reload.
 * 
 * @version	0.1-1
 * @created	July 28, 2015, 11:56 (EEST)
 * @updated	July 28, 2015, 11:56 (EEST)
 */
abstract class UIStateObject extends UIObjectPrototype
{
	/**
	 * Sets the state of the object.
	 * 
	 * @param	mixed	$state
	 * 		The state value.
	 * 
	 * @return	void
	 */
	public static function setState($state) {}
	
	/**
	 * Gets the state of the object
	 * 
	 * @return	mixed
	 * 		The state value.
	 */
	public static function getState() {}
}
//#section_end#
?>