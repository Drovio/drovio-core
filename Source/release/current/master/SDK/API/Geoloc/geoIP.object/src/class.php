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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Geolocation
 * 
 * This class uses GeoIP php extension to get information about country codes and timezones based on the ip address.
 * This version is not working for detailed timezones where there might be more in a country.
 * 
 * @version	0.1-1
 * @created	October 19, 2015, 12:45 (BST)
 * @updated	October 19, 2015, 12:45 (BST)
 */
class geoIP
{
	/**
	 * Get the corresponding timezone according to ip address.
	 * 
	 * @param	string	$ipAddress
	 * 		The ip address.
	 * 		Leave empty to get the current remote address.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The ip timezone.
	 */
	public static function getTimezoneByIP($ipAddress = "")
	{
		// Get remote ip address if empty
		$ipAddress = (empty($ipAddress) ? $_SERVER['REMOTE_ADDR'] : $ipAddress);
		
		// Get country code
		// In the future should be replaced with full region info
		// to support countries with more than 1 timezones
		$countryCode = self::getCountryCode2ByIP($ipAddress);
		
		// Return timezone
		return geoip_time_zone_by_country_and_region($countryCode);
	}
	
	/**
	 * Get the country ISO2A code by ip.
	 * 
	 * @param	string	$ipAddress
	 * 		The ip address.
	 * 		Leave empty to get the current remote address.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The country code.
	 */
	public static function getCountryCode2ByIP($ipAddress = "")
	{
		// Get remote ip address if empty
		$ipAddress = (empty($ipAddress) ? $_SERVER['REMOTE_ADDR'] : $ipAddress);
		
		// Return country code
		return geoip_country_code_by_name($ipAddress);
	}
	
	/**
	 * Get the country ISO3A code by ip.
	 * 
	 * @param	string	$ipAddress
	 * 		The ip address.
	 * 		Leave empty to get the current remote address.
	 * 		It is empty by default.
	 * 
	 * @return	string
	 * 		The country code.
	 */
	public static function getCountryCode3ByIP($ipAddress = "")
	{
		// Get remote ip address if empty
		$ipAddress = (empty($ipAddress) ? $_SERVER['REMOTE_ADDR'] : $ipAddress);
		
		// Return country code
		return geoip_country_code3_by_name($ipAddress);
	}
}
//#section_end#
?>