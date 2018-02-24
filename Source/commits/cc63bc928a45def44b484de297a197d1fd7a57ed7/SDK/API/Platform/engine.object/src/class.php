<?php
//#section#[header]
// Namespace
namespace API\Platform;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Platform
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("ESS", "Protocol", "http/HTTPRequest");
importer::import("API", "Geoloc", "datetimer");
importer::import("DEV", "Profiler", "debugger");
importer::import("DEV", "Profiler", "logger");

use \ESS\Environment\session;
use \ESS\Protocol\http\HTTPRequest;
use \API\Geoloc\datetimer;
use \DEV\Profiler\debugger;
use \DEV\Profiler\logger;

/**
 * Platform Engine
 * 
 * Class responsible for starting and pausing the platform engine.
 * 
 * @version	3.0-4
 * @created	March 17, 2013, 11:16 (GMT)
 * @updated	December 17, 2015, 10:32 (GMT)
 */
class engine
{
	/**
	 * Starts debugger, session and user preferences.
	 * 
	 * @return	void
	 */
	public static function start()
	{
		logger::getInstance()->log("Starting Drovio Engine.", logger::INFO);
		
		// Set default max execution time limit
		set_time_limit(30);
		
		// Init HTTPRequest variables
		HTTPRequest::init();
		
		// Start Session
		session::init();

		// Start Debugger
		debugger::init();

		// Init datetimer
		datetimer::init();
		
		// Register shutdown function
		register_shutdown_function('\API\Platform\engine::shutdown');
	}
	
	/**
	 * Restarts the engine. Shutdown and then start again.
	 * 
	 * @return	void
	 */
	public static function restart()
	{
		logger::getInstance()->log("Restarting platform engine.", logger::INFO);
		
		// Shutdown Engine
		self::shutdown();
		
		// Start again
		self::start();
	}
	
	/**
	 * This function is called last when a php script quits.
	 * It also checks for errors that may have occurred and logs them.
	 * 
	 * @return	void
	 */
	public static function shutdown()
	{
		// Check for error and log
		$error = error_get_last();
		
		// Log error
		if ($error['type'] === E_ERROR)
			logger::log($error['message']." in ".$error['file']." on line ".$error['line'], logger::ERROR);
	}
	
	/**
	 * Get a variable from the request.
	 * It uses the HTTPRequest to get the variable.
	 * 
	 * This is based on the user request and supports GET, POST and COOKIES. It works independent and the user doesn't know (doesn't have to know) where the variable comes from.
	 * 
	 * @param	string	$name
	 * 		The variable name to get.
	 * 
	 * @return	mixed
	 * 		The variable value or NULL if the variable doesn't exist in the requested scope.
	 */
	public static function getVar($name)
	{
		// Get var from request
		return HTTPRequest::getVar($name, $name);
	}
	
	/**
	 * Get data from the HTTPRequest.
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
		return HTTPRequeset::getRequestData($jsonDecode);
	}
	
	/**
	 * Checks if it is a POST request.
	 * It serves not to check implicit with the HTTPRequest.
	 * 
	 * @param	boolean	$includePUT
	 * 		Set to include check for PUT request method.
	 * 		It is FALSE by default.
	 * 
	 * @return	boolean
	 * 		True if it is a POST (or a PUT, depending on the first parameter) request, false otherwise.
	 */
	public static function isPost($includePUT = FALSE)
	{
		// Check if the request method is POST or PUT (if included)
		return HTTPRequest::isRequestMethod("POST") || ($includePUT && self::isPut());
	}
	
	/**
	 * Checks if it is a PUT request.
	 * It serves not to check implicit with the HTTPRequest.
	 * 
	 * @return	boolean
	 * 		True if it is a PUT request, false otherwise.
	 */
	public static function isPut()
	{
		return HTTPRequest::isRequestMethod("PUT");
	}
	
	/**
	 * Checks if it is a DELETE request.
	 * It serves not to check implicit with the HTTPRequest.
	 * 
	 * @return	boolean
	 * 		True if it is a DELETE request, false otherwise.
	 */
	public static function isDelete()
	{
		return HTTPRequest::isRequestMethod("DELETE");
	}
}
//#section_end#
?>