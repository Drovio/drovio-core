<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::ServerReport");
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Developer", "profiler::logger");
importer::import("UI", "Developer", "logger");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\ServerReport;
use \ESS\Protocol\client\BootLoader;
use \API\Developer\profiler\logger as APILogger;
use \UI\Developer\logger as UILogger;
use \UI\Html\DOM;

/**
 * HTML Server Report
 * 
 * Creates an asynchronous server report in HTML format according to user request.
 * 
 * @version	{empty}
 * @created	March 8, 2013, 13:23 (EET)
 * @revised	December 11, 2013, 12:24 (EET)
 */
class HTMLServerReport extends ServerReport
{
	/**
	 * The replace method identified.
	 * 
	 * @type	string
	 */
	const REPLACE_METHOD = "replace";
	
	/**
	 * The append method identified.
	 * 
	 * @type	string
	 */
	const APPEND_METHOD = "append";
	
	/**
	 * Returns the server report
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get()
	{
		// Set Response Headers
		parent::setResponseHeaders();

		return self::getReport();
	}
	
	/**
	 * Adds a content report to the report stack
	 * 
	 * @param	DOMElement	$content
	 * 		The body of the report content
	 * 
	 * @param	string	$type
	 * 		The content's type
	 * 
	 * @param	string	$holder
	 * 		The holder where the content will be inserted in the DOM.
	 * 		It's a CSS selector.
	 * 
	 * @param	string	$method
	 * 		Defines whether the content will replace the existing or will be appended.
	 * 
	 * @return	ServerReport
	 * 		{description}
	 */
	public static function addContent($content, $type = "data", $holder = "", $method = self::REPLACE_METHOD)
	{
		$report = self::getReportContent($type, $content, $holder, $method);
		self::$reports[] = $report;
	}
	
	/**
	 * Adds an action report to the report stack
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value.
	 * 
	 * @return	ServerReport
	 * 		{description}
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
	 * Builds the entire report with the head and the body and returns the html generated.
	 * 
	 * @return	string
	 * 		{description}
	 */
	protected static function getReport()
	{
		// External Report Container
		$report = DOM::create("report");
		DOM::append($report);
		
		// Create Report Headers
		$header = DOM::create("section", "", "head");
		DOM::append($report, $header);
		
		// Create Report Body
		$body = DOM::create("section", "", "body");
		DOM::append($report, $body);
		
		// Add BootLoader Headers
		self::setModuleResource();
		
		// Add Logger Head Content
		if (APILogger::status())
		{
			$logContainer = DOM::create("log");
			$logContent = UILogger::getLogs("l.".time(), "Log [".time()."]");
			DOM::append($logContainer, $logContent);
			parent::addHeader($logContainer);
		}
		
		// Add header contents
		foreach (parent::$headers as $header_content)
			DOM::append($header, $header_content);
			
		// Add report contents
		foreach (self::$reports as $report_content)
			DOM::append($body, $report_content);
			
		// Return HTML
		return DOM::getHTML();
	}
	
	/**
	 * Adds a header to the report indicating the module being loaded.
	 * 
	 * @return	void
	 */
	private static function setModuleResource()
	{
		$moduleRsrc = BootLoader::getModuleResource();
	
		$header = DOM::create("rsrc");
		DOM::data($header, "attr", $moduleRsrc);
		parent::addHeader($header);
	}
	
	/**
	 * Creates a report content as a DOMElement inside the report.
	 * 
	 * @param	string	$type
	 * 		The report type.
	 * 
	 * @param	DOMElement	$context
	 * 		The report context.
	 * 
	 * @param	string	$holder
	 * 		The holder where the content will be inserted in the DOM.
	 * 		It's a CSS selector.
	 * 
	 * @param	string	$method
	 * 		Defines whether the content will replace the existing or will be appended.
	 * 		Accepted Values:
	 * 		- "replace"
	 * 		- "append"
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected static function getReportContent($type = "data", $context = NULL, $holder = NULL, $method = self::REPLACE_METHOD)
	{
		// Build Report Content
		$content = DOM::create("div", "", "", "content");
		DOM::attr($content, 'data-type', $type);
		
		$attr = array();
		$attr['holder'] = $holder;
		$attr['method'] = $method;
		DOM::data($content, 'attr', $attr);
		
		// Insert Context
		if (!is_null($context))
			DOM::append($content, $context);
			
		return $content;
	}
	
	/**
	 * Returns an action report to the report stack
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value
	 * 
	 * @return	DOMElement
	 * 		{description}
	 */
	protected static function getActionContext($type, $value)
	{
		// Create context
		$action = DOM::create("action");
		
		// Add Attributes
		$attributes = array();
		$attributes['type'] = $type;
		$attributes['value'] = $value;
		DOM::data($action, 'attr', $attributes);
		
		return $action;
	}
}
//#section_end#
?>