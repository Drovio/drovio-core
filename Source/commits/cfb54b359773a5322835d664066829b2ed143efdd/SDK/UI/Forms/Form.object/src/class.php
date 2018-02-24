<?php
//#section#[header]
// Namespace
namespace UI\Forms;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("ESS", "Protocol", "client/FormProtocol");
importer::import("ESS", "Prototype", "html/FormPrototype");
importer::import("UI", "Forms", "formControls/formItem");
importer::import("UI", "Forms", "formControls/formInput");
importer::import("UI", "Forms", "formControls/formButton");
importer::import("UI", "Forms", "formControls/formLabel");
importer::import("UI", "Html", "DOM");
importer::import("AEL", "Platform", "application");
importer::import("DEV", "Apps", "test/appTester");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Prototype\html\FormPrototype;
use \UI\Forms\formControls\formItem;
use \UI\Forms\formControls\formInput;
use \UI\Forms\formControls\formButton;
use \UI\Forms\formControls\formLabel;
use \UI\Html\DOM;
use \AEL\Platform\application;
use \DEV\Apps\test\appTester;
use \DEV\Profiler\logger;

/**
 * Form object
 * 
 * Builds a basic form based on the FormPrototype and provides a "factory" for building all the necessary form items.
 * 
 * It also implements the FormProtocol.
 * 
 * @version	1.0-1
 * @created	November 14, 2014, 16:50 (EET)
 * @revised	December 18, 2014, 15:11 (EET)
 */
class Form extends FormPrototype
{
	/**
	 * Builds the form element.
	 * 
	 * @param	string	$action
	 * 		The form action url string.
	 * 
	 * @param	string	$async
	 * 		Sets the async attribute for simple forms.
	 * 
	 * @return	Form
	 * 		The Form object.
	 */
	public function build($action = "", $async = TRUE)
	{
		// Create simple action form
		parent::build($action);
		if ($async)
			parent::setAsync();
		
		// Insert hidden form id
		$hiddenFormID = $this->getInput($type = "hidden", $name = "__fid", $value = $this->getFormID());
		$this->append($hiddenFormID);
		
		return $this;
	}
	
	/**
	 * Gets the form ID from the form that performed the post action.
	 * 
	 * @return	string
	 * 		The form id.
	 */
	public static function getPostedFormID()
	{
		return $_POST['__fid'];
	}
	
	/**
	 * Validate a form with the FormProtocol to see if no hidden value is modified.
	 * 
	 * @param	boolean	$clear
	 * 		Whether to clear the form registration from the session or not.
	 * 		It is false by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate($clear = FALSE)
	{
		return FormProtocol::validate(self::getPostedFormID(), $_POST, $clear);
	}
	
	/**
	 * Get HTML Server Report content by the form protocol.
	 * 
	 * @param	DOMElement	$content
	 * 		The report content.
	 * 
	 * @param	boolean	$reset
	 * 		Indicator whether to reset the submitted form.
	 * 
	 * @return	string
	 * 		The server report output.
	 */
	public static function getSubmitContent($content, $reset = FALSE)
	{
		// Set HTML Server Report
		HTMLServerReport::clear();
		
		// Submit Action
		FormProtocol::addSubmitAction();
		
		// Reset Action
		if ($reset)
			FormProtocol::addResetAction();
		
		// Return Content
		HTMLServerReport::addContent($content, "data", ".formReport");
		return HTMLServerReport::get();
	}
	
	/**
	 * Builds and returns an input item.
	 * 
	 * @param	string	$type
	 * 		The input's type.
	 * 		This must abide by the rules of the possible input types.
	 * 
	 * @param	string	$name
	 * 		The input's name.
	 * 
	 * @param	string	$value
	 * 		The input's default value.
	 * 
	 * @param	string	$class
	 * 		The extra class for the input.
	 * 
	 * @param	boolean	$autofocus
	 * 		Inserts the autofocus attribute to the input.
	 * 
	 * @param	boolean	$required
	 * 		Indicates this input as required.
	 * 
	 * @return	DOMElement
	 * 		The input element.
	 */
	public function getInput($type = "text", $name = "", $value = "", $class = "", $autofocus = FALSE, $required = FALSE)
	{
		$fi = new formInput();
		$input = $fi->build($type, $name, $this->getInputID($name), $value, $required)->get();
		DOM::appendAttr($input, "class", $class);
		
		DOM::attr($input, 'autofocus', $autofocus);
		
		// Register hidden value
		if ($type == "hidden")
			parent::setHiddenValue($name, $value);
		
		return $input;
	}
	
