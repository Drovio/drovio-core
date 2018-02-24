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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("UI", "Html", "HTML");
importer::import("UI", "Forms", "formFactory");

use \ESS\Protocol\reports\HTMLServerReport;
use \UI\Html\HTML;
use \UI\Forms\formFactory;

/**
 * Switch Button
 * 
 * Displays an interactive switch button.
 * 
 * @version	0.1-3
 * @created	March 28, 2013, 13:11 (EET)
 * @updated	January 26, 2015, 12:06 (EET)
 */
class switchButton extends formFactory
{
	/**
	 * Builds the switch button.
	 * 
	 * @param	boolean	$active
	 * 		Indicates whether the switch is on or off.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function build($active = FALSE)
	{
		// Create Form
		$form = parent::build("", TRUE)->get();
		HTML::addClass($form, "uiSwitchButton".($active ? " on" : ""));
		
		$switchInput = parent::getInput($type = "hidden", $name = $this->getFormID(), $value = ($active ? 1 : 0), $class = "", $autofocus = FALSE, $required = FALSE);
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
	public function setAction($moduleID, $viewName = "", $attr = array())
	{
		// Engage module
		$this->engageModule($moduleID, $viewName);
		
		// Add extra attributes as hidden values
		foreach ($attr as $attrName => $attrValue)
		{
			$input = parent::getInput($type = "hidden", $attrName, $attrValue, $class = "", $autofocus = FALSE, $required = FALSE);
			$this->append($input);
		}
		
		return $this;
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