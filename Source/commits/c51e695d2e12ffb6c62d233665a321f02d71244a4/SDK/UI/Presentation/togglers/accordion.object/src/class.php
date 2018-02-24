<?php
//#section#[header]
// Namespace
namespace UI\Presentation\togglers;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Presentation
 * @namespace	\togglers
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Presentation", "togglers::toggler");

use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;
use \UI\Presentation\togglers\toggler;

/**
 * Accordion Object
 * 
 * Builds an accordion object. It consists of multiple togglers, only one of which stays open.
 * 
 * @version	{empty}
 * @created	June 12, 2013, 15:16 (EEST)
 * @revised	June 12, 2013, 15:16 (EEST)
 */
class accordion extends UIObjectPrototype
{
	/**
	 * Builds the accordion object.
	 * 
	 * @param	string	$id
	 * 		The object's id.
	 * 
	 * @return	accordion
	 * 		The accordion object.
	 */
	public function build($id = "")
	{
		// Create the holder
		$holder = DOM::create("div", "", $id, "accordion");
		$this->set($holder);

		return $this;
	}
	
	/**
	 * Adds a slice to the accordion.
	 * 
	 * @param	string	$id
	 * 		The slice's id.
	 * 
	 * @param	DOMElement	$head
	 * 		The head of the slice.
	 * 
	 * @param	DOMElement	$content
	 * 		The content of the slice.
	 * 
	 * @param	boolean	$selected
	 * 		Indicates whether this slice will be the selected (open).
	 * 
	 * @return	accordion
	 * 		The accordion object.
	 */
	public function addSlice($id, $head, $content, $selected = FALSE)
	{
		// Create the toggler
		$tog = new toggler();
		$togElement = $tog->build($id, $head, $content, $selected)->get();
		DOM::append($this->get(), $togElement);
		
		return $this;
	}
}
//#section_end#
?>