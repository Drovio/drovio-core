<?php
//#section#[header]
// Namespace
namespace UI\Forms\interactive;

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
 * @namespace	\interactive
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "Form");
importer::import("UI", "Forms", "formControls/switchButton");

use \ESS\Protocol\reports\HTMLServerReport;
use \UI\Html\HTML;
use \UI\Forms\Form;
use \UI\Forms\formControls\switchButton;

/**
 * Switch Button Async Form
 * 
 * Displays an interactive switch button.
 * It is an autonomous form that works separately from other forms.
 * 
 * @version	0.1-2
 * @created	November 16, 2015, 15:05 (GMT)
 * @updated	December 5, 2015, 15:20 (GMT)
 */
class switchButtonForm extends Form
{
	/**
	 * Builds the switch button.
	 * 
	 * @param	string	$action
	 * 		The form action attribute.
	 * 		Leave empty to engage with modules or apps.
	 * 
	 * @param	boolean	$active
	 * 		Indicates whether the switch is on or off.
	 * 		It is FALSE by default.
	 * 
	 * @param	string	$name
	 * 		The name of the switch for the post action.
	 * 		It is empty by default.
	 * 
	 * @param	{type}	$value
	 * 		{description}
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function build($action = "", $active = FALSE, $name = "", $value = "")
	{
		// Compatibility
		if (is_bool($action))
		{
			$active = $action;
			$action = "";
		}
		
		// Build form container
		$form = parent::build($action, TRUE)->get();
		HTML::addClass($form, "uiSwitchButtonForm");
		
		// Build switch button
		$sb = new switchButton();
		$switch = $sb->build($active, $name, $value, $class = "sbf")->get();
		$this->append($switch);
		
		// Return this object
		return $this;
	}
	
	/**
	 * Sets the module action for the switch.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 
	 * @param	array	$attr
	 * 		An array of extra attributes for the request.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function engageModule($moduleID, $viewName = "", $attr = array())
	{
		// Add extra attributes as hidden values
		foreach ($attr as $attrName => $attrValue)
		{
			$input = $this->getInput($type = "hidden", $attrName, $attrValue, $class = "", $autofocus = FALSE, $required = FALSE);
			$this->append($input);
		}
		
		// Engage module in form
		return parent::engageModule($moduleID, $viewName);
	}
	
	/**
	 * Sets the app action for the switch.
	 * 
	 * @param	string	$viewName
	 * 		The app's view name to post to.
	 * 		If empty, gets the default app view.
	 * 		It is empty by default.
	 * 
	 * @param	array	$attr
	 * 		An array of extra attributes for the request.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function engageApp($viewName = "", $attr = array())
	{
		// Add extra attributes as hidden values
		foreach ($attr as $attrName => $attrValue)
		{
			$input = $this->getInput($type = "hidden", $attrName, $attrValue, $class = "", $autofocus = FALSE, $required = FALSE);
			$this->append($input);
		}
		
		// Engage app in form
		return parent::engageApp($viewName);
	}
	
	/**
	 * Adds a report action to the stack to enable or disable a switch button.
	 * Because of the bubbling feature of events, the event will be triggered withing the switch so we don't have to identify the action.
	 * 
	 * @param	boolean	$status
	 * 		The new switch button status.
	 * 
	 * @return	void
	 */
	public static function addStatusReportAction($status = TRUE)
	{
		// Set action status
		$action = ($status ? "on" : "off");
		HTMLServerReport::addAction("switch.".$action);
	}
	
	/**
	 * Adds a report action to enable or disable the switch button and returns the report.
	 * 
	 * @param	boolean	$status
	 * 		The new switch button status.
	 * 
	 * @return	string
	 * 		Returns html or json report.
	 */
	public static function getReport($status = TRUE)
	{
		// Set action status
		self::addStatusReportAction($status);
		
		// Return server report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>