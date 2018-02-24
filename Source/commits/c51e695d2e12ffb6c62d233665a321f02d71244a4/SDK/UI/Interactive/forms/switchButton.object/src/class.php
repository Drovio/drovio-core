<?php
//#section#[header]
// Namespace
namespace UI\Interactive\forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Interactive
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
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
 * @revised	October 11, 2013, 10:28 (EEST)
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
	 * @return	{empty}
	 * 		{description}
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
	 * @return	{empty}
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
	 * @param	integer	$id
	 * 		The module's id.
	 * 		(see ModuleProtocol)
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module.
	 * 		(see ModuleProtocol)
	 * 
	 * @return	{empty}
	 * 		{description}
	 */
	public function setAction($id, $action = "")
	{
		ModuleProtocol::addAction($this->get(), "sb", $id, $action);
		return $this;
	}
	
	/**
	 * Returns the switchButton status report.
	 * 
	 * @param	boolean	$status
	 * 		The return status.
	 * 
	 * @return	{empty}
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