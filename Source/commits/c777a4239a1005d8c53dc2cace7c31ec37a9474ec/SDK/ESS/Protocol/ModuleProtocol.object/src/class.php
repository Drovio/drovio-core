<?php
//#section#[header]
// Namespace
namespace ESS\Protocol;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Client-Server Module Handler Protocol
 * 
 * This is the most valuable and basic protocol for loading dynamic content from modules.
 * It uses the AsCoProtocol to send the requests and the HTML Server Report Handlers to receive data.
 * 
 * @version	1.0-4
 * @created	July 29, 2014, 20:10 (BST)
 * @updated	December 5, 2015, 15:14 (GMT)
 */
class ModuleProtocol
{
	/**
	 * Adds a custom action to a DOMElement.
	 * This action will invoke the given module id with the actionName given.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	string	$actionName
	 * 		The name of the data attribute for the action identifier.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to set as action.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addAction($item, $actionName, $moduleID, $viewName = "", $attr = array(), $loading = FALSE)
	{
		// Set action
		$actionArray = self::getAction($moduleID, $viewName, $loading);
		DOM::data($item, $actionName, $actionArray);
		
		// Add Attributes
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Adds async handler to container.
	 * This enables the protocol to load the content asynchronously when the object given (holder) enters the DOM.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the content. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	boolean	$startup
	 * 		Whether this action will be triggered when on next content.modified.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addAsync($item, $moduleID, $viewName = "", $holder = "", $startup = TRUE, $attr = array(), $loading = FALSE)
	{
		$actionArray = self::getAction($moduleID, $viewName, $holder, $loading);
		DOM::data($item, "mdl-ajx", $actionArray);
		self::addAsyncATTR($item, $attr);

		if ($startup)
			DOM::data($item, "startup", $startup);
	}
	
	
	/**
	 * Inserts a module handler to a DOMElement.
	 * It interacts with the server (GET Request Method) and gets content.
	 * It can be invoked by mouse click.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the content. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addActionGET($item, $moduleID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		$actionArray = self::getAction($moduleID, $viewName, $holder, $loading);
		DOM::data($item, "mdl-gt", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts a module handler to a DOMElement.
	 * It interacts with the server (GET Request Method) and downloads a file.
	 * It can be invoked by mouse click.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName =&gt; attrValue).
	 * 
	 * @return	void
	 */
	public static function addActionDL($item, $moduleID, $viewName = "", $attr = array())
	{
		$actionArray = self::getAction($moduleID, $viewName);
		DOM::data($item, "mdl-dl", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts a module handler to a DOMElement. The DOMElement must by only form.
	 * It interacts with the server (POST Request Method) and gets the answer.
	 * It can be invoked by form submit event.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the content. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName => attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 * 
	 * @deprecated	For posting data you should use forms.
	 */
	public static function addActionPOST($item, $moduleID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		$actionArray = self::getAction($moduleID, $viewName, $holder, $loading);
		DOM::data($item, "action", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts a number of attributes to be sent to the server along with the request.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the attributes.
	 * 		This must be the element that will trigger the event.
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
	 * @param	integer	$moduleID
	 * 		The module id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If empty, get the module's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the content. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	array
	 * 		The action's data array.
	 * 
	 * @deprecated	You should use an action ready function.
	 */
	public static function getAction($moduleID, $viewName = "", $holder = "", $loading = FALSE)
	{
		$actionArray = array();
		$actionArray['id'] = $moduleID;
		$actionArray['action'] = $viewName;
		$actionArray['holder'] = $holder;
		$actionArray['loading'] = $loading;
		
		return $actionArray;
	}
	
	/**
	 * Inserts directly the action array that was generated by the "getAction" method to the item.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to receive the action
	 * 
	 * @param	array	$action
	 * 		The action array.
	 * 
	 * @return	void
	 */
	public static function addActionReady($item, $action)
	{
		DOM::data($item, "action", $action);
	}
}
//#section_end#
?>