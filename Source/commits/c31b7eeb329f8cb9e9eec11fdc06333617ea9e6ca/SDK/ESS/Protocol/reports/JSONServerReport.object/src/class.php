<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\reports;

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
 * @namespace	\reports
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports::ServerReport");
importer::import("ESS", "Protocol", "http/HTTPResponse");

use \ESS\Protocol\reports\ServerReport;
use \ESS\Protocol\http\HTTPResponse;

/**
 * JSON Server Report
 * 
 * Creates an asynchronous server report in JSON format according to user request.
 * 
 * @version	0.5-3
 * @created	July 29, 2014, 22:40 (EEST)
 * @revised	December 15, 2014, 12:43 (EET)
 */
class JSONServerReport extends ServerReport
{
	/**
	 * DEPRECATED. The content "data" type.
	 * 
	 * @type	string
	 */
	const CONTENT_DATA = "data";
	
	/**
	 * The content "json" type.
	 * 
	 * @type	string
	 */
	const CONTENT_JSON = "json";
	
	/**
	 * The content "html" type.
	 * 
	 * @type	string
	 */
	const CONTENT_HTML = "html";
	
	/**
	 * The content "xml" type.
	 * 
	 * @type	string
	 */
	const CONTENT_XML = "xml";
	
	/**
	 * The content "action" type.
	 * 
	 * @type	string
	 */
	const CONTENT_ACTION = "action";
	
	/**
	 * Get the json server report.
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
	 * 		The server report in json format.
	 */
	public static function get($allowOrigin = "", $withCredentials = TRUE)
	{
		// Set Response Headers
		parent::setResponseHeaders(HTTPResponse::CONTENT_APP_JSON);
		
		// Set allow origin
		if (!empty($allowOrigin))
			HTTPResponse::setAllowOrigin($allowOrigin);
		
		// Set Allow Credentials
		HTTPResponse::setAllowCredentials($withCredentials);

		return self::getReport();
	}
	
	/**
	 * Adds a content report to the report stack.
	 * 
	 * @param	array	$content
	 * 		The body of the report content.
	 * 
	 * @param	string	$key
	 * 		The content key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @param	string	$type
	 * 		The content's type.
	 * 		See class constants.
	 * 		It is CONTENT_JSON by default.
	 * 
	 * @return	void
	 */
	public static function addContent($content, $key = "", $type = self::CONTENT_JSON)
	{
		// Get report content
		$report = self::getReportContent($type, $content);
		
		// Append to reports
		if (empty($key))
			self::$reports[] = $report;
		else
			self::$reports[$key] = $report;
	}
	
	/**
	 * Adds an action report to the report stack.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value
	 * 
	 * @param	string	$key
	 * 		The action key value.
	 * 		If set, the action will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public static function addAction($type, $value = "", $key = "")
	{
		// Create Action Report Content
		$actionContext = self::getActionContext($type, $value);
		
		// Build Form Report
		$report = self::getReportContent(self::CONTENT_ACTION, $actionContext);
		
		// Append to reports
		if (empty($key))
			self::$reports[] = $report;
		else
			self::$reports[$key] = $report;
	}
	
	/**
	 * Builds the entire report with the head and the body and returns the json string generated.
	 * 
	 * @return	string
	 * 		The json report generated.
	 */
	protected static function getReport()
	{
		// External Report Container
		$report = array();
		
		// Add header contents
		$report['head'] = self::$headers;
		
		// Add report contents
		$report['body'] = self::$reports;

		// Return HTML
		return json_encode($report, JSON_FORCE_OBJECT);
	}
	
	/**
	 * Creates a report content as an array inside the report.
	 * 
	 * @param	string	$type
	 * 		The report type.
	 * 
	 * @param	array	$context
	 * 		The report context.
	 * 
	 * @return	array
	 * 		The report content array.
	 */
	protected static function getReportContent($type = self::CONTENT_JSON, $context = NULL)
	{
		// Build Report Content
		$content = array();
		$content['type'] = $type;
		
		// Check Context Type
		if (is_array($context))
			$content['context'] = $context;
		
		// Return JSON Ready array
		return $content;
	}
	
	/**
	 * Builds a JSON action report.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value.
	 * 
	 * @return	array
	 * 		The action array context.
	 */
	protected static function getActionContext($type, $value)
	{
		// Create context
		$action = array();
		$action['type'] = $type;
		$action['value'] = $value;
		
		return $action;
	}
}
//#section_end#
?>