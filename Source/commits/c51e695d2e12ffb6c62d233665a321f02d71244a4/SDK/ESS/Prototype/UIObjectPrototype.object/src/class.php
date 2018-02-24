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

/**
 * UI Object Prototype
 * 
 * It is the prototype for all ui objects.
 * All ui objects must inherit this class and implement the "build" method to build the object.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 10:07 (UTC)
 * @revised	March 7, 2013, 10:07 (UTC)
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
	 * It's the abstract Object Builder Function
	 * 
	 * @return	mixed
	 */
	abstract public function build();
	
	/**
	 * Returns the UI Object Holder
	 * 
	 * @return	DOMElement
	 */
	public function get()
	{
		return $this->UIObjectHolder;
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