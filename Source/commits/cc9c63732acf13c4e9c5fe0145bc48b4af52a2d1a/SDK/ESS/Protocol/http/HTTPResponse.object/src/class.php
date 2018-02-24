<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\http;

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
 * @namespace	\http
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * HTTP Response Handler
 * 
 * Manages and adapts the http response according to redback rules.
 * 
 * @version	0.1-1
 * @created	November 5, 2014, 15:30 (EET)
 * @revised	November 5, 2014, 15:30 (EET)
 */
class HTTPResponse
{
	/**
	 * The text/html content-type
	 * 
	 * @type	string
	 */
	const CONTENT_TEXT_HTML = "text/html; charset=utf-8";
	/**
	 * The text/xml content-type
	 * 
	 * @type	string
	 */
	const CONTENT_TEXT_XML = "text/xml";
	/**
	 * The text/plain content-type
	 * 
	 * @type	string
	 */
	const CONTENT_TEXT_PLAIN = "text/plain";
	
	/**
	 * The text/javascript content-type
	 * 
	 * @type	string
	 */
	const CONTENT_TEXT_JS = "text/javascript";
	/**
	 * The text/css content-type
	 * 
	 * @type	string
	 */
	const CONTENT_TEXT_CSS = "text/css";
	/**
	 * The application/pdf content-type
	 * 
	 * @type	string
	 */
	const CONTENT_APP_PDF = "application/pdf";
	
	/**
	 * The application/zip content-type
	 * 
	 * @type	string
	 */
	const CONTENT_APP_ZIP = "application/zip";
	/**
	 * The application/octet-stream content-type
	 * 
	 * @type	string
	 */
	const CONTENT_APP_STREAM = "application/octet-stream";
	
	/**
	 * The application/json content-type
	 * 
	 * @type	string
	 */
	const CONTENT_APP_JSON = "application/json";
	
	/**
	 * All the response status codes with their description.
	 * 
	 * @type	array
	 */
	private static $statusCodes = array(
		100 => '100 Continue',
		101 => '101 Switching Protocols',
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported'
	);
	
	/**
	 * Initializes the response headers with the redback ones.
	 * 
	 * @param	string	$contenttype
	 * 		The page content type.
	 * 		Choose from the pre-defined class constants.
	 * 		It is CONTENT_TEXT_HTML by default.
	 * 
	 * @return	void
	 */
	public static function initialize($contenttype = self::CONTENT_TEXT_HTML)
	{
		self::setIdentity();
		
		self::setContentType($contenttype);
		
		self::setAllowOrigin();
		
		self::setAllowCredentials(TRUE);
	}
	
	/**
	 * Returns the status code description of the given code.
	 * 
	 * @param	integer	$code
	 * 		The response status code.
	 * 
	 * @return	string
	 * 		The status code description.
	 */
	public static function getStatusCode($code)
	{
		return self::$statusCodes[$code];
	}
	
	/**
	 * Defines the 'Access-Control-Allow-Origin' header.
	 * 
	 * @param	string	$allow
	 * 		The value of the header.
	 * 
	 * @return	void
	 */
	public static function setAllowOrigin($allow = "*")
	{
		header('Access-Control-Allow-Origin: '.$allow);
	}
	
	/**
	 * Defines the 'Access-Control-Allow-Credentials' header.
	 * The default server value is "*".
	 * 
	 * @param	boolean	$allow
	 * 		The value of the header.
	 * 		It is TRUE by default.
	 * 
	 * @return	void
	 */
	public static function setAllowCredentials($allow = TRUE)
	{
		header('Access-Control-Allow-Credentials: '.($allow ? "true" : "false"));
	}
	
	/**
	 * Defines the 'Content-type' header.
	 * 
	 * @param	string	$type
	 * 		The content-type.
	 * 		It can accept all the constants of this class.
	 * 		It is CONTENT_TEXT_HTML by default.
	 * 
	 * @return	void
	 */
	public static function setContentType($type = self::CONTENT_TEXT_HTML)
	{
		header('Content-type: '.$type);
	}
	
	/**
	 * Defines the 'Location' header.
	 * It can be used for redirection.
	 * 
	 * @param	string	$location
	 * 		The new location url.
	 * 
	 * @return	void
	 */
	public static function setLocation($location)
	{
		header('Location: '.$location);
	}
	
	/**
	 * Sets some headers for the Redback response identity.
	 * 
	 * @return	void
	 */
	private static function setIdentity()
	{
		// Frame Options
		//header('X-Frame-Options: deny');
		
		// XSS Protection
		header('X-XSS-Protection: 0');
		
		// Powered By
		header('X-Powered-By: Redback Services Protocol');
		
		// P3P Policy
		header('P3P:CP="Redback does not have a P3P policy."');
	}
}
//#section_end#
?>