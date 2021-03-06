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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ApplicationProtocol");
importer::import("ESS", "Prototype", "ReportFactory");

use \ESS\Protocol\ApplicationProtocol;
use \ESS\Prototype\ReportFactory;

/**
 * Redback's Action Factory for Applications
 * 
 * This is a class for attaching all kind of actions and listeners to elements to be handled in the client side and communicate with the server.
 * 
 * @version	0.1-1
 * @created	September 4, 2014, 15:33 (EEST)
 * @revised	September 4, 2014, 15:33 (EEST)
 */
class AppActionFactory extends ReportFactory
{
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
	 * 		The application's view name.
	 * 		If empty, get the application's default view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The holder container for the content that will be loaded.
	 * 		For more information, see \ESS\Protocol\ApplicationProtocol.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 		For more information, see \ESS\Protocol\ApplicationProtocol.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function setAppAction($item, $appID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		ApplicationProtocol::addActionGET($item, $appID, $viewName, $holder, $attr, $loading);
	}
}
//#section_end#
?>