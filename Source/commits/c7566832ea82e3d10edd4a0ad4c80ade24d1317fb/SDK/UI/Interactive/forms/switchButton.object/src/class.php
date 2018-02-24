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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Prototype", "UIObjectPrototype");
importer::import("API", "Resources", "literals::moduleLiteral");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Protocol\server\JSONServerReport;
use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Prototype\UIObjectPrototype;
use \API\Resources\literals\moduleLiteral;
use \UI\Html\DOM;

/**
 * Switch Button
 * 
 * Displays an interactive switch button.
 * 
 * @version	{empty}
 * @created	March 28, 2013, 13:11 (EET)
 * @revised	April 7, 2014, 12:33 (EEST)
 */
class switchButton extends UIObjectPrototype
{
	/**
	 * The switch button's id
	 * 
	 * @type	string
	 */
	private $id;
	
	/**
	 * Constructor Method.
	 * Initializes variables.
	 * 
	 * @param	string	$id
	 * 		The button's id.
	 * 
	 * @return	void
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Builds the switch button.
	 * 
	 * @param	boolean	$active
	 * 		Indicates whether the switch is on or off.
	 * 
	 * @return	switchButton
	 * 		{description}
	 */
	public function build($active = FALSE)
	{
		// Create holder
		$holder = DOM::create("div", "", $this->id, "uiSwitchButton".($active ? " on" : ""));
		$this->set($holder);
		
		$switchInput = DOM::create("input");
		DOM::attr($switchInput, "type", "hidden");
		DOM::attr($switchInput, "name", $this->id);
		DOM::attr($switchInput, "value", ($active ? 1 : 0));
		DOM::append($holder, $switchInput);
		
		$switch = DOM::create("div", "", "", "switch");
		DOM::append($holder, $switch);
		
		return $this;
	}

	/**
	 * Sets the module action for the switch.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$action
	 * 		The module's view name.
	 * 
	 * @param	array	$attr
	 * 		An array of extra attributes for the request.
	 * 
	 * @return	switchButton
	 * 		The switchButton object.
	 */
	public function setAction($moduleID, $action = "", $attr = array())
	{
		ModuleProtocol::addAction($this->get(), "sb", $moduleID, $action, $attr);
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
		
		HTMLServerReport::clear();
		$action = ($status ? "on" : "off");
		HTMLServerReport::addAction("switch.".$action);
		return HTMLServerReport::get();
	}
}
//#section_end#
?>