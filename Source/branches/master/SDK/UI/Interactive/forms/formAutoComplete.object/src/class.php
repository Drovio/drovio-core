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
importer::import("API", "Resources", "filesystem/directory");
importer::import("ESS", "Protocol", "ApplicationProtocol");
importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("UI", "Html", "DOM");

use \AEL\Platform\application;
use \API\Resources\filesystem\directory;
use \ESS\Protocol\ApplicationProtocol;
use \ESS\Protocol\ModuleProtocol;
use \UI\Html\DOM;

/**
 * Form Auto Complete
 * 
 * Auto completes other inputs based on specific element input values.
 * 
 * @version	0.1-1
 * @created	March 11, 2013, 11:19 (EET)
 * @updated	July 27, 2015, 18:33 (EEST)
 */
class formAutoComplete
{
	/**
	 * Makes an element master for auto completing other elements according to input value.
	 * 
	 * @param	DOMElement	$element
	 * 		The master element.
	 * 
	 * @param	string	$path
	 * 		The script path from which it will get the data.
	 * 
	 * @param	array	$fill
	 * 		The set of input elements which will be filled with the new values.
	 * 
	 * @param	array	$hide
	 * 		The set of input elements which will be hidden
	 * 
	 * @param	array	$populate
	 * 		The set of input elements which will be populated with new values (select etc)
	 * 
	 * @param	string	$mode
	 * 		Sets the behavior of the autocomplete elements in case any element changes its value.
	 * 		Accepted values:
	 * 		- "strict" : Autocomplete breaks on value change
	 * 		- "lenient" : Autocomplete preserves on value change
	 * 
	 * @return	void
	 */
	public static function engage($element, $path, $fill = array(), $hide = array(), $populate = array(), $mode = "strict")
	{
		$autoComplete = array();
		$autoComplete['fill'] = implode("|", $fill);
		$autoComplete['hide'] = implode("|", $hide);
		$autoComplete['populate'] = implode("|", $populate);
		$autoComplete['mode'] = $mode;
		$autoComplete['path'] = directory::normalize($path);
		
		DOM::data($element, "autocomplete", $autoComplete);
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
		ModuleProtocol::addAction($element, "data-ac-mdl", $moduleID, $viewName, $attr, $loading = FALSE);
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
		ApplicationProtocol::addAction($element, "data-ac-app", $applicationID, $viewName, $attr, $loading = FALSE);
	}
}
//#section_end#
?>