	/**
	 * Builds and returns a form label.
	 * 
	 * @param	string	$text
	 * 		The label's text.
	 * 
	 * @param	string	$for
	 * 		The input's id where this label is pointing to.
	 * 
	 * @param	string	$class
	 * 		The extra class for the label.
	 * 
	 * @return	DOMElement
	 * 		The label element.
	 */
	public function getLabel($text, $for = "", $class = "")
	{
		$fl = new formLabel();
		$label = $fl->build($text, $for)->get();
		DOM::appendAttr($label, "class", $class);
		
		return $label;
	}
	
	/**
	 * Builds and returns a button (as an input).
	 * 
	 * @param	string	$title
	 * 		The button's title.
	 * 
	 * @param	string	$name
	 * 		The button's name.
	 * 
	 * @param	string	$class
	 * 		The extra class of the button.
	 * 
	 * @return	DOMElement
	 * 		The button element.
	 */
	public function getButton($title, $name = "", $class = "")
	{
		$fb = new formButton();
		$button = $fb->build($title, "button", $this->getInputID($name), $name)->get();
		DOM::appendAttr($button, "class", $class);
		
		return $button;
	}
	
	/**
	 * Builds and returns a specific submit button.
	 * 
	 * @param	string	$title
	 * 		The button's title.
	 * 
	 * @param	string	$id
	 * 		The button's id.
	 * 
	 * @param	string	$name
	 * 		The button's name.
	 * 
	 * @return	DOMElement
	 * 		The button element.
	 */
	public function getSubmitButton($title, $id = "", $name = "")
	{
		$fb = new formButton();
		return $fb->build($title, "submit", $id, $name, TRUE)->get();
	}
	
	/**
	 * Builds and returns a specific reset button.
	 * 
	 * @param	string	$title
	 * 		The button's title.
	 * 
	 * @param	string	$id
	 * 		The button's id.
	 * 
	 * @return	DOMElement
	 * 		The button element.
	 */
	public function getResetButton($title, $id = "")
	{
		$fb = new formButton();
		return $fb->build($title, "reset", $id)->get();
	}
	
	/**
	 * Builds and returns a form textarea.
	 * 
	 * @param	string	$name
	 * 		The textarea's name.
	 * 
	 * @param	string	$value
	 * 		The textarea's default value.
	 * 
	 * @param	string	$class
	 * 		The extra class for the textarea.
	 * 
	 * @param	boolean	$autofocus
	 * 		Inserts the autofocus attribute to the input.
	 * 
	 * @return	DOMElement
	 * 		The textarea element.
	 */
	public function getTextarea($name = "", $value = "", $class = "", $autofocus = FALSE)
	{
		// Create Item
		$fi = new formItem();
		$textarea = $fi->build("textarea", $name, $id, $value)->get();
		DOM::appendAttr($textarea, "class", $class);
		DOM::nodeValue($textarea, $value);
		
		if ($autofocus)
			DOM::attr($input, 'autofocus', "autofocus");
		
		return $textarea;
	}
	
	/**
	 * Builds and returns an HTML5 fieldset.
	 * 
	 * @param	string	$title
	 * 		The fieldset's legend title.
	 * 
	 * @param	string	$name
	 * 		The fieldset's name.
	 * 
	 * @param	string	$class
	 * 		The extra class for the fieldset.
	 * 
	 * @return	DOMElement
	 * 		The fieldset element.
	 */
	public function getFieldset($title, $name = "", $class = "")
	{
		// Create Item
		$fi = new formItem();
		$fieldset = $fi->build("fieldset", $name, $this->getInputID($name))->get();
		DOM::appendAttr($fieldset, "class", $class);
		
		$legend = DOM::create("legend", $title);
		DOM::append($fieldset, $legend);
		
		return $fieldset;
	}
	
	/**
	 * Builds and returns an option select input.
	 * 
	 * @param	string	$name
	 * 		The select's name.
	 * 
	 * @param	boolean	$multiple
	 * 		Option for multiple selection.
	 * 
	 * @param	string	$class
	 * 		The extra class for the select item.
	 * 
	 * @param	array	$options
	 * 		An array of option elements as select contents.
	 * 
	 * @return	DOMElement
	 * 		The select element.
	 */
	public function getSelect($name = "", $multiple = FALSE, $class = "", $options = array())
	{
		// Create Item
		$fi = new formItem();
		$select = $fi->build("select", $name, $this->getInputID($name), "", $class)->get();
		
		if ($multiple)
			DOM::attr($select, "multiple", "multiple");
		
		// Insert options if any
		foreach ($options as $option)
			DOM::append($select, $option);
		
		return $select;
	}
	
