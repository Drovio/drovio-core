<?php
//#section#[header]
// Namespace
namespace UI\Forms\formControls;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\formControls
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Form Item
 * 
 * Represents the minimum form control item.
 * 
 * @version	{empty}
 * @created	March 11, 2013, 14:52 (EET)
 * @revised	April 18, 2013, 13:09 (EEST)
 */
class formItem extends UIObjectPrototype
{
	/**
	 * Builds the form control item.
	 * 
	 * @param	string	$tag
	 * 		The item's tagName
	 * 
	 * @param	string	$name
	 * 		The item's name
	 * 
	 * @param	string	$id
	 * 		The item's id
	 * 
	 * @param	string	$value
	 * 		The item's value
	 * 
	 * @param	string	$class
	 * 		The item's class.
	 * 
	 * @return	formItem
	 */
	public function build($tag = "", $name = "", $id = "", $value = "", $class = "")
	{
		// Build element
		$element = DOM::create($tag, "", $id, $class);
		DOM::attr($element, "name", $name);
		DOM::attr($element, "value", $value);
		
		// Set ui object holder
		$this->set($element);
		
		return $this;
	}
}
//#section_end#
?>