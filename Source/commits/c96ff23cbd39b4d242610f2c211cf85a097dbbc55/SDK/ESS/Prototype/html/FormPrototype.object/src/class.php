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

importer::import("ESS", "Protocol", "client::FormProtocol");
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
 * @version	{empty}
 * @created	March 7, 2013, 12:05 (EET)
 * @revised	February 6, 2014, 10:28 (EET)
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
	 * 		The form id
	 * 
	 * @param	{type}	$preventUnload
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function __construct($id = "", $preventUnload = FALSE)
	{
		// Set the formID
		$this->formID = ($id == "" ? "frm_".mt_rand() : $id);
		$this->pu = $preventUnload;
		
		// Register the form
		FormProtocol::register($this->formID);
	}
	
	/**
	 * Builds the form spine and sets the UIObject
	 * 
	 * @param	string	$action
	 * 		The action of the form (if any).
	 * 
	 * @return	FormPrototype
	 * 		{description}
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
		if ($this->pu)
			FormProtocol::setPreventUnload($form);
		
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
	 * 		{description}
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
	 * 		{description}
	 */
	public function appendReport($element)
	{
		if (is_null($element))
			return FALSE;
		
		DOM::append($this->formReport, $element);
		
		return $this;
	}
	
	/**
	 * Adds a module POST action to the form.
	 * 
	 * @param	int	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module (if any).
	 * 
	 * @return	FormPrototype
	 * 		{description}
	 */
	public function setAction($moduleID, $action = "")
	{
		// Get form element
		$form = $this->get();
		
		// Set module action
		FormProtocol::engage($form, $moduleID, $action);
		
		return $this;
	}
	
	/**
	 * Sets the async attribute to the given form element
	 * 
	 * @return	FormPrototype
	 * 		The FormPrototype object.
	 */
	public function setAsync()
	{
		// Get form element
		$form = $this->get();
		
		// Set async action
		FormProtocol::setAsync($form, $moduleID, $action);
		
		return $this;
	}
	
	/**
	 * Store a hidden input's value for later validation.
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
	 * 		{description}
	 */
	public function getFormID()
	{
		return $this->formID;
	}
}
//#section_end#
?>