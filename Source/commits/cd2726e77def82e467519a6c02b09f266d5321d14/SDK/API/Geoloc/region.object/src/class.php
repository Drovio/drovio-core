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
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Geoloc", "locale");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\storage\session;
use \API\Resources\storage\cookies;
use \API\Geoloc\locale;
 
/**
 * Region Manager
 * 
 * Gets all the information about the user's region.
 * 
 * @version	{empty}
 * @created	March 24, 2014, 9:56 (EET)
 * @revised	March 29, 2014, 13:26 (EET)
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
	 * Gets the default region by locale.
	 * 
	 * @return	string
	 * 		It is the countryCode_ISO2A field, so the ISO2A country code.
	 */
	public static function getDefault()
	{
		if (empty(self::$defaultRegion))
		{
			$dbc = new interDbConnection();
			$dbq = new dbQuery("641161041", "resources.geoloc.locale");
			$result = $dbc->execute($dbq);
			$defaultRegion = $dbc->fetch($result);
			self::$defaultRegion = $defaultRegion['countryCode_ISO2A'];
		}
		
		return self::$defaultRegion;
	}
	
	/**
	 * Get region information.
	 * 
	 * @param	{type}	$locale
	 * 		{description}
	 * 
	 * @return	array
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Geoloc\locale::info() instead.
	 */
	public static function getInfo($locale = "")
	{
		return locale::info($locale);
	}
	
	/**
	 * Get user's current country id.
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Geoloc\locale::info() and get the country id from there instead.
	 */
	public static function get_countryId()
	{
		// Region Selector
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1072413526", "geo");

		$attr = array();
		$attr['locale'] = locale::get();
		$result = $dbc->execute($dbq, $attr);
		$currentRegion = $dbc->fetch($result);
		
		return $currentRegion['country_id'];
	}
	
	/**
	 * Get user's current country name.
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Geoloc\locale::info() and get the country name from there instead.
	 */
	public static function get_country()
	{
		// Region Selector
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1072413526", "geo");

		$attr = array();
		$attr['locale'] = locale::get();
		$result = $dbc->execute($dbq, $attr);
		$currentRegion = $dbc->fetch($result);
		
		return $currentRegion['countryName'];
	}
}
//#section_end#
?>