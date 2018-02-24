<?php
//#section#[header]
// Namespace
namespace API\Resources\geoloc;

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
 * @package	Resources
 * @namespace	\geoloc
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "logger");
importer::import("API", "Profile", "accountSettings");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Security", "account");
importer::import("UI", "Html", "DOM");

use \DEV\Profiler\logger;
use \API\Profile\accountSettings;
use \API\Resources\storage\session;
use \API\Security\account;
use \UI\Html\DOM;

/**
 * Date time manager
 * 
 * Manages the stored date the time and handles how they will be displayed (in the proper timezone) to the user.
 * 
 * @version	{empty}
 * @created	September 18, 2013, 12:38 (EEST)
 * @revised	February 19, 2014, 15:32 (EET)
 */
class datetimer
{
	/**
	 * The auto timezone value.
	 * 
	 * @type	string
	 */
	const AUTO = "auto";
	
	/**
	 * Inits the datetimer and sets the timezone according to user's location.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Log Activity
		logger::log("Init date-timer and loading timezone...", logger::INFO);
		
		// Load timezone
		$timezone = self::loadTimezone();
		
		// Set timezone
		self::setTimezone($timezone, FALSE);
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
	public static function setTimezone($timezone, $updateUser = TRUE)
	{
		// Set php timezone
		date_default_timezone_set($timezone);
		
		// Session set
		session::set("timezone", $timezone, "geoloc");
		
		// Check logged in user and set timezone in user settings
		if ($updateUser && account::validate())
		{
			$pAccount = new accountSettings();
			$pAccount->setSettingsValue("timezone", $timezone);
		}
	}
	
	/**
	 * Loads the timezone from the session or from the user's timezone settings.
	 * If timezone is set to auto, the timezone will be auto determined from the user's location.
	 * 
	 * @return	void
	 */
	private static function loadTimezone()
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
			$timezone = $pAccount->getSettingsValue("timezone");
			if (!empty($timezone) && $timezone != self::AUTO)
				return $timezone;
			else if (empty($timezone))
				$pAccount->setSettingsValue("timezone", self::AUTO);
		}
		
		// Get user's location and set default timezone for display time
		//$ip = $_SERVER;
		
		// This is TEMP
		return "Europe/Athens";
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
		$timespan = DOM::create("abbr", "", "", "timestamp live");
		DOM::attr($timespan, "data-utime", $time);
		DOM::attr($timespan, "data-format", $format);
		DOM::attr($timespan, "title", $title);
		
		return $timespan;
	}
}
//#section_end#
?>