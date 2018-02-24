<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\server\ModuleProtocol;
use \UI\Html\DOM;

/**
 * Form Submit and reset Protocol
 * 
 * This protocol is used for every form in Redback.
 * Defines how the forms will interact with the server and the modules.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 11:38 (EET)
 * @revised	November 25, 2013, 10:03 (EET)
 */
class FormProtocol
{
	/**
	 * Attach Module Protocol to form
	 * 
	 * @param	DOMElement	$form
	 * 		The form element
	 * 
	 * @param	int	$moduleID
	 * 		The module to be called upon form POST
	 * 
	 * @param	string	$action
	 * 		The auxiliary name of the given moduleID
	 * 
	 * @return	void
	 */
	public static function engage($form, $moduleID, $action = "")
	{
		// Set POST Parameters
		ModuleProtocol::addAction($form, "form-action", $moduleID, $action);
		
		// Set async form
		self::setAsync($form);
	}
	
	/**
	 * Sets the form to post async.
	 * 
	 * @param	DOMElement	$form
	 * 		The form element.
	 * 
	 * @return	void
	 */
	public static function setAsync($form)
	{
		// Add Async Parameter
		DOM::attr($form, "data-async", "async");
	}
	
	/**
	 * Sets the form to keep track if there are any changes and prevent the page from unload before posting.
	 * 
	 * @param	DOMElement	$form
	 * 		The form to prevent the unload event.
	 * 
	 * @return	void
	 */
	public static function setPreventUnload($form)
	{
		// Add before unload data
		DOM::attr($form, "data-pu", "1");
	}
	
	/**
	 * Adds a submit action to the server report.
	 * 
	 * @return	void
	 */
	public static function addSubmitAction()
	{
		// Action Attributes
		$type = "FormProtocol";
		$value = "submit";
		
		// Set Action
		HTMLServerReport::addAction($type, $value);
	}
	
	/**
	 * Adds a reset action to the server report
	 * 
	 * @return	void
	 */
	public static function addResetAction()
	{
		// Action Attributes
		$type = "FormProtocol";
		$value = "reset";
		
		// Set Action
		HTMLServerReport::addAction($type, $value);
	}
}
//#section_end#
?>