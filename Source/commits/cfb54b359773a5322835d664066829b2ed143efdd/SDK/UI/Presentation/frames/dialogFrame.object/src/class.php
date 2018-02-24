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
 * @version	3.0-1
 * @created	May 15, 2013, 15:03 (EEST)
 * @revised	December 18, 2014, 15:13 (EET)
 */
class dialogFrame extends windowFrame
{
	/**
	 * OK/Cancel dialog buttons.
	 * 
	 * @type	string
	 */
	const TYPE_OK_CANCEL = "1";
	/**
	 * Yes/No dialog buttons.
	 * 
	 * @type	string
	 */
	const TYPE_YES_NO = "2";
	
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
	 * Builds the frame along with the form action.
	 * 
	 * @param	mixed	$title
	 * 		The dialog's title.
	 * 
	 * @param	string	$action
	 * 		The form action to post the dialog to.
	 * 		Leave empty in order to engage with module or application protocol.
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$background
	 * 		Defines whether the dialog popup will have a background.
	 * 		It is TRUE by default, as a dialog.
	 * 
	 * @param	string	$type
	 * 		The dialog buttons type.
	 * 		Use class constants to define an OK/Cancel or Yes/No type.
	 * 		Default type is OK/Cancel.
	 * 
	 * @return	dialogFrame
	 * 		The dialogFrame object.
	 */
	public function build($title = "Dialog Frame", $action = "", $background = TRUE, $type = self::TYPE_OK_CANCEL)
	{
		// Build frame
		$windowFrame = parent::build($title, "dialogFrame")->get();
		
		// This is for backwards compatibility
		if (!empty($action) && is_numeric($action))
		{
			$moduleID = $action;
			$viewName = $background;
			$action = "";
			
			$arguments = func_get_args();
			$background = (is_null($arguments[3]) ? TRUE : $arguments[3]);
			$type = (is_null($arguments[4]) ? self::TYPE_OK_CANCEL : $arguments[4]);
		}
		else if (empty($action) && is_string($background))
		{
			$action = $background;
			
			$arguments = func_get_args();
			$background = (is_null($arguments[3]) ? TRUE : $arguments[3]);
			$type = (is_null($arguments[4]) ? self::TYPE_OK_CANCEL : $arguments[4]);
		}
		
		// Build Form
		$this->formFactory = new simpleForm();
		$this->formFactory->build($action, FALSE);
		
		// Set module action (backwards compatibility)
		if (!empty($moduleID))
			$this->engageModule($moduleID, $viewName);
			
		// Append form to frame
		parent::append($this->formFactory->get());
		
		// Build form content
		$this->formContent = DOM::create("div", "", "", "formContents");
		$this->formFactory->append($this->formContent);
		
		// Build Controls
		$this->buildControls($type);
		
		// Set popup options
		$this->pp->background($background);
		
		return $this;
	}
	
	/**
	 * Get the frame's form factory object.
	 * 
	 * @return	formFactory
	 * 		The form factory object.
	 */
	public function getFormFactory()
	{
		return $this->formFactory;
	}
	
	/**
	 * Get the dialog's form id.
	 * 
	 * @return	string
	 * 		The form id.
	 */
	public function getFormID()
	{
		$thisForm = $this->formFactory->get();
		return DOM::attr($thisForm, "id");
	}
	
	/**
	 * Engage this frame to post to a given module view.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to post to.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name to post to.
	 * 		If empty, gets the default module view.
	 * 		It is empty by default.
	 * 
	 * @return	dialogFrame
	 * 		The dialogFrame object.
	 */
	public function engageModule($moduleID, $viewName = "")
	{
		// Engage dialog form to module
		$this->formFactory->engageModule($moduleID, $viewName);
		
		return $this;
	}
	
	/**
	 * Engage this dialog frame to post to a given view of the current application.
	 * 
	 * @param	string	$viewName
	 * 		The app's view name to post to.
	 * 		If empty, gets the default app view.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	public function engageApp($viewName = "")
	{
		// Normalize arguments (compatibility)
		$argNum = func_num_args();
		$args = func_get_args();
		if ($argNum >= 2)
			$viewName = $args[1];
		else if ($argNum == 1)
			if (is_numeric($args[0]))
				$viewName = "";
				
		// Engage dialog form to app
		$this->formFactory->engageApp($viewName);
		
		return $this;
	}
	
	/**
	 * Builds the dialog controls.
	 * 
	 * @param	string	$type
	 * 		The dialog buttons type.
	 * 		Use class constants to define an OK/Cancel or Yes/No type.
	 * 		Default type is OK/Cancel.
	 * 
	 * @return	void
	 */
	private function buildControls($type = self::TYPE_OK_CANCEL)
	{
		// Create dialog controls container
		$controlsContainer = DOM::create("div", "", "", "dialogControls");
		$this->formFactory->append($controlsContainer);
		
		// Button Container
		$btnContainer = DOM::create("div", "", "", "ctrls");
		DOM::append($controlsContainer, $btnContainer);
		
		// Set button literals
		$lbl_submit = literal::dictionary($type == self::TYPE_OK_CANCEL ? "ok" : "yes");
		$lbl_reset = literal::dictionary($type == self::TYPE_OK_CANCEL ? "cancel" : "no");
		
		// Insert Controls
		$submitBtn = $this->formFactory->getSubmitButton($lbl_submit, "dlgExec");
		DOM::append($btnContainer, $submitBtn);
		$resetBtn = $this->formFactory->getResetButton($lbl_reset, "dlgCancel");
		DOM::append($btnContainer, $resetBtn);
	}
	
	/**
	 * Appends a given element to the dialogFrame form container.
	 * 
	 * @param	DOMElement	$element
	 * 		The element to be appended.
	 * 
	 * @return	dialogFrame
	 * 		The dialogFrame object.
	 */
	public function append($element)
	{
		DOM::append($this->formContent, $element);
		return $this;
	}
}
//#section_end#
?>