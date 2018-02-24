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

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * UI Object Prototype
 * 
 * It is the prototype for all ui objects.
 * All ui objects must inherit this class and implement the "build" method to build the object.
 * 
 * @version	1.0-2
 * @created	July 28, 2015, 11:48 (EEST)
 * @updated	September 3, 2015, 12:22 (EEST)
 */
abstract class UIObjectPrototype
{
	/**
	 * The UI Object Holder
	 * 
	 * @type	DOMElement
	 */
	protected $UIObjectHolder;

	/**
	 * It's the abstract Object Builder Function.
	 * 
	 * @return	this
	 * 		It should return the current object (this) to support chain pattern.
	 */
	abstract public function build();
	
	/**
	 * Get the object holder.
	 * 
	 * @return	DOMElement
	 * 		The object holder DOMElement.
	 */
	public function get()
	{
		return $this->UIObjectHolder;
	}
	
	/**
	 * Append a DOMElement to the root object holder.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended to the object holder.
	 * 
	 * @return	UIObjectPrototype
	 * 		The UIObjectPrototype object.
	 * 		For every class that extends this class, this function should return the current object (this).
	 */
	public function append($element)
	{
		// Check if element is not empty
		if (empty($element))
			return FALSE;
		
		// Append element to object holder
		if (isset($this->UIObjectHolder) && is_object($this->UIObjectHolder))
			DOM::append($this->UIObjectHolder, $element);
		
		return $this;
	}
	
	/**
	 * Sets the UI Object Holder that the inherited class has created.
	 * 
	 * @param	DOMElement	$object
	 * 		The object holder.
	 * 
	 * @return	void
	 */
	protected function set($object = NULL)
	{
		$this->UIObjectHolder = $object;
	}
}
//#section_end#
?>