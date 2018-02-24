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
 * @version	1.1-6
 * @created	March 17, 2013, 11:16 (GMT)
 * @updated	October 22, 2015, 17:14 (BST)
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
		logger::log("Starting Drovio Engine.", logger::INFO);
		
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
		logger::log("Restarting platform engine.", logger::INFO);
		
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
		return HTTPRequest::getVar($name, $name);
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
		// If include PUT method, return TRUE if one of them is valid
		if ($includePUT)
			return self::requestMethod("POST") || self::requestMethod("PUT");
		
		// Check if the request method is POST
		return self::requestMethod("POST");
	}
	
	/**
	 * Checks if the HTTPRequest request method is the same as the variable given.
	 * 
	 * @param	string	$type
	 * 		The request method to check.
	 * 
	 * @return	boolean
	 * 		True if the variable has the same value, false otherwise.
	 */
	private static function requestMethod($type)
	{
		return (HTTPRequest::requestMethod() == strtoupper($type));
	}
}
//#section_end#
?>