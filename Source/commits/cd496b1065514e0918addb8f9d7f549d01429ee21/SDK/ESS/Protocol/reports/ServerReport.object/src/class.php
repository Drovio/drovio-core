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
importer::import("ESS", "Protocol", "HttpResponse");
importer::import("ESS", "Protocol", "environment::Url");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\AsCoProtocol;
use \ESS\Protocol\HttpResponse;
use \ESS\Protocol\environment\Url;
use \UI\Html\DOM;

/**
 * Server Report Protocol
 * 
 * Creates an asynchronous server report according to user request.
 * Abstract class that provides the right function handlers for forming a server report.
 * 
 * @version	0.1-3
 * @created	July 29, 2014, 22:28 (EEST)
 * @revised	July 31, 2014, 18:02 (EEST)
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
	 * @param	DOMElement	$header
	 * 		The header element to be added.
	 * 
	 * @return	void
	 */
	public static function addHeader($header)
	{
		self::$headers[] = $header;
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
	 * Sets the HttpResponse Headers for the report.
	 * 
	 * @param	string	$type
	 * 		The header's content type.
	 * 		See HttpResponse class for this.
	 * 
	 * @return	void
	 */
	public function setResponseHeaders($type = HttpResponse::CONTENT_TEXT_HTML)
	{
		// Initialize Response
		HttpResponse::initialize();
		
		// Set Content Type
		HttpResponse::setContentType($type);
		
		// Set Allow Origin
		$subdomain = Url::getSubdomain();
		$domain = Url::getDomain();
		$allowOrigin = "http://".$subdomain.".".$domain;
		HttpResponse::setAllowOrigin($allowOrigin);
		
		// Set Allow Credentials
		HttpResponse::setAllowCredentials(TRUE);
	}
}
//#section_end#
?>