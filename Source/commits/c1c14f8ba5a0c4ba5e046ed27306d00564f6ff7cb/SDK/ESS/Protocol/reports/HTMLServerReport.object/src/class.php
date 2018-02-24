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
 * @version	1.0-2
 * @created	July 29, 2014, 22:45 (EEST)
 * @updated	May 13, 2015, 19:24 (EEST)
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
	 * Parse an html server report content as generated with a HTMLServerReport class.
	 * 
	 * @param	string	$report
	 * 		The report content.
	 * 
	 * @param	string	$defaultHolder
	 * 		The default holder to append elements if no holder is given.
	 * 		It is empty by default.
	 * 
	 * @param	array	$actions
	 * 		The array where all the actions will be appended to be returned to the caller, by action key.
	 * 		It is a call by reference.
	 * 		It is empty array by default.
	 * 
	 * @param	array	$contents
	 * 		The array where all the non-html content will be appended to be returned to the caller, by content type and key.
	 * 		It is a call by reference.
	 * 		It is empty array by default.
	 * 
	 * @return	void
	 */
	public static function parseReportContent($report, $defaultHolder = "", &$actions = array(), &$contents = array())
	{
		// Get actions and contents from parent
		parent::parseReportContent($report, $actions, $reportContents);
		
		// Get all html body payload (and data for compatibility)
		$htmlContents = array();
		if (isset($reportContents[self::CONTENT_DATA]))
			$htmlContents = array_merge($htmlContents, $reportContents[self::CONTENT_DATA]);
		if (isset($reportContents[self::CONTENT_HTML]))
			$htmlContents = array_merge($htmlContents, $reportContents[self::CONTENT_HTML]);

		// Parse all body content that is html
		foreach ($htmlContents as $key => $body)
		{
			// Get method and holder
			$method = $body['method'];
			$holder = $body['holder'];
			$content = $body['content'];
			
			// If holder is empty, continue to next
			$holder = (empty($holder) ? $defaultHolder : $holder);
			if (empty($holder))
				continue;
			
			// Get holder element
			$holderElement = HTML::select($holder)->item(0);
			
			// Act according to method
			if ($method == HTMLServerReport::APPEND_METHOD)
			{
				$oldInnerHTML = DOM::innerHTML($holderElement);
				$newInnerHTML = $oldInnerHTML.$content;
				DOM::innerHTML($holderElement, $newInnerHTML);
			}
			else
				DOM::innerHTML($holderElement, $content);
		}
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
			self::addContent($logContext, $type = "data", "#pageHelper", HTMLServerReport::APPEND_METHOD);
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
	 * @param	string	$content
	 * 		The report content.
	 * 		It is NULL by default.
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
	protected static function getReportContent($type = self::CONTENT_HTML, $content = NULL, $holder = NULL, $method = self::REPLACE_METHOD)
	{
		// Create content array
		$payload = array();
		$payload['holder'] = $holder;
		$payload['method'] = $method;
		
		// If content is null, return report content as is
		if (is_null($content))
			return parent::getReportContent($type, $payload);
		
		// Create context array
		if (is_array($content))
			$payload['content'] = $content;
		else
		{
			try
			{
				// Parse and get HTML
				$parser = new DOMParser();
				$content = $parser->import($content);
				$parser->append($content);
				$htmlContent = $parser->getHTML();
			}
			catch (Exception $ex)
			{
				$htmlContent = "<h1>Invalid Context Object.</h1>";
			}
			
			// Set context
			$payload['content'] = $htmlContent;
		}

		// Generate content from parent
		return parent::getReportContent($type, $payload);
	}
}
//#section_end#
?>