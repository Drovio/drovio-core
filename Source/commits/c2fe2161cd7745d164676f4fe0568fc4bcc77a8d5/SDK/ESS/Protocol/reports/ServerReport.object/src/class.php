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

importer::import("ESS", "Protocol", "AsCoProtocol");
importer::import("ESS", "Protocol", "http/HTTPResponse");
importer::import("ESS", "Environment", "url");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\http\HTTPResponse;
use \ESS\Environment\url;
use \UI\Html\DOM;

/**
 * Server Report Protocol
 * 
 * Creates an asynchronous server report according to user request.
 * Abstract class that provides the right function handlers for forming a server report.
 * 
 * @version	1.1-1
 * @created	July 29, 2014, 22:28 (EEST)
 * @revised	November 26, 2014, 10:49 (EET)
 */
abstract class ServerReport
{
	/**
	 * Contains all the reports that will be handled separately.
	 * 
	 * @type	array
	 */
	protected static $reports = array();
	/**
	 * Contains all the headers in order to prepare the ground for the reports.
	 * 
	 * @type	array
	 */
	protected static $headers = array();
	
	/**
	 * Returns the server report.
	 * 
	 * @return	string
	 * 		The server report in the form set by the inherited class.
	 */
	abstract public static function get();
	
	/**
	 * Adds a content report to the report stack.
	 * 
	 * @param	DOMElement	$content
	 * 		The body of the report content.
	 * 
	 * @return	void
	 */
	abstract public static function addContent($content);
	
	/**
	 * Adds an action report to the report stack.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value.
	 * 		It can be empty or a context for the event.
	 * 		It is empty by default.
	 * 
	 * @return	void
	 */
	abstract public static function addAction($type, $value = "");
	
	/**
	 * Get the report output.
	 * 
	 * @return	string
	 * 		The server's report.
	 */
	abstract protected static function getReport();
	
	/**
	 * Creates a content report for the report.
	 * 
	 * @param	string	$type
	 * 		The content type
	 * 
	 * @param	mixed	$context
	 * 		The content context.
	 * 
	 * @return	mixed
	 * 		The report content the inherited classes can handle.
	 */
	abstract protected static function getReportContent($type, $context);
	
	/**
	 * Creates an action report for the report.
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 
	 * @param	string	$value
	 * 		The action value.
	 * 
	 * @return	mixed
	 * 		The report content the inherited classes can handle.
	 */
	abstract protected static function getActionContext($type, $value);
	
	/**
	 * Adds a header to the report.
	 * 
	 * @param	mixed	$header
	 * 		The header value.
	 * 		It can vary depending on the report type.
	 * 
	 * @param	string	$key
	 * 		The header key value.
	 * 		If set, the header will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public static function addHeader($header, $key = "")
	{
		if (empty($key))
			self::$headers[] = $header;
		else
			self::$headers[$key] = $header;
	}
	
	/**
	 * Clears the report stack
	 * 
	 * @return	void
	 */
	public static function clear()
	{
		self::$reports = array();
	}
	
	/**
	 * Get all the server reports so far.
	 * 
	 * @return	void
	 */
	public static function getReportStack()
	{
		return self::$reports;
	}
	
	/**
	 * Sets the HTTPResponse Headers for the report.
	 * 
	 * @param	string	$type
	 * 		The header's content type.
	 * 		See HTTPResponse class for this.
	 * 
	 * @return	void
	 */
	public function setResponseHeaders($type = HTTPResponse::CONTENT_TEXT_HTML)
	{
		// Initialize Response
		HTTPResponse::initialize();
		
		// Set Content Type
		HTTPResponse::setContentType($type);
		
		// Set Allow Origin
		$urlInfo = url::info();
		$protocol = $urlInfo['protocol'];
		$subdomain = url::getSubdomain();
		$domain = url::getDomain();
		$allowOrigin = $protocol."://".$subdomain.".".$domain;
		HTTPResponse::setAllowOrigin($allowOrigin);
		
		// Set Allow Credentials
		HTTPResponse::setAllowCredentials(TRUE);
	}
}
//#section_end#
?>