<?php
//#section#[header]
// Namespace
namespace API\Resources\geoloc;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\geoloc
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "storage::cookies");
importer::import("API", "Resources", "geoloc::locale");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\storage\session;
use \API\Resources\storage\cookies;
use \API\Resources\geoloc\locale;
 
/**
 * Region Manager
 * 
 * Gets all the information about the user's region.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 14:02 (EEST)
 * @revised	September 18, 2013, 11:37 (EEST)
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
	 * 		It is the countryCode_ISO2A.
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
	 * 		It is the countryCode_ISO2A.
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
	 * Gets the current region info.
	 * 
	 * @return	array
	 * 		Returns an array with the region info data.
	 */
	public static function getInfo()
	{
		// Region Selector
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1072413526", "geo");

		$attr = array();
		$attr['locale'] = locale::get();
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Get user's current country id.
	 * 
	 * @return	integer
	 * 		{description}
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