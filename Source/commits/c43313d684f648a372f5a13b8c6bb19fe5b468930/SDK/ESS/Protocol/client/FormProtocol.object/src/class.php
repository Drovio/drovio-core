<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client;

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
 * @namespace	\client
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::HTMLServerReport");
importer::import("ESS", "Protocol", "ModuleProtocol");
importer::import("ESS", "Environment", "session");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\ModuleProtocol;
use \ESS\Environment\session;
use \UI\Html\DOM;

/**
 * Form Submit and reset Protocol
 * 
 * This protocol is used for every form in Redback.
 * Defines how the forms will interact with the server and the modules.
 * 
 * @version	0.3-3
 * @created	March 7, 2013, 11:38 (EET)
 * @revised	November 7, 2014, 12:39 (EET)
 */
class FormProtocol
{
	/**
	 * Register a form for hidden-value-validation.
	 * 
	 * @param	string	$formID
	 * 		The form id.
	 * 
	 * @return	void
	 */
	public static function register($formID)
	{
		$formData = array();
		$formName = "formData_".$formID;
		session::set($formName, $formData, "FormProtocol");
	}
	
	/**
	 * Unregister a form from hidden-value-validation.
	 * 
	 * @param	string	$formID
	 * 		The form id.
	 * 
	 * @return	void
	 */
	public static function unregister($formID)
	{
		$formName = "formData_".$formID;
		session::clear($formName, "FormProtocol");
	}
	
	/**
	 * Register a hidden value.
	 * 
	 * @param	string	$formID
	 * 		The form id from the parent form of the input.
	 * 
	 * @param	string	$name
	 * 		The hidden input name.
	 * 
	 * @param	string	$value
	 * 		The hidden input value.
	 * 
	 * @return	void
	 */
	public static function registerVal($formID, $name, $value)
	{
		$formName = "formData_".$formID;
		$formData = session::get($formName, NULL, "FormProtocol");
		if (is_null($formData))
			$formData = array();
		
		$formData[$name] = $value;
		session::set($formName, $formData, "FormProtocol");
	}
	
	/**
	 * Validates the posted data with the data stored.
	 * 
	 * @param	string	$formID
	 * 		The form id.
	 * 
	 * @param	array	$post
	 * 		The posted data.
	 * 
	 * @param	boolean	$clear
	 * 		Whether to clear the form session registration or not.
	 * 		The default value is false.
	 * 
	 * @return	mixed
	 * 		True if data match, false if there are mismatches.
	 * 		In case of no data stored or if the session expires,
	 * 		it will return NULL to indicate absence of stored data.
	 */
	public static function validate($formID, $post, $clear = FALSE)
	{
		// Get form data
		$formName = "formData_".$formID;
		$formData = session::get($formName, NULL, "FormProtocol");

		// If data is null (not saved, or deleted) return NULL
		if (empty($formData))
			return NULL;
		
		// Search for matches
		foreach ($formData as $key => $value)
			if ($formData[$key] != $post[$key])
				return FALSE;
		
		// Unregister form and return TRUE
		if ($clear)
			self::unregister($formID);
		
		return TRUE;
	}
	
	/**
	 * Attach Module Protocol to form
	 * 
	 * @param	DOMElement	$form
	 * 		The form element
	 * 
	 * @param	int	$moduleID
	 * 		The module to be called upon form POST
	 * 
	 * @param	string	$viewName
	 * 		The view name of the given moduleID.
	 * 
	 * @return	void
	 * 
	 * @deprecated	This method is generally deprecated. Use formFactory::engageModule() instead.
	 */
	public static function engage($form, $moduleID, $viewName = "")
	{
		// Add module attributes
		ModuleProtocol::addAction($form, "form-action", $moduleID, $viewName);
		
		// Set async form
		self::setAsync($form);
	}
	
	/**
	 * Sets the form to post async.
	 * 
	 * @param	DOMElement	$form
	 * 		The form element.
	 * 
	 * @return	void
	 */
	public static function setAsync($form)
	{
		// Add Async Parameter
		DOM::attr($form, "data-async", "async");
	}
	
	/**
	 * Sets the form to keep track if there are any changes and prevent the page from unload before posting.
	 * 
	 * @param	DOMElement	$form
	 * 		The form to prevent the unload event.
	 * 
	 * @return	void
	 */
	public static function setPreventUnload($form)
	{
		// Add before unload data
		DOM::attr($form, "data-pu", "1");
	}
	
	/**
	 * Adds a submit action to the server report.
	 * 
	 * @return	void
	 */
	public static function addSubmitAction()
	{
		// Set Action
		HTMLServerReport::addAction("fsubmited.form");
	}
	
	/**
	 * Adds a reset action to the server report
	 * 
	 * @return	void
	 */
	public static function addResetAction()
	{
		// Set Action
		HTMLServerReport::addAction("freset.form");
	}
}
//#section_end#
?>