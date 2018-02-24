<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\api;

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
 * @namespace	\api
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "reports/JSONServerReport");
importer::import("ESS", "Environment", "url");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Geoloc", "datetimer");

use \ESS\Protocol\reports\JSONServerReport;
use \ESS\Environment\url;
use \API\Geoloc\locale;
use \API\Geoloc\datetimer;

/**
 * Platform API Protocol Handler
 * 
 * This is a protocol for formatting all the API responses.
 * 
 * @version	1.0-2
 * @created	December 19, 2014, 16:46 (EET)
 * @revised	December 21, 2014, 23:42 (EET)
 */
class APIResponse
{
	/**
	 * Constructor method.
	 * Initalizes the headers.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize response and set headers
		$this->setHeaders();
	}
	
	/**
	 * Set all the API headers.
	 * 
	 * @return	void
	 */
	private function setHeaders()
	{
		// Get request protocol
		$urlInfo = url::info();
		$protocol = ($urlInfo['protocol'] == "https" ? "RAPS" : "RAP");
		
		// Set service Headers
		$this->addHeader($protocol, $key = "protocol");
		$this->addHeader($protocol."/0.5", $key = "version");
		$this->addHeader("Redback API Protocol", $key = "protocol_description");
		
		// Set default response status
		self::setStatus();
		
		// Set response Headers
		$this->addHeader(locale::get(), $key = "locale");
		//$this->addHeader(datetimer::get(), $key = "timezone");
		
		// Set timing
		$this->addHeader(time(), $key = "time");
		$this->addHeader(microtime(TRUE), $key = "time_float");
	}
	
	/**
	 * Set the response header status code.
	 * 
	 * @param	string	$code
	 * 		The response status code.
	 * 		The default value is 1.
	 * 
	 * @return	void
	 */
	public function setStatus($code = 1)
	{
		// Normalize code
		$code = (empty($code) && $code !== 0 ? 1 : $code);
		
		// Check type
		if (!is_numeric($code))
			$code = 1;
		$this->addHeader(intval($code), $key = "status");
	}
	
	/**
	 * Set a redirect header value for the client.
	 * 
	 * @param	string	$url
	 * 		The url to redirect to.
	 * 
	 * @return	void
	 */
	public function setRedirect($url)
	{
		$this->addHeader($url, $key = "redirect");
	}
	
	/**
	 * Adds a header to the response.
	 * 
	 * @param	mixed	$header
	 * 		A header context.
	 * 		It can be string, number or array.
	 * 
	 * @param	string	$key
	 * 		The header key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public function addHeader($header, $key = "")
	{
		JSONServerReport::addHeader($header, $key);
	}
	
	/**
	 * Adds a response data content to the api response.
	 * 
	 * @param	mixed	$content
	 * 		The response content data.
	 * 		It can be string, number or array.
	 * 
	 * @param	string	$key
	 * 		The header key value.
	 * 		If set, the content will be available at the given key, otherwise it will inserted in the array with a numeric key (next array key).
	 * 
	 * @return	void
	 */
	public function addContent($content, $key = "")
	{
		JSONServerReport::addContent($content, $key);
	}
	
	/**
	 * Returns the API Response in JSON format.
	 * 
	 * @param	string	$allowOrigin
	 * 		The allow origin header value for the ServerReport response headers.
	 * 		If empty, calculate the inner allow origin of the framework (more secure).
	 * 		It is wildcard ("*") by default.
	 * 
	 * @param	boolean	$withCredentials
	 * 		The allow credentials header value for the API response headers.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The JSON response string.
	 */
	public function getResponse($allowOrigin = "*", $withCredentials = FALSE)
	{
		// Return JSON Server Report
		return JSONServerReport::get($allowOrigin, $withCredentials);
	}
}
//#section_end#
?>