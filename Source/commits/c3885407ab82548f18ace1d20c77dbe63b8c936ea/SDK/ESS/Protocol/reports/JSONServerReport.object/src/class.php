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

use \ESS\Protocol\reports\ServerReport;

/**
 * JSON Server Report
 * 
 * Creates an asynchronous server report in JSON format according to user request.
 * 
 * @version	0.1-1
 * @created	July 29, 2014, 22:40 (EEST)
 * @revised	July 29, 2014, 22:40 (EEST)
 */
class JSONServerReport extends ServerReport
{
	/**
	 * Get the json server report.
	 * 
	 * @return	string
	 * 		The server report in json format.
	 */
	public static function get()
	{
		// Set Response Headers
		parent::setResponseHeaders();

		return self::getReport();
	}
	
	/**
	 * Adds a content report to the report stack.
	 * 
	 * @param	array	$content
	 * 		The body of the report content.
	 * 
	 * @return	void
	 */
	public static function addContent($content)
	{
		// jQuery Escape invalid characters
		$report = self::getReportContent("data", $content);
		self::$reports[] = $report;
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
	 * @return	void
	 */
	public static function addAction($type, $value = "")
	{
		// Create Action Report Content
		$actionContext = self::getActionContext($type, $value);
		
		// Build Form Report
		$report = self::getReportContent("action", $actionContext);
		
		// Append to reports
		self::$reports[] = $report;
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
		$header = array();
		foreach (self::$headers as $hc)
			$header[] = $hc;
		$report['head'] = $header;
		
		// Add report contents
		$body = array();
		foreach (self::$reports as $bc)
			$body[] = $bc;
		$report['body'] = $body;

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
	protected static function getReportContent($type = "data", $context = NULL)
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