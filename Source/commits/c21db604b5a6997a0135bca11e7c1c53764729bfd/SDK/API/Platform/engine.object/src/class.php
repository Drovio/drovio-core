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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @version	1.0-3
 * @created	March 17, 2013, 13:16 (EET)
 * @revised	November 17, 2014, 12:59 (EET)
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
		logger::log("Starting Redback Engine.", logger::INFO);
		
		// Start Debugger
		debugger::init();
		
		// Start Session
		session::init();
		
		// Init datetimer
		datetimer::init();
		
		// Set default max execution time limit
		set_time_limit(30);
		
		// Init HTTPRequest variables
		HTTPRequest::init();
	}
	
	/**
	 * Restarts the engine. Shutdown and then start again.
	 * 
	 * @return	void
	 */
	public static function restart()
	{
		logger::log("Restarting Redback Engine.", logger::INFO);
		
		// Shutdown Engine
		self::shutdown();
		
		// Start again
		self::start();
	}
	
	/**
	 * Sets in suspension (with the user's log out or switch account) the system platform.
	 * 
	 * @return	void
	 */
	public static function shutdown()
	{
		logger::log("Shutting down Redback Engine.", logger::INFO);
		
		// Destroy session
		session::destroy();
		
		// End Debugger
		debugger::deactivate();
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
	 * It serves not to check implicit with the
	 * 
	 * @return	boolean
	 * 		True if it is a POST request, false otherwise.
	 */
	public static function isPost()
	{
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