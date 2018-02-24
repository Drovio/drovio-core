<?php
//#section#[header]
// Namespace
namespace UI\Interactive\forms;

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
 * @package	Interactive
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/HTMLServerReport");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "Form");

use \ESS\Protocol\reports\HTMLServerReport;
use \UI\Html\HTML;
use \UI\Forms\Form;

/**
 * Switch Button
 * 
 * Displays an interactive switch button.
 * It is an autonomous form that works separately from other forms.
 * 
 * @version	2.0-1
 * @created	March 28, 2013, 13:11 (EET)
 * @updated	July 27, 2015, 11:54 (EEST)
 */
class switchButton extends Form
{
	/**
	 * Builds the switch button.
	 * 
	 * @param	string	$action
	 * 		The form action attribute.
	 * 		Leave empty to engage with modules.
	 * 
	 * @param	boolean	$active
	 * 		Indicates whether the switch is on or off.
	 * 
	 * @param	string	$name
	 * 		The name of the switch for the post action.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function build($action = "", $active = FALSE, $name = "")
	{
		// Compatibility
		if (is_bool($action))
		{
			$active = $action;
			$action = "";
		}
		
		// Build form container
		$form = parent::build($action, TRUE)->get();
		HTML::addClass($form, "uiSwitchButton");
		if ($active)
			HTML::addClass($form, "on");
		
		$inputName = (empty($name) ? "swt".mt_rand() : $name);
		$switchInput = $this->getInput($type = "text", $name = $inputName, $value = ($active ? 1 : 0), $class = "swt_val", $autofocus = FALSE, $required = FALSE);
		$this->append($switchInput);
		
		$switch = HTML::create("div", "", "", "switch");
		$this->append($switch);
		
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
	 * 
	 * @deprecated	Use engageModule() instead.
	 */
	public function setAction($moduleID, $viewName = "", $attr = array())
	{
		return $this->engageModule($moduleID, $viewName, $attr);
	}
	
	/**
	 * Returns the switchButton status report.
	 * 
	 * @param	boolean	$status
	 * 		The return status.
	 * 
	 * @return	string
	 * 		Returns html or json report.
	 */
	public static function getReport($status = TRUE)
	{
		// Set action status
		$action = ($status ? "on" : "off");
		HTMLServerReport::addAction("switch.".$action);
		
		// Return server report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>