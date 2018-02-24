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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::HTMLServerReport");
importer::import("ESS", "Protocol", "server::ModuleProtocol");
importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("ESS", "Protocol", "client::PopupProtocol");
importer::import("ESS", "Protocol", "client::environment::Url");

use \ESS\Protocol\server\HTMLServerReport;
use \ESS\Protocol\server\ModuleProtocol;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Protocol\client\PopupProtocol;
use \ESS\Protocol\client\environment\Url;

/**
 * Action Factory
 * 
 * This is the factory for attaching all possible actions to handlers.
 * 
 * @version	{empty}
 * @created	March 12, 2013, 14:23 (EET)
 * @revised	March 7, 2014, 12:23 (EET)
 */
class ActionFactory
{
	/**
	 * Attaches the module's GET action.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module
	 * 
	 * @param	string	$holder
	 * 		The holder container. For more information, see \ESS\Protocol\server\ModuleProtocol.
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 
	 * @return	void
	 */
	public static function setModuleAction($item, $moduleID, $action = "", $holder = "", $attr = array())
	{
		return ModuleProtocol::addActionGET($item, $moduleID, $action, $holder, $attr);
	}
	
	/**
	 * Attaches the popup action.
	 * 
	 * @param	DOMElement	$item
	 * 		The handler.
	 * 
	 * @param	integer	$moduleID
	 * 		The module's id.
	 * 
	 * @param	string	$action
	 * 		The name of the auxiliary of the module
	 * 
	 * @param	array	$attr
	 * 		A set of attributes to be passed to the module.
	 * 
	 * @return	void
	 */
	public static function setPopupAction($item, $moduleID, $action = "", $attr = array())
	{
		return PopupProtocol::addAction($item, $moduleID, $action, $attr);
	}
	
	/**
	 * Creates an action report for page redirect.
	 * 
	 * @param	string	$location
	 * 		The redirect header location.
	 * 
	 * @param	string	$domain
	 * 		The subdomain of the location (if any).
	 * 
	 * @param	boolean	$formSubmit
	 * 		Indicates whether the report will contain a submit action.
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
		
		// Set header (if mdl preloader doesn't exist) and Return Report
		if (!_MDL_PRELOADER_)
		{
			ob_clean();
			header("Location: ".$redirectLocation);
		}
		return HTMLServerReport::get();
	}
		
	/**
	 * Creates an action report for page reload.
	 * 
	 * @param	boolean	$formSubmit
	 * 		Indicates whether the report will contain a submit action.
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