	/**
	 * Builds and returns a select input with a given resource.
	 * 
	 * @param	string	$name
	 * 		The select's name.
	 * 
	 * @param	boolean	$multiple
	 * 		Option for multiple selection.
	 * 
	 * @param	string	$class
	 * 		The extra class for the select item.
	 * 
	 * @param	array	$resource
	 * 		The select's resource as an associative array with key the value of the option and value the title.
	 * 
	 * @param	string	$selectedValue
	 * 		The selected value of the given resource.
	 * 
	 * @return	DOMElement
	 * 		The select element.
	 */
	public function getResourceSelect($name = "", $multiple = FALSE, $class = "", $resource = array(), $selectedValue = "")
	{
		// Get select element
		$select = $this->getSelect($name, $multiple, $class);
		
		// Set resource context
		foreach ($resource as $value => $title)
		{
			$option = $this->getOption($title, $value, $value == $selectedValue);
			DOM::append($select, $option);
		}
		
		return $select;
	}
	
	/**
	 * Builds and returns a select option item.
	 * 
	 * @param	string	$title
	 * 		The option's title.
	 * 
	 * @param	string	$value
	 * 		The option's value.
	 * 
	 * @param	boolean	$selected
	 * 		Specifies a selected option in the select item.
	 * 
	 * @param	string	$id
	 * 		The option element id.
	 * 
	 * @param	string	$class
	 * 		The option element class.
	 * 
	 * @return	DOMElement
	 * 		The option element.
	 */
	public function getOption($title, $value = "", $selected = FALSE, $id = "", $class = "")
	{
		$fi = new formItem();
		$option = $fi->build("option", "", $id, $value, $class)->get();
		
		// Option's title
		$titleElement = $title;
		if (gettype($titleElement) == "string")
			$titleElement = DOM::create("span", $title);
		DOM::append($option, $titleElement);
		
		if ($selected)
			DOM::attr($option, "selected", "selected");
		
		return $option;
	}
	
	/**
	 * Builds and returns a select option group.
	 * 
	 * @param	string	$label
	 * 		The group's lavel.
	 * 
	 * @param	array	$options
	 * 		An array of option elements for the group.
	 * 
	 * @return	DOMElement
	 * 		The option group element.
	 */
	public function getOptionGroup($label, $options = array())
	{
		$fi = new formItem();
		$optgroup = $fi->build("optgroup")->get();
		
		// Attributes
		DOM::attr($optgroup, "label", $label);
		
		// Insert options if any
		foreach ($options as $option)
			DOM::append($optgroup, $option);
		
		return $optgroup;
	}
	
	/**
	 * Engage this form to post to a given module view.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to post to.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name to post to.
	 * 		If empty, gets the default module view.
	 * 		It is empty by default.
	 * 
	 * @return	Form
	 * 		The Form object.
	 */
	public function engageModule($moduleID, $viewName = "")
	{
		// Add form action for modules
		DOM::attr($this->get(), "action", "/ajax/modules/load.php");
		
		// Add hidden for modules
		$input = $this->getInput($type = "hidden", $name = "__MID", $value = $moduleID, $class = "", $autofocus = FALSE, $required = FALSE);
		$this->append($input);
		
		if (!empty($viewName))
		{
			$input = $this->getInput($type = "hidden", $name = "__MVN", $value = $viewName, $class = "", $autofocus = FALSE, $required = FALSE);
			$this->append($input);
		}
		
		// Set async form
		FormProtocol::setAsync($this->get());
		
		return $this;
	}
	
	/**
	 * Engage this form to post to a given view of the current application.
	 * 
	 * @param	string	$viewName
	 * 		The app's view name to post to.
	 * 		If empty, gets the default app view.
	 * 		It is empty by default.
	 * 
	 * @return	Form
	 * 		The Form object.
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
				
		// Add form action for apps
		if (appTester::publisherLock())
			DOM::attr($this->get(), "action", "/ajax/apps/load.php");
		else
			DOM::attr($this->get(), "action", "/ajax/apps/tester.php");
		
		// Get current application id
		$applicationID = application::init();
		if (empty($applicationID))
		{
			logger::log("No application is running to engage the form #".$this->getFormID().".", logger::ERROR);
			return $this;
		}
		
		// Add hidden inputs for action
		$input = $this->getInput($type = "hidden", $name = "__AID", $value = $applicationID, $class = "", $autofocus = FALSE, $required = FALSE);
		$this->append($input);
		
		if (!empty($viewName))
		{
			$input = $this->getInput($type = "hidden", $name = "__AVN", $value = $viewName, $class = "", $autofocus = FALSE, $required = FALSE);
			$this->append($input);
		}
		
		// Set async form
		FormProtocol::setAsync($this->get());
		
		return $this;
	}
	
	/**
	 * Create a system specific form input id.
	 * 
	 * @param	string	$name
	 * 		The input's name.
	 * 
	 * @return	string
	 * 		The input id
	 */
	protected function getInputID($name)
	{
		return (empty($name) ? "" : "i".$this->getFormID()."_".$name.mt_rand());
	}
}
//#section_end#
?>