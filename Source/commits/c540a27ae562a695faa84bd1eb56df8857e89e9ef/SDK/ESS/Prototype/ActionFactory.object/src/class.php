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

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("ESS", "Protocol", "client::PopupProtocol");
importer::import("ESS", "Protocol", "environment::Url");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\ModuleProtocol;
use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Protocol\client\PopupProtocol;
use \ESS\Protocol\environment\Url;

/**
 * Redback's Action Factory
 * 
 * This is a class for attaching all kind of actions and listeners to elements to be handled in the client side and communicate with the server.
 * 
 * @version	0.1-2
 * @created	March 12, 2013, 14:23 (EET)
 * @revised	August 19, 2014, 10:14 (EEST)
 */
class ActionFactory
{
	/**
	 * Creates a module action listener to the given item.
	 * This listener invokes a module GET action to the specified module and view.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
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
	 * Attaches the popup action.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler item to invoke the action.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
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
	 * Creates a specific action report to invoke a page redirect to the client.
	 * 
	 * @param	string	$location
	 * 		The destination url to redirect.
	 * 
	 * @param	string	$domain
	 * 		The subdomain of the location.
	 * 		If left empty, the full url will be considered the first parameter, otherwise the url will be resolved to this subdomain.
	 * 
	 * @param	boolean	$formSubmit
	 * 		Indicates whether the report will contain a submit action for forms and allow to unload the form.
	 * 
	 * @return	string
	 * 		The report content.
	 */
	public static function getReportRedirect($location, $domain = "", $formSubmit = FALSE)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Set form submit action
		if ($formSubmit)
			FormProtocol::addSubmitAction();
		
		// Set Redirect Location
		$redirectLocation = $location;
		if (!empty($domain))
			$redirectLocation = url::resolve($domain, $location);
		
		// Add this modulePage as content
		$redirectLocation = (empty($redirectLocation) ? "/" : $redirectLocation);
		HTMLServerReport::addAction("page.redirect", $redirectLocation);
		
		// Set header (if ascop doesn't exist) and Return Report
		ob_clean();
		if (!AsCoProtocol::exists())
			header("Location: ".$redirectLocation);
		return HTMLServerReport::get();
	}
		
	/**
	 * Creates a specific action report to invoke a page reload to the client.
	 * 
	 * @param	boolean	$formSubmit
	 * 		Indicates whether the report will contain a submit action for forms and allow to unload the form.
	 * 
	 * @return	string
	 * 		The report content.
	 */
	public static function getReportReload($formSubmit = FALSE)
	{
		// Clear Report
		HTMLServerReport::clear();
		
		// Set form submit action
		if ($formSubmit)
			FormProtocol::addSubmitAction();
		
		// Add this modulePage as content
		HTMLServerReport::addAction("page.reload");
		
		// Return Report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>