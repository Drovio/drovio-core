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
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * HTTP Request Manager
 * 
 * Manages all the information that the HTTP request transfers from the client to the server.
 * 
 * It is designer to provide data independent of the kind of the request (stateless or stateful).
 * 
 * @version	2.0-1
 * @created	November 5, 2014, 13:52 (GMT)
 * @updated	November 25, 2015, 16:12 (GMT)
 */
class HTTPRequest
{
	/**
	 * An array containing all the request variables (name and value).
	 * 
	 * @type	array
	 */
	private static $request;
	
	/**
	 * An array containing all the client cookies (name and value), for web access only.
	 * 
	 * @type	array
	 */
	private static $cookies;
	
	/**
	 * Initializes the request with all the data.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Get current url info
		self::$request = $_REQUEST;
		
		// Get cookies
		self::$cookies = $_COOKIE;
	}
	
	/**
	 * Get the request method string.
	 * 
	 * @return	string
	 * 		The request method string in uppercase.
	 */
	public static function requestMethod()
	{
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
	
	/**
	 * Checks if the current request method is the same as the given request method.
	 * 
	 * @param	string	$method
	 * 		The request method to check.
	 * 
	 * @return	boolean
	 * 		True if the same, false otherwise.
	 */
	public static function isRequestMethod($method)
	{
		return (self::requestMethod() == strtoupper($method));
	}
	
	/**
	 * Get the request headers.
	 * 
	 * @param	string	$header
	 * 		Specify a header to get the value for.
	 * 		Leave empty to get all headers.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		An array of all request headers or a specific header value if specified.
	 */
	public static function getRequestHeaders($header = "")
	{
		// Get all request headers
		$allHeaders = apache_request_headers();
		
		// Return all or one header
		if (!empty($header))
			return $allHeaders[$header];
		
		// Return all headers
		return $allHeaders;
	}
	
	/**
	 * Get a variable from the request.
	 * It can include cookies for web content.
	 * 
	 * It works independently and can get a variable from the url or from a cookie without the user knowing.
	 * 
	 * @param	string	$name
	 * 		The variable name.
	 * 
	 * @param	string	$cookieName
	 * 		The cookie name.
	 * 		If this is set, the function will search for the cookie variable if not found in the request variables.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The variable value or NULL if the variable doesn't exist in the requested scope.
	 */
	public static function getVar($name, $cookieName = "")
	{
		// Get value from url variables
		if (!empty($name))
			$value = self::$request[$name];
		
		// Check if empty and get from cookie (if declared)
		if (empty($value) && !empty($cookieName))
			$value = self::$cookies[$cookieName];
		
		// Check if empty and return null
		if (empty($value))
			return NULL;
			
		// Else return the actual value
		return $value;
	}
	
	/**
	 * Get request data.
	 * It supports url encoded data and json payload.
	 * 
	 * @param	boolean	$jsonDecode
	 * 		If the input is provided through php://input and it's json, set true to decode to array.
	 * 		It is FALSE by default.
	 * 
	 * @return	mixed
	 * 		The request data as string or array according to input.
	 */
	public static function getRequestData($jsonDecode = FALSE)
	{
		// Get request data
		$requestData = self::$request;
		if (empty($requestData))
		{
			// Get php input
			$requestData = file_get_contents('php://input');
			
			// Decode json input data
			if ($jsonDecode)
				$requestData = json_decode($requestData, TRUE);
		}
		
		// Return request data
		return $requestData;
	}
}
//#section_end#
?>