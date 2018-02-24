<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

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
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Client-Server Module Handler Protocol
 * 
 * The most valuable and basic protocol for loading dynamic content.
 * It's based on AJAX content loading and defines the controllers for those interactions.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:20 (EET)
 * @revised	August 3, 2013, 13:00 (EEST)
 */
class ModuleProtocol
{
	/**
	 * Adds a custom action to a DOMElement.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the action.
	 * 
	 * @param	string	$dataName
	 * 		The name of the data attribute.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to set as action.
	 * 
	 * @param	string	$auxName
	 * 		The auxiliary name of the module (if specified - optional).
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @return	void
	 */
	public static function addAction($item, $dataName, $moduleID, $auxName = "", $attr = array())
	{
		// Set action
		$actionArray = self::getAction($moduleID, $auxName);
		DOM::data($item, $dataName, $actionArray);
		
		// Add Attributes
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Adds async handler to container. This enables the protocol to load the content asynchronously when the holder enters the DOM.
	 * 
	 * @param	DOMElement	$item
	 * 		The element to receive the handler
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$auxName
	 * 		The auxiliary name of the module (if specified - optional).
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 
	 * @param	boolean	$startup
	 * 		Whether this action will be triggered when on next content.modified.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @return	void
	 */
	public static function addAsync($item, $moduleID, $auxName = "", $holder = "", $startup = TRUE, $attr = array())
	{
		$actionArray = self::getAction($moduleID, $auxName, $holder);
		DOM::data($item, "ajx", $actionArray);
		self::addAsyncATTR($item, $attr);

		if ($startup)
			DOM::attr($item, "data-startup", $startup);
	}
	
	
	/**
	 * Inserts a module handler to a DOMElement.
	 * It interacts with the server (GET Request Method) and gets content.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the handler
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to be loaded.
	 * 
	 * @param	string	$auxName
	 * 		The auxiliary name of the module (if specified - optional).
	 * 
	 * @param	string	$holder
	 * 		The CSS selector of the object that will receive the content from the server
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @return	void
	 */
	public static function addActionGET($item, $moduleID, $auxName = "", $holder = "", $attr = array())
	{
		$actionArray = self::getAction($moduleID, $auxName, $holder);
		DOM::data($item, "gt", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts a module handler to a DOMElement.
	 * It interacts with the server (POST Request Method) and gets the answer.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the handler
	 * 
	 * @param	{type}	$moduleID
	 * 		{description}
	 * 
	 * @param	{type}	$auxName
	 * 		{description}
	 * 
	 * @param	string	$holder
	 * 		The CSS selector of the object that will receive the content from the server
	 * 
	 * @param	array	$attr
	 * 		A number of attributes to be sent to the server along with the request.
	 * 
	 * @return	void
	 * 
	 * @deprecated	This method is deprecated. For posting data you should use forms.
	 */
	public static function addActionPOST($item, $moduleID, $auxName = "", $holder = "", $attr = array())
	{
		$actionArray = self::getAction($moduleID, $auxName, $holder);
		DOM::data($item, "action", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts a number of attributes to be sent to the server along with the request.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the attributes.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @return	void
	 */
	public static function addAsyncATTR($item, $attr)
	{
		DOM::data($item, "attr", $attr);
	}
	
	/**
	 * It creates and returns an action array indicating the action that will be committed upon request.
	 * 
	 * @param	int	$id
	 * 		The module id.
	 * 
	 * @param	string	$action
	 * 		The auxiliary name of the module id given.
	 * 
	 * @param	string	$holder
	 * 		The CSS selector of the object that will receive the content from the server
	 * 
	 * @return	array
	 * 		The data array.
	 */
	public static function getAction($id, $action = "", $holder = "")
	{
		$actionArray = array();
		$actionArray['id'] = $id;
		$actionArray['action'] = $action;
		$actionArray['holder'] = $holder;
		
		return $actionArray;
	}
	
	/**
	 * Inserts directly the action array that was generated by the "getAction" method to the item.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the action
	 * 
	 * @param	array	$action
	 * 		The action array
	 * 
	 * @return	void
	 * 
	 * @deprecated	This function is deprecated. You should use an action ready function.
	 */
	public static function addActionReady($item, $action)
	{
		DOM::data($item, "action", $action);
	}
}
//#section_end#
?>