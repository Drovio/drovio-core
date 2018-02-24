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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM;

/**
 * Client-Server Application Handler Protocol
 * 
 * This protocol is responsible for loading application views and content.
 * It uses the AsCoProtocol to send the requests and the HTML Server Report Handlers to receive data.
 * 
 * @version	1.0-2
 * @created	September 4, 2014, 15:22 (EEST)
 * @updated	June 9, 2015, 12:43 (EEST)
 */
class ApplicationProtocol
{
	/**
	 * Adds a custom action to a DOMElement.
	 * This action will invoke the given application id with the viewName given.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	string	$actionName
	 * 		The name of the data attribute for the action identifier.
	 * 
	 * @param	integer	$appID
	 * 		The application id to set as action.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName -> attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addAction($item, $actionName, $appID, $viewName = "", $attr = array(), $loading = FALSE)
	{
		// Set action
		$actionArray = self::getAction($appID, $viewName, $loading);
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
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the holder. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	boolean	$startup
	 * 		Whether this action will be triggered when on next content.modified.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName -> attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addAsync($item, $appID, $viewName = "", $holder = "", $startup = TRUE, $attr = array(), $loading = FALSE)
	{
		$actionArray = self::getAction($appID, $viewName, $holder, $loading);
		DOM::data($item, "app-ajx", $actionArray);
		self::addAsyncATTR($item, $attr);

		if ($startup)
			DOM::data($item, "startup", $startup);
	}
	
	
	/**
	 * Inserts an application handler to a DOMElement.
	 * It interacts with the server (GET Request Method) and gets content.
	 * It can be invoked by mouse click.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the holder. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName -> attrValue).
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function addActionGET($item, $appID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		$actionArray = self::getAction($appID, $viewName, $holder, $loading);
		DOM::data($item, "app-gt", $actionArray);
		self::addAsyncATTR($item, $attr);
	}
	
	/**
	 * Inserts an application handler to a DOMElement.
	 * It interacts with the server (GET Request Method) and downloads content.
	 * It can be invoked by mouse click.
	 * 
	 * @param	DOMElement	$item
	 * 		The item to attach the event listener to.
	 * 
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName -> attrValue).
	 * 
	 * @return	void
	 */
	public static function addActionDL($item, $appID, $viewName = "", $attr = array())
	{
		$actionArray = self::getAction($appID, $viewName);
		DOM::data($item, "app-dl", $actionArray);
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
	 * 		An associative array as a number of attributes to be sent to the server along with the request (attrName -> attrValue).
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
	 * @param	integer	$appID
	 * 		The application id to load.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The DOM holder/receiver where the content will go.
	 * 		It's a CSS selector.
	 * 		If left empty, the content will decide the holder. If the content's holder is also empty, it goes as child of the sender.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	private static function getAction($appID, $viewName = "", $holder = "", $loading = FALSE)
	{
		$actionArray = array();
		$actionArray['id'] = $appID;
		$actionArray['view'] = $viewName;
		$actionArray['holder'] = $holder;
		$actionArray['loading'] = $loading;
		
		return $actionArray;
	}
}
//#section_end#
?>