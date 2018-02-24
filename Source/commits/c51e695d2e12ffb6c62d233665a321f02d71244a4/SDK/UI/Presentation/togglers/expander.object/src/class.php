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

importer::import("UI", "Html", "DOM");
importer::import("ESS", "Prototype", "UIObjectPrototype");

use \UI\Html\DOM;
use \ESS\Prototype\UIObjectPrototype;

/**
 * Expander
 * 
 * Builds an Expander object. It consists of a miniature, an expansion, and the toggling switch. 
 * By clicking the switch, the miniature and the expansion swap "displays".
 * 
 * @version	{empty}
 * @created	June 12, 2013, 15:19 (EEST)
 * @revised	June 12, 2013, 15:19 (EEST)
 */
class expander extends UIObjectPrototype
{
	/**
	 * The miniature holder of the expander.
	 * 
	 * @type	DOMElement
	 */
	private $miniature;
	/**
	 * The expansion holder of the expander.
	 * 
	 * @type	DOMElement
	 */
	private $expansion;
	
	
	/**
	 * Builds the expander's structure and returns the object for further manipulation.
	 * 
	 * @param	DOMElement	$miniature
	 * 		An element to be appended in the miniature part of the expander.
	 * 
	 * @param	DOMElement	$expansion
	 * 		An element to be appended in the expansion part of the expander.
	 * 
	 * @param	boolean	$expanded
	 * 		The initial state of the expander. Default is minified.
	 * 
	 * @return	DOMElement
	 * 		The expander object.
	 */
	public function build($miniature = NULL, $expansion = NULL, $expanded = FALSE)
	{
		$wrapperClass = "expander";
		$miniatureClass = "miniature";
		$expansionClass = "expansion";
		$switchClass = "switch";
		$switchText = "More";
	
		if ($expanded)
		{
			$wrapperClass = "expander expanded";
			$switchText = "Less";
		}
	
		$wrapper = DOM::create("div", "", "", $wrapperClass);
		$this->set($wrapper);
		
		$this->miniature = DOM::create("div", "", "", $miniatureClass);
		DOM::append($wrapper, $this->miniature);
		if (!empty($miniature))
			DOM::append($this->miniature, $miniature);
				
		$this->expansion = DOM::create("div", "", "", $expansionClass);
		DOM::append($wrapper, $this->expansion);
		if (!empty($expansion))
			DOM::append($this->expansion, $expansion);
	
		$switch = DOM::create("div", $switchText, "", $switchClass);
		DOM::append($wrapper, $switch);
	
		return $this;
	}
	
	/**
	 * Appends an element to the miniature section
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	DOMElement
	 * 		The expander
	 */
	public function appendToMiniature($element)
	{
		DOM::append($this->miniature, $element);
		
		return $this;
	}
	
	/**
	 * Appends an element to the expansion section
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	DOMElement
	 * 		The expander
	 */
	public function appendToExpansion($element)
	{
		DOM::append($this->expansion, $element);
		
		return $this;
	}
}
//#section_end#
?>