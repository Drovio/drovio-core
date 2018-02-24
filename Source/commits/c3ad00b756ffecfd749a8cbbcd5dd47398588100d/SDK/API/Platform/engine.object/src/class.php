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

importer::import("API", "Geoloc", "datetimer");
importer::import("API", "Resources", "storage::session");
importer::import("DEV", "Profiler", "debugger");
importer::import("DEV", "Profiler", "logger");

use \API\Geoloc\datetimer;
use \API\Resources\storage\session;
use \DEV\Profiler\debugger;
use \DEV\Profiler\logger;

/**
 * Platform Engine
 * 
 * Class responsible for starting and pausing the platform engine.
 * 
 * @version	0.1-2
 * @created	March 17, 2013, 13:16 (EET)
 * @revised	September 16, 2014, 16:38 (EEST)
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
}
//#section_end#
?>