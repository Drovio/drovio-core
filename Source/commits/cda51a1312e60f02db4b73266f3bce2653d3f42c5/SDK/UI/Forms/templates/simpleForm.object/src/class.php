<?php
//#section#[header]
// Namespace
namespace UI\Forms\templates;

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
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "formFactory");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\client\FormProtocol;
use \API\Literals\literal;
use \UI\Forms\formFactory;
use \UI\Html\DOM;

/**
 * Simple Form Template builder
 * 
 * Builds an html form with a specific layout, if the user wants to.
 * It has access to the FormFactory (it extends it) and can build every form control.
 * 
 * @version	0.1-1
 * @created	April 18, 2013, 11:21 (EEST)
 * @revised	July 18, 2014, 11:31 (EEST)
 */
class simpleForm extends formFactory
{
	/**
	 * Builds the form.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 
	 * @param	boolean	$controls
	 * 		Options whether the form will have built in specific controls (submit and reset buttons).
	 * 
	 * @param	boolean	$async
	 * 		Sets the form to post async.
	 * 
	 * @return	simpleForm
	 * 		The simpleForm object.
	 */
	public function build($moduleID = "", $action = "", $controls = TRUE, $async = TRUE)
	{
		// Create Form
		$formAction = $action;
		if (!empty($moduleID))
			$formAction = "";
		$form = parent::build($formAction, $async)->get();
		DOM::appendAttr($form, "class", "simpleForm");
		
		// Set module action
		if (!empty($moduleID))
			$this->setModuleAction($moduleID, $action);
		
		if (!$controls)
			return $this;
		
		// Build form controls
		$this->buildControls();
		
		return $this;
	}
	
	/**
	 * Builds and inserts a form row including a label and an input.
	 * 
	 * @param	mixed	$title
	 * 		The label's title. It can be a DOMElement or a string (which will be nested into a span).
	 * 
	 * @param	DOMElement	$input
	 * 		The input to be inserted to the form row.
	 * 
	 * @param	boolean	$required
	 * 		Defines whether the input given is required.
	 * 
	 * @param	string	$notes
	 * 		Notes for the user to insert valid input.
	 * 
	 * @return	simpleForm
	 * 		The simpleForm object.
	 */
	public function insertRow($title, $input, $required = FALSE, $notes = "")
	{
		// Build the row
		$row = $this->buildRow($title, $input, $required, $notes);
		return $this->append($row);
	}
	
	/**
	 * Builds a form row including a label and an input.
	 * 
	 * @param	mixed	$title
	 * 		The label's title. It can be a DOMElement or a string (which will be nested into a span).
	 * 
	 * @param	DOMElement	$input
	 * 		The input to be inserted to the form row.
	 * 
	 * @param	boolean	$required
	 * 		Defines whether the input given is required.
	 * 
	 * @param	string	$notes
	 * 		Notes for the user to insert valid input.
	 * 
	 * @return	DOMElement
	 * 		The row DOMElement.
	 */
	public function buildRow($title, $input, $required = FALSE, $notes = "")
	{
		// Create form row
		$row = $this->getRow();
		
		// Create label
		$label = $this->buildLabel($title, DOM::attr($input, "id"), $required);
		DOM::append($row, $label);
		
		// Append input
		if (!is_null($input))
			DOM::append($row, $input);
		
		// Set input notes
		if (!empty($notes))
		{
			$notesElement = $this->getNotes($notes);
			DOM::append($row, $notesElement);
		}
		
		return $row;
	}
	
	/**
	 * Creates and returns a form row.
	 * 
	 * @return	DOMElement
	 * 		The row DOMElement.
	 */
	public function getRow()
	{
		return DOM::create("div", "", "", "simpleFormRow");
	}
	
	/**
	 * Builds a simple form label.
	 * 
	 * @param	mixed	$title
	 * 		The label's title. It can be a string or a DOMElement.
	 * 
	 * @param	string	$for
	 * 		The id of the input that this label is pointing to.
	 * 
	 * @return	DOMElement
	 * 		The simple label, as the template builds it.
	 */
	public function getSimpleLabel($title, $for)
	{
		return parent::getLabel($title, $for, $class = "simpleFormLabel");
	}
	
	/**
	 * Adds a module POST action to the form.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$action
	 * 		The module's view name.
	 * 
	 * @return	simpleForm
	 * 		The simpleForm object.
	 */
	private function setModuleAction($moduleID, $action = "")
	{
		// Get form element
		$form = $this->get();
		
		// Set module action
		FormProtocol::engage($form, $moduleID, $action);
		
		return $this;
	}
	
	/**
	 * Builds and returns a simple form label.
	 * 
	 * @param	mixed	$title
	 * 		The label's title. It can be string or DOMElement (span).
	 * 
	 * @param	string	$for
	 * 		The input's id where this label is pointing to.
	 * 
	 * @param	boolean	$required
	 * 		Creates a required span indicator for the input.
	 * 
	 * @return	DOMElement
	 * 		The label DOMElement.
	 */
	private function buildLabel($title, $for, $required = FALSE)
	{
		// Create label
		$label = $this->getSimpleLabel($title, $for);
		
		// Set Required indicator
		if ($required)
		{
			$requiredSpan = DOM::create("span", "*", "", "required");
			DOM::append($label, $requiredSpan);
		}
		
		$colonSpan = DOM::create("span", ":");
		DOM::append($label, $colonSpan);
		
		return $label;
	}
	
	/**
	 * Builds and appends the form default controls (submit and reset buttons).
	 * 
	 * @return	void
	 */
	private function buildControls()
	{
		// Get form element
		$form = $this->get();
		
		// Create form controls
		$formControls = DOM::create("div", "", "", "formControls");
		DOM::append($form, $formControls);
		
		// Insert Controls
		$row = $this->getRow();
		DOM::append($formControls, $row);
		$submitBtn = $this->getSubmitButton(literal::get("global::dictionary", "execute"));
		DOM::append($row, $submitBtn);
		$resetBtn = $this->getResetButton(literal::get("global::dictionary", "reset"));
		DOM::append($row, $resetBtn);
	}
	
	/**
	 * Builds and returns a note container with context.
	 * 
	 * @param	string	$notes
	 * 		The user's notes for this specific input.
	 * 
	 * @return	DOMElement
	 * 		The notes DOMElement to be inserted in the form row.
	 */
	private function getNotes($notes)
	{
		if (gettype($notes) == "string")
			$notes = DOM::create("span", $notes);
		
		$notesElement = DOM::create("div", "", "", "simpleFormNotes");
		DOM::append($notesElement, $notes);
		
		return $notesElement;
	}
}
//#section_end#
?>