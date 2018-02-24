<?php
//#section#[header]
// Namespace
namespace UI\Presentation\frames;

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
 * @package	Presentation
 * @namespace	\frames
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Literals", "literal");
importer::import("UI", "Presentation", "frames::windowFrame");
importer::import("UI", "Forms", "templates::simpleForm");
importer::import("UI", "Html", "DOM");

use \API\Literals\literal;
use \UI\Presentation\frames\windowFrame;
use \UI\Forms\templates\simpleForm;
use \UI\Html\DOM;

/**
 * Window Dialog Frame
 * 
 * Creates a dialog frame popup to display content to the user and perform an action.
 * 
 * @version	{empty}
 * @created	May 15, 2013, 15:03 (EEST)
 * @revised	April 14, 2014, 16:23 (EEST)
 */
class dialogFrame extends windowFrame
{
	/**
	 * A formFactory object for building the form input objects.
	 * 
	 * @type	formFactory
	 */
	private $formFactory;
	
	/**
	 * The form container that contains the form inputs.
	 * 
	 * @type	DOMElement
	 */
	private $formContent;
	
	/**
	 * The form of the dialog.
	 * 
	 * @type	DOMElement
	 */
	private $form;
	
	/**
	 * Builds the frame along with the form action.
	 * 
	 * @param	string	$title
	 * 		The dialog's title.
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 
	 * @param	boolean	$background
	 * 		Defines whether the dialog popup will have a background.
	 * 
	 * @return	dialogFrame
	 * 		{description}
	 */
	public function build($title = "Dialog Frame", $moduleID = "", $action = "", $background = TRUE)
	{
		// Build frame
		$windowFrame = parent::build($title, "dialogFrame")->get();
		
		// Build Form
		$this->formFactory = new simpleForm();
		$this->form = $this->formFactory->build($moduleID, $action, FALSE)->get();
		parent::append($this->form);
		
		// Build form content
		$this->formContent = DOM::create("div", "", "", "formContents");
		$this->formFactory->append($this->formContent);
		
		// Build Controls
		$this->buildControls();
		
		// Set popup options
		$this->pp->background($background);
		
		return $this;
	}
	
	/**
	 * Get the dialog's form id.
	 * 
	 * @return	string
	 * 		The form id.
	 */
	public function getFormID()
	{
		return DOM::attr($this->form, "id");
	}
	
	/**
	 * Builds the dialog controls.
	 * 
	 * @return	void
	 */
	private function buildControls()
	{
		// Create dialog controls container
		$controlsContainer = DOM::create("div", "", "", "dialogControls");
		$this->formFactory->append($controlsContainer);
		
		// Button Container
		$btnContainer = DOM::create("div", "", "", "ctrls");
		DOM::append($controlsContainer, $btnContainer);
		
		// Insert Controls
		$submitBtn = $this->formFactory->getSubmitButton(literal::get("global::dictionary", "save"), "dlgExec");
		DOM::append($btnContainer, $submitBtn);
		$resetBtn = $this->formFactory->getResetButton(literal::get("global::dictionary", "cancel"), "dlgCancel");
		DOM::append($btnContainer, $resetBtn);
	}
	
	/**
	 * Appends a given element to the dialogFrame form container.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	dialogFrame
	 * 		{description}
	 */
	public function append($element)
	{
		DOM::append($this->formContent, $element);
		return $this;
	}
}
//#section_end#
?>