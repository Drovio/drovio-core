<?php
//#section#[header]
// Namespace
namespace UI\Forms\templates;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Forms
 * @namespace	\templates
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "literals::literal");
importer::import("UI", "Forms", "formFactory");
importer::import("UI", "Html", "DOM");

use \API\Resources\literals\literal;
use \UI\Forms\formFactory;
use \UI\Html\DOM;

/**
 * Simple Form
 * 
 * Simple form builder.
 * 
 * @version	{empty}
 * @created	April 18, 2013, 11:21 (EEST)
 * @revised	October 24, 2013, 12:40 (EEST)
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
		$form = parent::build($moduleID, $action, $async)->get();
		DOM::appendAttr($form, "class", "simpleForm");
		
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
	 * 		{description}
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
	 * 		{description}
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
	 * 		{description}
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
	 * 		{description}
	 */
	public function getSimpleLabel($title, $for)
	{
		return parent::getLabel($title, $for, $class = "simpleFormLabel");
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
	 * 		{description}
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
	 * 		The user's notes.
	 * 
	 * @return	DOMElement
	 * 		{description}
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