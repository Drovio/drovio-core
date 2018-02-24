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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/JSONServerReport");

use \ESS\Protocol\reports\JSONServerReport;

/**
 * JSON Content Object
 * 
 * Creates a json content object for async communication.
 * 
 * @version	1.1-2
 * @created	July 30, 2014, 12:55 (EEST)
 * @updated	May 21, 2015, 13:34 (EEST)
 */
class JSONContent
{
	/**
	 * Adds a header to the report.
	 * 
	 * @param	array	$header
	 * 		A header array context.
	 * 
	 * @param	string	$key
	 * 		The header key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public function addHeader($header, $key = "")
	{
		JSONServerReport::addHeader($header, $key);
	}
	
	/**
	 * Adds a report data content to the server report.
	 * 
	 * @param	array	$content
	 * 		The report content data in array form.
	 * 
	 * @param	string	$key
	 * 		The content key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public function addReportContent($content, $key = "")
	{
		JSONServerReport::addContent($content, $key);
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
	 * @param	string	$key
	 * 		The action key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public function addReportAction($type, $value = "", $key = "")
	{
		JSONServerReport::addAction($type, $value, $key);
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
	 * @param	string	$key
	 * 		The content key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	string
	 * 		The JSON report string.
	 */
	public function getReport($content, $allowOrigin = "", $withCredentials = TRUE, $key = "")
	{
		// Add the given array of data as content
		$this->addReportContent($content, $key);
		
		// Return Report
		return JSONServerReport::get($allowOrigin, $withCredentials);
	}
}
//#section_end#
?>