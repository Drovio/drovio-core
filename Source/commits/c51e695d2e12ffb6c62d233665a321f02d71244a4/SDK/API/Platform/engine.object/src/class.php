<?php
//#section#[header]
// Namespace
namespace API\Platform;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Platform
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::debugger");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "geoloc::datetimer");

use \API\Developer\profiler\debugger;
use \API\Resources\storage\session;
use \API\Resources\geoloc\datetimer;

/**
 * Platform Engine
 * 
 * Class responsible for starting and pausing the platform engine.
 * 
 * @version	{empty}
 * @created	March 17, 2013, 13:16 (EET)
 * @revised	November 12, 2013, 11:47 (EET)
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
		// Start Debugger
		debugger::init();
		
		// Start Session
		session::init();
		
		// Init datetimer
		datetimer::init();
	}
	
	/**
	 * Restarts the engine. Shutdown and then start again.
	 * 
	 * @return	void
	 */
	public static function restart()
	{
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
		// Destroy session
		session::destroy();
		
		// End Debugger
		debugger::deactivate();
	}
}
//#section_end#
?>