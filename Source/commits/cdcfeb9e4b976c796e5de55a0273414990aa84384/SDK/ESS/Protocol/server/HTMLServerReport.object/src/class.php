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
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "server::JSONServerReport");
importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "client::BootLoader");
importer::import("API", "Resources", "DOMParser");
importer::import("UI", "Html", "DOM");
importer::import("UI", "Developer", "logController");
importer::import("DEV", "Profiler", "logger");

use \ESS\Protocol\server\JSONServerReport;
use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\client\BootLoader;
use \API\Resources\DOMParser;
use \UI\Html\DOM;
use \UI\Developer\logController;
use \DEV\Profiler\logger;


/**
 * HTML Server Report
 * 
 * Creates an asynchronous server report in HTML format according to user request.
 * 
 * @version	{empty}
 * @created	March 8, 2013, 13:23 (EET)
 * @revised	December 23, 2013, 10:00 (EET)
 */
class HTMLServerReport extends JSONServerReport
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
	public static function addContent($content, $type = self::CONTENT_DATA, $holder = "", $method = self::REPLACE_METHOD)
	{
		$report = self::getReportContent($type, $content, $holder, $method);
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
		$report = array();
		
		// Add BootLoader Headers
		self::setModuleResource();
		
		// Add Logs
		if (logger::status())
		{
			$logContext = logController::getLogs("log_".time(), $head = "Log ".date("H:i:s", time()));
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
		
		// Check if ascop exists otherwise return report as it is
		if (!AsCoProtocol::exists())
			return $report;

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