<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");

// Import logger
importer::import("API", "Developer", "profiler::logger");
use \API\Developer\profiler\logger as loggr;
use \API\Developer\profiler\logger;
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
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Developer", "logger");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\ServerReport;
use \ESS\Protocol\client\BootLoader;
use \API\Developer\profiler\logger as APILogger;
use \API\Resources\DOMParser;
use \UI\Developer\UIlogger;
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
		// Get logs
		if (APILogger::status())
		{
			//$logContext = UIlogger::getLogs($id = "log_".time(), $head = "Log ".date("H:i:s", time()));
			//self::addContent($logContext, $type = "data", $holder = ".loggerPool", $method = self::APPEND_METHOD);
		}
		
		// External Report Container
		$report = array();
		
		// Add BootLoader Headers
		self::setModuleResource();
		
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
	 * Adds a header to the report indicating the module being loaded.
	 * 
	 * @return	void
	 */
	private static function setModuleResource()
	{
		$moduleRsrc = BootLoader::getModuleResource();
		
		$header = array();
		$header['header_type'] = "rsrc";
		foreach($moduleRsrc as $key => $value)
			$header[$key] = $value;
		
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
		$content = array();
		$content['type'] = $type;
		$content['holder'] = $holder;
		$content['method'] = $method;
		
		if (!is_null($context))
		{
			if (is_array($context))
				$content['context'] = $context;
			else
			{
				try
				{
					// Parse and get HTML
					$parser = new DOMParser();
					$context = $parser->import($context);
					$parser->append($context);
					$htmlContext = $parser->getHTML();
				}
				catch (Exception $ex)
				{
					$htmlContext = "Invalid Context Object.";
				}
				
				// Set context
				$content['context'] = $htmlContext;
			}
		}
			
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
		$action = array();
		$action['type'] = $type;
		$action['value'] = $value;
		
		return $action;
	}
}
//#section_end#
?>