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
importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "client::FormProtocol");
importer::import("ESS", "Protocol", "environment::Url");

use \ESS\Protocol\reports\HTMLServerReport;
use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\client\FormProtocol;
use \ESS\Protocol\environment\Url;

/**
 * Redback's Report Factory
 * 
 * This is a class for getting specific reports for page reload and redirect.
 * 
 * @version	0.1-1
 * @created	August 29, 2014, 14:03 (EEST)
 * @revised	August 29, 2014, 14:03 (EEST)
 */
class ReportFactory
{
	/**
	 * Creates a specific action report to invoke a page redirect to the client.
	 * 
	 * @param	string	$location
	 * 		The destination url to redirect.
	 * 
	 * @param	string	$domain
	 * 		The subdomain of the location.
	 * 		If left empty, the full url will be considered the first parameter, otherwise the url will be resolved to this subdomain.
	 * 		It is empty by default.
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