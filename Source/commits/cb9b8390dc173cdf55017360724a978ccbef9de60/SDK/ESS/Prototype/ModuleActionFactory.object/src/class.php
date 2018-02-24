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

importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "client::PopupProtocol");
importer::import("ESS", "Prototype", "ReportFactory");

use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\client\PopupProtocol;
use \ESS\Prototype\ReportFactory;

/**
 * Redback's Action Factory for Modules
 * 
 * This is a class for attaching all kind of actions and listeners to elements to be handled in the client side and communicate with the server.
 * 
 * Only for internal use by the system.
 * Applications don't have access.
 * 
 * @version	1.0-1
 * @created	November 21, 2014, 11:05 (EET)
 * @updated	May 25, 2015, 14:31 (EEST)
 */
class ModuleActionFactory extends ReportFactory
{
	/**
	 * Creates a module action listener to the given item.
	 * This listener invokes a module GET action to the specified module and view.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If it left empty, it gets the default module's view.
	 * 		Empty by default.
	 * 
	 * @param	string	$holder
	 * 		The holder container for the content that will be loaded.
	 * 		For more information, see \ESS\Protocol\ModuleProtocol.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 
	 * @param	boolean	$loading
	 * 		If true, set the page loading indicator.
	 * 		False by default.
	 * 
	 * @return	void
	 */
	public static function setModuleAction($item, $moduleID, $viewName = "", $holder = "", $attr = array(), $loading = FALSE)
	{
		ModuleProtocol::addActionGET($item, $moduleID, $viewName, $holder, $attr, $loading);
	}
	
	/**
	 * Attaches the popup action to the given item.
	 * It prepares the client for the popup to show.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If it left empty, it gets the default module's view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Merged with setModuleAction. The popup is invoked by the content loaded.
	 */
	public static function setPopupAction($item, $moduleID, $viewName = "", $attr = array())
	{
		PopupProtocol::addAction($item, $moduleID, $viewName, $attr);
	}
	
	/**
	 * Creates a module download action listener to the given item.
	 * This listener invokes a module GET action to the specified module and view.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id to load.
	 * 
	 * @param	string	$viewName
	 * 		The module's view name.
	 * 		If it left empty, it gets the default module's view.
	 * 		Empty by default.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 
	 * @return	void
	 */
	public static function setDownloadAction($item, $moduleID, $viewName = "", $attr = array())
	{
		ModuleProtocol::addActionDL($item, $moduleID, $viewName, $attr);
	}
}
//#section_end#
?>