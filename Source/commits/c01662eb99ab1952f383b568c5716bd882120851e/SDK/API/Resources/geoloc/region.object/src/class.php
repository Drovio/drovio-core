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

importer::import("ESS", "Environment", "session");
importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Resources", "geoloc::locale");

use \ESS\Environment\session;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Resources\geoloc\locale;
 
/**
 * Region Manager
 * 
 * Gets all the information about the user's region.
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 14:02 (EEST)
 * @revised	November 5, 2014, 16:51 (EET)
 * 
 * @deprecated	Use \API\Geoloc\region instead.
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