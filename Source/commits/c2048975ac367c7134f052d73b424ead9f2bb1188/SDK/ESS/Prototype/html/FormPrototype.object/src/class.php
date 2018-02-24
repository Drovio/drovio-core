<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client/FormProtocol");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\FormProtocol;
use \ESS\Prototype\UIObjectPrototype;
use \UI\Html\DOM;

/**
 * Form Builder Prototype
 * 
 * It's the prototype for building every form in the system.
 * All the form objects must inherit this FormPrototype in order to build the spine of a form well-formed.
 * It implements the FormProtocol.
 * 
 * @version	1.0-2
 * @created	March 7, 2013, 12:05 (EET)
 * @revised	December 23, 2014, 13:57 (EET)
 */
class FormPrototype extends UIObjectPrototype
{
	/**
	 * The form's id.
	 * 
	 * @type	string
	 */
	private $formID;
	
	/**
	 * The form report element container.
	 * 
	 * @type	DOMElement
	 */
	private $formReport;
	
	/**
	 * The form body element container.
	 * 
	 * @type	DOMElement
	 */
	private $formBody;
	
	/**
	 * Defines whether the form will prevent page unload on edit.
	 * 
	 * @type	boolean
	 */
	private $pu = FALSE;
	
	/**
	 * Constructor Method. Defines the form ID.
	 * 
	 * @param	string	$id
	 * 		The form element id.
	 * 
	 * @param	mixed	$preventUnload
	 * 		Set an empty value (NULL, FALSE or anything that empty() returns as TRUE) and it will deactivate this form from preventing unload.
	 * 		Set TRUE to prevent unload with a system message or give a message to show to the user specific for this form.
	 * 		It is FALSE by default.
	 * 
	 * @return	void
	 */
	public function __construct($id = "", $preventUnload = FALSE)
	{
		// Set the formID
		$this->formID = ($id == "" ? "f".mt_rand() : $id);
		$this->pu = $preventUnload;
		
		// Register the form
		FormProtocol::register($this->formID);
	}
	
	/**
	 * Builds the form spine and sets the UIObject
	 * 
	 * @param	string	$action
	 * 		The form's action attribute (if any).
	 * 
	 * @return	FormPrototype
	 * 		The FormPrototype object.
	 */
	public function build($action = "")
	{		
		// Create form element
		$form = DOM::create("form", "", $this->formID);
		DOM::attr($form, "method", "post");
		DOM::attr($form, "action", $action);
		
		// Create form report element
		$this->formReport = DOM::create("div", "", "", "formReport");
		DOM::append($form, $this->formReport);
		
		// Create form body element
		$this->formBody = DOM::create("div", "", "", "formBody");
		DOM::append($form, $this->formBody);
		
		// Set before unload parameter
		FormProtocol::setPreventUnload($form, $this->pu);
		
		// Set Object
		$this->set($form);
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to the form body.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	FormPrototype
	 * 		The FormPrototype object.
	 */
	public function append($element)
	{
		if (is_null($element))
			return FALSE;
		
		DOM::append($this->formBody, $element);
		
		return $this;
	}
	
	/**
	 * Appends a DOMElement to the form report container.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	FormPrototype
	 * 		The FormPrototype object.
	 */
	public function appendReport($element)
	{
		if (is_null($element))
			return FALSE;
		
		DOM::append($this->formReport, $element);
		
		return $this;
	}
	
	/**
	 * Sets the async attribute to the given form element.
	 * An async form posts through our Async Communication Protocol to the defined action url.
	 * 
	 * @return	FormPrototype
	 * 		The FormPrototype object.
	 */
	public function setAsync()
	{
		// Get form element
		$form = $this->get();
		
		// Set async action
		FormProtocol::setAsync($form);
		
		return $this;
	}
	
	/**
	 * Store a hidden input's value for validation during post.
	 * 
	 * @param	string	$name
	 * 		The input name.
	 * 
	 * @param	string	$value
	 * 		The input value.
	 * 
	 * @return	void
	 */
	public function setHiddenValue($name, $value)
	{
		FormProtocol::registerVal($this->formID, $name, $value);
	}
	
	/**
	 * Get the form's id.
	 * 
	 * @return	string
	 * 		The form's id attribute as set when the form was built.
	 */
	public function getFormID()
	{
		return $this->formID;
	}
}
//#section_end#
?>