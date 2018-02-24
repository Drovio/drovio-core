<?php
//#section#[header]
// Namespace
namespace UI\Forms\formControls;

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
 * @package	Forms
 * @namespace	\formControls
 * 
 * @copyright	Copyright (C) 2016 Drovio. All rights reserved.
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
 * @version	0.2-3
 * @created	March 11, 2013, 13:52 (CET)
 * @updated	November 12, 2016, 16:07 (CET)
 * 
 * @deprecated	Moved to Panda
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
	 * @param	mixed	$context
	 * 		The item context.
	 * 		It can be string or DOMElement.
	 * 
	 * @return	formItem
	 * 		The formItem object.
	 */
	public function build($tag = "", $name = "", $id = "", $value = "", $class = "", $context = "")
	{
		// Build element
		$element = DOM::create($tag, $context, $id, $class);
		DOM::attr($element, "name", $name);
		DOM::attr($element, "value", $value);
		
		// Set ui object holder
		$this->set($element);
		
		return $this;
	}
}
//#section_end#
?>