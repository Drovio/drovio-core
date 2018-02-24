<?php
//#section#[header]
// Namespace
namespace API\Geoloc;

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
 * @package	Geoloc
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "accountSettings");
importer::import("UI", "Html", "DOM");
importer::import("DEV", "Profiler", "logger");

use \ESS\Environment\session;
use \API\Profile\account;
use \API\Profile\accountSettings;
use \UI\Html\DOM;
use \DEV\Profiler\logger;

/**
 * Date time manager
 * 
 * Manages the stored date the time and handles how they will be displayed (in the proper timezone) to the user.
 * 
 * @version	1.0-3
 * @created	March 24, 2014, 10:03 (EET)
 * @updated	May 4, 2015, 17:36 (EEST)
 */
class datetimer
{
	/**
	 * The auto timezone value.
	 * 
	 * @type	string
	 */
	const AUTO = "AUTO";
	
	/**
	 * Inits the datetimer and sets the timezone according to user's location.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Log Activity
		logger::log("Init datetimer and load timezone...", logger::INFO);
		
		// Load timezone
		$timezone = self::get();
		
		// Set timezone
		self::set($timezone, FALSE);
	}
	
	/**
	 * Sets the current timezone for the system and for the user.
	 * 
	 * @param	string	$timezone
	 * 		The timezone to be set.
	 * 
	 * @param	boolean	$updateUser
	 * 		Indicates whether the user's timezone settings will be updated.
	 * 
	 * @return	void
	 */
	public static function set($timezone, $updateUser = TRUE)
	{
		// Set php timezone
		date_default_timezone_set($timezone);
		
		// Session set
		session::set("timezone", $timezone, "geoloc");
		
		// Check logged in user and set timezone in user settings
		if ($updateUser && account::validate())
		{
			$pAccount = new accountSettings();
			$pAccount->set("timezone", $timezone);
		}
	}
	
	/**
	 * Get the current timezone.
	 * If timezone is set to auto, the timezone will be auto determined from the user's location.
	 * 
	 * @return	string
	 * 		The current timezone.
	 */
	public static function get()
	{
		// Get from session
		$timezone = session::get("timezone", NULL, "geoloc");
		if (!is_null($timezone))
			return $timezone;
		
		// Check logged in user
		if (account::validate())
		{
			$pAccount = new accountSettings();
			
			// Load timezone from user settings
			$timezone = $pAccount->get("timezone");
			if (!empty($timezone) && $timezone != self::AUTO)
				return $timezone;
			else if (empty($timezone))
				$pAccount->set("timezone", self::AUTO);
		}
		
		// Get user's location and set default timezone for display time
		//$ip = $_SERVER;
		
		// This is TEMP
		return "Europe/Athens";
	}
	
	/**
	 * Sets the current timezone for the system and for the user.
	 * 
	 * @param	string	$timezone
	 * 		The timezone to be set.
	 * 
	 * @param	boolean	$updateUser
	 * 		Indicates whether the user's timezone settings will be updated.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use set() instead.
	 */
	public static function setTimezone($timezone, $updateUser = TRUE)
	{
		self::set($timezone, $updateUser);
	}
	
	/**
	 * Creates an element that displays a live timestamp (updates with an interval of 30 seconds).
	 * 
	 * @param	integer	$time
	 * 		The unix timestamp.
	 * 
	 * @param	string	$format
	 * 		The time format to display on hover (php time format).
	 * 
	 * @return	DOMElement
	 * 		The abbr element.
	 */
	public static function live($time = "", $format = 'd F, Y \a\t H:i')
	{
		// Get time
		$time = (empty($time) ? time() : $time);
		
		// Get title
		$title = date($format, $time);
		
		// Create timespan
		$timespan = DOM::create("abbr", $title, "", "timestamp live");
		DOM::attr($timespan, "data-utime", $time);
		DOM::attr($timespan, "data-format", $format);
		DOM::attr($timespan, "title", $title);
		
		return $timespan;
	}
}
//#section_end#
?>