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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/JSONServerReport");
importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("DEV", "Profiler", "ui/logViewer");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\reports\JSONServerReport;
use \ESS\Protocol\http\HTTPResponse;
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \UI\Html\HTML;
use \DEV\Profiler\ui\logViewer;
use \DEV\Profiler\logger;

/**
 * HTML Server Report
 * 
 * Creates an asynchronous server report in HTML format according to user request.
 * 
 * @version	0.3-3
 * @created	July 29, 2014, 22:45 (EEST)
 * @updated	April 17, 2015, 15:51 (EEST)
 */
class HTMLServerReport extends JSONServerReport
{
	/**
	 * The replace method identifier.
	 * 
	 * @type	string
	 */
	const REPLACE_METHOD = "replace";
	
	/**
	 * The append method identifier.
	 * 
	 * @type	string
	 */
	const APPEND_METHOD = "append";
	
	/**
	 * The extra content "popup" type.
	 * 
	 * @type	string
	 */
	const CONTENT_POPUP = "popup";
	
	/**
	 * Returns the server report
	 * 
	 * @return	string
	 * 		The html server report.
	 */
	public static function get()
	{
		// Set Response Headers
		parent::setResponseHeaders(HTTPResponse::CONTENT_APP_JSON);

		return self::getReport();
	}
	
	/**
	 * Adds a content report to the report stack.
	 * 
	 * @param	DOMElement	$content
	 * 		The body of the report content.
	 * 
	 * @param	string	$type
	 * 		The content's type.
	 * 		See class constants.
	 * 		It is CONTENT_DATA by default.
	 * 
	 * @param	string	$holder
	 * 		The holder where the content will be inserted in the DOM.
	 * 		It's a CSS selector.
	 * 		Empty by default.
	 * 
	 * @param	string	$method
	 * 		Defines whether the content will replace the existing or will be appended.
	 * 		See class constants.
	 * 		It is REPLACE_METHOD by default.
	 * 
	 * @param	string	$key
	 * 		The content key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public static function addContent($content, $type = self::CONTENT_HTML, $holder = "", $method = self::REPLACE_METHOD, $key = "")
	{
		// Get report content
		$report = self::getReportContent($type, $content, $holder, $method);
		
		// Append to reports
		parent::addReport($report, $key);
	}
	
	/**
	 * Builds the entire report with the head and the body and returns the html generated.
	 * 
	 * @return	string
	 * 		The generated html server report.
	 */
	protected static function getReport()
	{
		// Add Logs
		if (logger::status())
		{
			$logContext = logViewer::getLogs("log_".time(), $head = "Log ".date("H:i:s", time()));
			HTMLServerReport::addContent($logContext, $type = "data", "#pageHelper", HTMLServerReport::APPEND_METHOD);
		}
		
		// Return report
		return parent::getReport();
	}
	
	/**
	 * Creates a report content as a DOMElement inside the report.
	 * 
	 * @param	string	$type
	 * 		The content's type.
	 * 		See class constants.
	 * 		It is CONTENT_DATA by default.
	 * 
	 * @param	DOMElement	$context
	 * 		The report context.
	 * 
	 * @param	string	$holder
	 * 		The holder where the content will be inserted in the DOM.
	 * 		It's a CSS selector.
	 * 		Empty by default.
	 * 
	 * @param	string	$method
	 * 		Defines whether the content will replace the existing or will be appended.
	 * 		See class constants.
	 * 		It is REPLACE_METHOD by default.
	 * 
	 * @return	array
	 * 		The report content array for the server report.
	 */
	protected static function getReportContent($type = self::CONTENT_HTML, $context = NULL, $holder = NULL, $method = self::REPLACE_METHOD)
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
}
//#section_end#
?>