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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Literals", "literal");
importer::import("UI", "Forms", "Form");
importer::import("UI", "Html", "DOM");

use \API\Literals\literal;
use \UI\Forms\Form;
use \UI\Html\DOM;

/**
 * Simple Form Template builder
 * 
 * Builds an html form with a specific layout, if the user wants to.
 * It has access to the FormFactory (it extends it) and can build every form control.
 * 
 * @version	3.0-6
 * @created	April 18, 2013, 11:21 (EEST)
 * @updated	January 5, 2015, 11:58 (EET)
 */
class simpleForm extends Form
{
	/**
	 * The form's control button container.
	 * 
	 * @type	DOMElement
	 */
	private $formControls;
	
	/**
	 * Whether the form has required visual input fields.
	 * 
	 * @type	boolean
	 */
	private $requiredNotes = FALSE;
	
	/**
	 * Builds the form.
	 * 
	 * @param	string	$action
	 * 		The form action string.
	 * 		For Modules and Applications leave this empty and engage form with formFactory.
	 * 
	 * @param	boolean	$defaultButtons
	 * 		Options whether the form will have the default control buttons (execute and reset buttons).
	 * 
	 * @param	boolean	$async
	 * 		Sets the form to post async.
	 * 
	 * @return	simpleForm
	 * 		The simpleForm object.
	 */
	public function build($action = "", $defaultButtons = TRUE, $async = TRUE)
	{
		// This is for backwards compatibility
		if (!empty($action) && is_numeric($action))
		{
			$moduleID = $action;
			$viewName = $defaultButtons;
			$action = "";
			
			$arguments = func_get_args();
			$defaultButtons = (is_null($arguments[2]) ? TRUE : $arguments[2]);
			$async = (is_null($arguments[3]) ? TRUE : $arguments[3]);
		}
		else if (empty($action) && is_string($defaultButtons))
		{
			$action = $defaultButtons;
			
			$arguments = func_get_args();
			$defaultButtons = (is_null($arguments[2]) ? TRUE : $arguments[2]);
			$async = (is_null($arguments[3]) ? TRUE : $arguments[3]);
		}
		
		// Create Form
		parent::build($action, $async);
		$form = parent::get();
		DOM::appendAttr($form, "class", "simpleForm");
		
		// Build form controls
		$this->buildControls($defaultButtons);
		
		
		// Set module action (backwards compatibility)
		if (!empty($moduleID))
			$this->engageModule($moduleID, $viewName);
		
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
		// Set form required notes
		if ($required)
			$this->requiredNotes = TRUE;
			
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
		// Set form required notes
		if ($required)
			$this->requiredNotes = TRUE;
			
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
	 * Get the form object.
	 * 
	 * @param	boolean	$requiredNotes
	 * 		If set to true and there are required input fields, a note will be inserted after the form body and before the controls saying what the red asterisk means.
	 * 
	 * @return	DOMElement
	 * 		The form DOMElement.
	 */
	public function get($requiredNotes = FALSE)
	{
		// Insert form notes for required fields (explanation)
		if ($this->requiredNotes && $requiredNotes)
		{
			// Create notes container
			$notesContainer = DOM::create("div", "", "", "simpleFormRequiredNotes");
			
			// Create note
			$literal = literal::get("sdk.Forms.simpleForm", "lbl_requiredNote");
			$note = DOM::create("p", $literal, "", "rqNote");
			DOM::append($notesContainer, $note);
			
			$requiredSpan = DOM::create("span", "*", "", "required");
			DOM::prepend($note, $requiredSpan);
			
			// Append before controls
			DOM::appendBefore($this->formControls->parentNode, $this->formControls, $notesContainer);
		}
		
		// Get object from parent
		return parent::get();
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
	 * Append a form control (button) to the form's control button container.
	 * 
	 * @param	DOMElement	$control
	 * 		The form control (button) to append.
	 * 
	 * @return	simpleForm
	 * 		The simpleForm object.
	 */
	public function appendControl($control)
	{
		DOM::append($this->formControls, $control);
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
	 * @param	boolean	$defaultButtons
	 * 		Options whether the form will have the default control buttons (execute and reset buttons).
	 * 
	 * @return	void
	 */
	private function buildControls($defaultButtons)
	{
		// Get form element
		$form = parent::get();
		
		// Create form controls
		$this->formControls = DOM::create("div", "", "", "formControls");
		DOM::append($form, $this->formControls);
		
		// Insert Default buttons for execute and reset
		if ($defaultButtons)
		{
			// Insert Controls
			$row = $this->getRow();
			DOM::append($this->formControls, $row);
			$submitBtn = $this->getSubmitButton(literal::dictionary("execute"));
			DOM::append($row, $submitBtn);
			$resetBtn = $this->getResetButton(literal::dictionary("reset"));
			DOM::append($row, $resetBtn);
		}
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
		return DOM::create("div", $notes, "", "simpleFormNotes");
	}
}
//#section_end#
?>