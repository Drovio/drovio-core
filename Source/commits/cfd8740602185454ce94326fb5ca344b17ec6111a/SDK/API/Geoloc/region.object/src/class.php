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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");

use \ESS\Environment\session;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
 
/**
 * Region Manager
 * 
 * Gets all the information about the user's region.
 * 
 * @version	2.0-1
 * @created	March 24, 2014, 9:56 (EET)
 * @updated	August 20, 2015, 12:46 (EEST)
 */
class region
{
	/**
	 * The default system region.
	 * 
	 * @type	string
	 */
	private static $defaultRegion = "";
	
	/**
	 * Get the user's current region
	 * 
	 * @return	string
	 * 		It is the countryCode_ISO2A field, so the ISO2A country code.
	 */
	public static function get()
	{
		// Get cookie
		$region = cookies::get("region");
		
		// If not set as cookie, check session
		if (!isset($region) || $region == NULL)
		{
			// If not set as session, set default
			$region = session::get("region", NULL, $namespace = "geoloc");
			if (!isset($region))
				$region = self::getDefault();
				
			// Set Cookie for a year
			cookies::set("region", $region, (365 * 24 * 60 * 60));
		}
		session::set("region", $region, "geoloc");
		return $region;
	}
	
	/**
	 * Set the system's current region value for the user.
	 * 
	 * @param	string	$region
	 * 		The region value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use \API\Geoloc\locale::info() instead.
	 */
	public static function set($region)
	{
		// Set Cookies for a year
		cookies::set("region", $region, (365 * 24 * 60 * 60));
		session::set("region", $region, $namespace = "geoloc");
	
		// Validate
		if (session::get("region", NULL, $namespace = "geoloc") == $region)
			return TRUE;
		return FALSE;
	}
	
	/**
	 * Gets the default region by locale.
	 * 
	 * @return	string
	 * 		It is the countryCode_ISO2A field, so the ISO2A country code.
	 */
	public static function getDefault()
	{
		if (empty(self::$defaultRegion))
		{
			$dbc = new dbConnection();
			$dbq = new dbQuery("641161041", "resources.geoloc.locale");
			$result = $dbc->execute($dbq);
			$defaultRegion = $dbc->fetch($result);
			self::$defaultRegion = $defaultRegion['countryCode_ISO2A'];
		}
		
		return self::$defaultRegion;
	}
	
	/**
	 * Get all countries.
	 * 
	 * @return	array
	 * 		An array of all countries by id.
	 */
	public static function getAllCountries()
	{
		// Get countries from session
		$sessionCountries = session::get("countries", NULL, $namespace = "geoloc");
		if (empty($sessionCountries))
		{
			// Get countries from db
			$dbc = new dbConnection();
			$dbq = new dbQuery("1434209549", "resources.geoloc.countries");
			$result = $dbc->execute($dbq);
			
			$sessionCountries = array();
			while ($row = $dbc->fetch($result))
				$sessionCountries[$row['id']] = $row;
			
			// Update session
			session::set("countries", $sessionCountries, $namespace = "geoloc");
		}
		
		// Return all countries
		return $sessionCountries;
	}
	
	/**
	 * Get country info.
	 * 
	 * @param	integer	$countryID
	 * 		The country id to get information for.
	 * 
	 * @return	array
	 * 		An array of all country info.
	 */
	public static function getCountryInfo($countryID)
	{
		// Get all countries
		$allCountries = self::getAllCountries();
		return $allCountries[$countryID];
	}
}
//#section_end#
?>