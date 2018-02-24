<?php
//#section#[header]
// Namespace
namespace UI\Interactive\forms;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Interactive
 * @namespace	\forms
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("ESS", "Protocol", "ApplicationProtocol");
importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("UI", "Html", "DOM");

use \AEL\Platform\application;
use \ESS\Protocol\ApplicationProtocol;
use \ESS\Protocol\ModuleProtocol;
use \UI\Html\DOM;

/**
 * Form Auto Suggest
 * 
 * Displays a popup with suggestions upon input value.
 * It allows to be connected with modules and applications.
 * 
 * @version	0.1-1
 * @created	March 11, 2013, 11:26 (EET)
 * @updated	July 27, 2015, 18:28 (EEST)
 */
class formAutoSuggest
{
	/**
	 * Adds a autosuggest controller to the given element.
	 * 
	 * @param	DOMElement	$element
	 * 		The element where the controller will be attached.
	 * 
	 * @param	string	$path
	 * 		The action script where the suggestions will come from.
	 * 
	 * @return	void
	 */
	public static function engage($element, $path)
	{
		DOM::attr($element, "data-as-path", $path);
	}
	
	/**
	 * Engage the auto suggest engine to a module.
	 * 
	 * NOTE: This doesn't work when on secure mode!
	 * 
	 * @param	DOMElement	$element
	 * 		The element where the controller will be attached.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$viewName
	 * 		The module view name to load.
	 * 		Leave empty for the module default view.
	 * 		It is empty by default.
	 * 
	 * @param	array	$attr
	 * 		An array of attributes to pass to the module view.
	 * 		It is empty array by default.
	 * 
	 * @return	void
	 */
	public static function engageModule($element, $moduleID, $viewName = "", $attr = array())
	{
		// Check if in secure mode
		if (importer::secure())
			return FALSE;
		
		// Engage module
		ModuleProtocol::addAction($element, "data-as-mdl", $moduleID, $viewName, $attr, $loading = FALSE);
	}
	
	/**
	 * Engage the auto suggest engine to the current application.
	 * 
	 * @param	DOMElement	$element
	 * 		The element where the controller will be attached.
	 * 
	 * @param	string	$viewName
	 * 		The application view name to load.
	 * 
	 * @param	array	$attr
	 * 		An array of attributes to pass to the application view.
	 * 		It is empty array by default.
	 * 
	 * @return	void
	 */
	public static function engageApp($element, $viewName = "", $attr = array())
	{
		// Validate application id
		$applicationID = application::init();
		if (empty($applicationID))
			return FALSE;
			
		// Set application action
		ApplicationProtocol::addAction($element, "data-as-app", $applicationID, $viewName, $attr, $loading = FALSE);
	}
	
	/**
	 * Get a specific report for the autosuggest control.
	 * 
	 * @param	DOMElement	$content
	 * 		The report content.
	 * 
	 * @return	string
	 * 		The report string output.
	 */
	public static function getReport($content)
	{
		// Transform DOMElement to html
		$htmlContent = DOM::DOM2HTML($content);
		
		// Set action for auto suggest
		HTMLServerReport::addAction("autosuggest.content", $htmlContent);
		
		// Return server report
		return HTMLServerReport::get();
	}
}
//#section_end#
?>