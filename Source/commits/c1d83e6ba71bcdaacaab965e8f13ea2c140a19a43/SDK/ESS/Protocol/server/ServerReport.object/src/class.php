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

importer::import("ESS", "Protocol", "server::AsCoProtocol");
importer::import("ESS", "Protocol", "server::HttpResponse");
importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("UI", "Html", "DOM");

use \ESS\Protocol\server\AsCoProtocol;
use \ESS\Protocol\server\HttpResponse;
use \ESS\Protocol\client\environment\Url;
use \UI\Html\DOM;

/**
 * Server Report Protocol
 * 
 * Creates an asynchronous server report according to user request.
 * It can be html or json.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:12 (EET)
 * @revised	November 7, 2013, 13:42 (EET)
 */
abstract class ServerReport
{
	/**
	 * Contains all the reports that will be handled separately
	 * 
	 * @type	array
	 */
	protected static $reports = array();
	/**
	 * Contains all the headers in order to prepare the ground for the reports
	 * 
	 * @type	array
	 */
	protected static $headers = array();
	
	/**
	 * Returns the server report
	 * 
	 * @return	string
	 * 		{description}
	 */
	abstract public static function get();
	
	/**
	 * Adds a content report to the report stack
	 * 
	 * @param	DOMElement	$content
	 * 		The body of the report content
	 * 
	 * @return	ServerReport
	 * 		{description}
	 */
	abstract public static function addContent($content);
	
	/**
	 * Adds an action report to the report stack
	 * 
	 * @param	string	$type
	 * 		The action type.
	 * 		This may vary according to the handler.
	 * 
	 * @param	string	$value
	 * 		The action value
	 * 
	 * @return	ServerReport
	 * 		{description}
	 */
	abstract public static function addAction($type, $value = "");
	
	/**
	 * Get the report output.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	/**
	 * Get the report output.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	abstract protected static function getReport();
	
	/**
	 * Creates a content report for the report
	 * 
	 * @param	string	$type
	 * 		The content type
	 * 
	 * @param	mixed	$context
	 * 		The content context.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	abstract protected static function getReportContent($type, $context);
	
	/**
	 * Creates an action report for the report
	 * 
	 * @param	string	$type
	 * 		The action type
	 * 
	 * @param	string	$value
	 * 		The action value
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	abstract protected static function getActionContext($type, $value);
	
	/**
	 * Adds a header to the report
	 * 
	 * @param	DOMElement	$header
	 * 		The header element to be added
	 * 
	 * @return	ServerReport
	 * 		{description}
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
	 * Returns the report stack
	 * 
	 * @return	array
	 * 		{description}
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