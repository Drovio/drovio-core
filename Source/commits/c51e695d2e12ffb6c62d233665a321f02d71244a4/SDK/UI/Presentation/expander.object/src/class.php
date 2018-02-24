<?php
//#section#[header]
// Namespace
namespace UI\Presentation;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "DOM::DOM");
importer::import("ESS", "Prototype", "UIObjectPrototype");

use \API\Platform\DOM\DOM;
use \ESS\Prototype\UIObjectPrototype;

/**
 * Expander
 * 
 * Builds an Expander object. It consists of a head and a body part. The body's view can be toggled by clicking on the head
 * 
 * @version	{empty}
 * @created	April 26, 2013, 12:33 (EEST)
 * @revised	June 12, 2013, 13:35 (EEST)
 * 
 * @deprecated	Use \UI\Presentation\togglers\expander instead.
 */
class expander extends UIObjectPrototype
{
	/**
	 * The head part of the expander
	 * 
	 * @type	DOMElement
	 */
	private $head;
	/**
	 * The body part of the expander
	 * 
	 * @type	DOMElement
	 */
	private $body;
	
	/**
	 * Constructor Method. Initializes the expander object
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		$this->build();
	}
	
	/**
	 * Builds the expander's structure and returns the object for further manipulation.
	 * 
	 * @return	expander
	 * 		{description}
	 */
	public function build()
	{
		$wrapperClass = "expander";
		$headClass = "expanderHead";
		$bodyWrapperClass = "expanderBodyWrapper";
		$expandedClass = "expanded";
	
		$wrapper = DOM::create("div", "", "", $wrapperClass);
		$this->set($wrapper);
		
		$this->head = DOM::create("div", "", "", $headClass);
		DOM::append($wrapper, $this->head);
		
		$bodyWrapper = DOM::create("div", "", "", $bodyWrapperClass);
		DOM::append($wrapper, $bodyWrapper);
		
		$this->body = DOM::create("div");
		DOM::append($bodyWrapper, $this->body);
		
		return $this;
	}
	
	/**
	 * Appends content to the head of the expander and returns the object for further manipulation
	 * 
	 * @param	DOMElement	$element
	 * 		The content to be appended to the expander's head.
	 * 
	 * @return	expander
	 * 		{description}
	 */
	public function appendToHead($element)
	{
		DOM::append($this->head, $element);
		
		return $this;
	}
	
	/**
	 * Appends content to the body of the expander and returns the object for further manipulation
	 * 
	 * @param	DOMElement	$element
	 * 		The content to be appended to the expander's body.
	 * 
	 * @return	expander
	 * 		{description}
	 */
	public function appendToBody($element)
	{
		DOM::append($this->body, $element);
		
		return $this;
	}
	
	/**
	 * Appends content to the head of the expander
	 * 
	 * @param	DOMElement	$element
	 * 		The content to be appended to the expander's head.
	 * 
	 * @return	void
	 * 
	 * @deprecated	use expander::appendToHead instead
	 */
	public function append_to_head($element)
	{
		$this->appendToHead($element);
	}
	
	/**
	 * Appends content to the body of the expander
	 * 
	 * @param	DOMElement	$element
	 * 		The content to be appended to the expander's body.
	 * 
	 * @return	void
	 * 
	 * @deprecated	use expander::appendToBody instead
	 */
	public function append_to_body($element)
	{
		$this->appendToBody($element);
	}
}
//#section_end#
?>