<?php
//#section#[header]
// Namespace
namespace UI\Content;

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
 * @package	Content
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::JSONServerReport");

use \ESS\Protocol\reports\JSONServerReport;

/**
 * JSON Content Object
 * 
 * Creates a json content object for async communication.
 * 
 * @version	1.0-1
 * @created	July 30, 2014, 12:55 (EEST)
 * @revised	November 19, 2014, 15:39 (EET)
 */
class JSONContent
{
	/**
	 * Adds a header to the report.
	 * 
	 * @param	array	$header
	 * 		A header array context.
	 * 
	 * @return	void
	 */
	public function addHeader($header)
	{
		JSONServerReport::addHeader($header);
	}
	
	/**
	 * Adds a report data content to the server report.
	 * 
	 * @param	array	$content
	 * 		The report content data in array form.
	 * 
	 * @return	void
	 */
	public function addReportContent($content)
	{
		JSONServerReport::addContent($content);
	}
	
	/**
	 * Adds a report action content to the server report.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 
	 * @param	string	$value
	 * 		The action value (if any, empty by default).
	 * 
	 * @return	void
	 */
	public function addReportAction($type, $value = "")
	{
		JSONServerReport::addAction($type, $value);
	}
	
	/**
	 * Returns the JSON Report for this data content.
	 * 
	 * @param	array	$content
	 * 		The report content data in array form.
	 * 
	 * @param	string	$allowOrigin
	 * 		The allow origin header value for the ServerReport response headers.
	 * 		If empty, calculate the inner allow origin of the framework (more secure).
	 * 		It is empty by default.
	 * 
	 * @param	boolean	$withCredentials
	 * 		The allow credentials header value for the ServerReport response headers.
	 * 		It is TRUE by default.
	 * 
	 * @return	string
	 * 		The JSON report string.
	 */
	public function getReport($content, $allowOrigin = "", $withCredentials = TRUE)
	{
		// Add the given array of data as content
		$this->addReportContent($content);
		
		// Return Report
		return JSONServerReport::get($allowOrigin, $withCredentials);
	}
}
//#section_end#
?>