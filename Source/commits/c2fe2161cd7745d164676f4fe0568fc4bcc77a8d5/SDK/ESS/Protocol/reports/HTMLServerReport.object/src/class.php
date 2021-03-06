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

importer::import("ESS", "Protocol", "reports::JSONServerReport");
importer::import("ESS", "Protocol", "loaders::ModuleLoader");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Html", "HTML");
importer::import("DEV", "Profiler", "ui::logViewer");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\reports\JSONServerReport;
use \ESS\Protocol\loaders\ModuleLoader;
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
 * @version	0.2-1
 * @created	July 29, 2014, 22:45 (EEST)
 * @revised	November 26, 2014, 10:49 (EET)
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
	 * The content "data" type.
	 * 
	 * @type	string
	 */
	const CONTENT_DATA = "data";
	/**
	 * The content "action" type.
	 * 
	 * @type	string
	 */
	const CONTENT_ACTION = "action";
	
	/**
	 * Returns the server report
	 * 
	 * @return	string
	 * 		The html server report.
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
	public static function addContent($content, $type = self::CONTENT_DATA, $holder = "", $method = self::REPLACE_METHOD, $key = "")
	{
		// Get report content
		$report = self::getReportContent($type, $content, $holder, $method);
		
		// Append to reports
		if (empty($key))
			self::$reports[] = $report;
		else
			self::$reports[$key] = $report;
	}
	
	/**
	 * Builds the entire report with the head and the body and returns the html generated.
	 * 
	 * @return	string
	 * 		The generated html server report.
	 */
	protected static function getReport()
	{
		// External Report Container
		$report = array();
		
		// Add Module Resource Headers
		self::setModuleResource();
		
		// Add Logs
		if (logger::status())
		{
			$logContext = logViewer::getLogs("log_".time(), $head = "Log ".date("H:i:s", time()));
			HTMLServerReport::addContent($logContext, $type = "data", "#pageHelper", HTMLServerReport::APPEND_METHOD);
		}
		
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
		$resources = ModuleLoader::getModuleResources();
		foreach ($resources as $rsrcID => $resourceData)
		{
			$header = array();
			foreach($resourceData as $key => $value)
				$header[$key] = $value;
	
			parent::addHeader($header);
		}
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
	protected static function getReportContent($type = self::CONTENT_DATA, $context = NULL, $holder = NULL, $method = self::REPLACE_METHOD)
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