<?php
//#section#[header]
// Namespace
namespace ESS\Prototype;

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
 * @package	Prototype
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "ApplicationProtocol");
importer::import("ESS", "Prototype", "ReportFactory");
importer::import("AEL", "Platform", "application");

use \ESS\Protocol\ApplicationProtocol;
use \ESS\Prototype\ReportFactory;
use \AEL\Platform\application;

/**
 * Redback's Action Factory for Applications
 * 
 * This is a class for attaching all kind of actions and listeners to elements to be handled in the client side and communicate with the server.
 * 
 * @version	2.0-1
 * @created	September 4, 2014, 15:33 (EEST)
 * @updated	May 25, 2015, 15:49 (EEST)
 */
class AppActionFactory extends ReportFactory
{
	/**
	 * Creates an application async action listener to the given item.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name to load.
	 * 		If empty, get the application's default view.
	 * 		It is empty by default.
	 * 
	 * @param	string	$holder
	 * 		The holder container for the content that will be loaded.
	 * 		It is a css selector for the context.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the view through the GET method.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		It is false by default.
	 * 
	 * @return	void
	 */
	public static function setAction($item, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		// Get application id to request
		$requestApplicationID = application::init();
		if (empty($requestApplicationID))
			return FALSE;
		
		// Set application action
		ApplicationProtocol::addActionGET($item, $requestApplicationID, $viewName, $holder, $attr, $loading);
	}
	
	/**
	 * Creates an application async download action listener to the given item.
	 * 
	 * @param	integer	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name to load.
	 * 		If empty, get the application's default view.
	 * 		It is empty by default.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the view through the GET method.
	 * 
	 * @return	void
	 */
	public static function setDownloadAction($item, $viewName = "", $attr = array())
	{
		// Get application id to request
		$requestApplicationID = application::init();
		if (empty($requestApplicationID))
			return FALSE;
		
		// Set application action
		ApplicationProtocol::addActionDL($item, $requestApplicationID, $viewName, $attr);
	}
	
	/**
	 * Creates an application action listener to the given item.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @param	string	$viewName
	 * 		The application's view name to load.
	 * 		If empty, get the application's default view.
	 * 		It is empty by default.
	 * 
	 * @param	string	$holder
	 * 		The holder container for the content that will be loaded.
	 * 		For more information, see \ESS\Protocol\ApplicationProtocol.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the view.
	 * 		For more information, see \ESS\Protocol\ApplicationProtocol.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		It is false by default.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use setAction() instead.
	 */
	public static function setAppAction($item, $appID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		// Use new function
		self::setAction($item, $viewName, $holder, $attr, $loading);
	}
}
//#section_end#
?>