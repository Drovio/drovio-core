<?php
//#section#[header]
// Namespace
namespace ESS\Prototype;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * UI State Object
 * 
 * Saves and gets the state of a ui object during execution and after page reload.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 11:40 (EET)
 * @revised	March 11, 2013, 11:40 (EET)
 */
abstract class UIStateObject extends UIObjectPrototype
{
	/**
	 * Sets the state of the object.
	 * 
	 * @param	string	$state
	 * 		The state value.
	 * 
	 * @return	void
	 */
	public static function setState($state)
	{
	}
	
	/**
	 * Gets the state of the object
	 * 
	 * @return	mixed
	 */
	public static function getState()
	{
	}
}
//#section_end#
?>