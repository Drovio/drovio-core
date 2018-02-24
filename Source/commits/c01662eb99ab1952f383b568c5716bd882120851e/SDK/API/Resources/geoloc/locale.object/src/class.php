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

use \ESS\Environment\session;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 14:01 (EEST)
 * @revised	November 5, 2014, 16:50 (EET)
 * 
 * @deprecated	Use \API\Geoloc\locale instead.
 */
class locale
{
	/**
	 * The default system locale.
	 * 
	 * @type	string
	 */
	private static $defaultLocale = "";
	
	/**
	 * Get the default system's locale
	 * 
	 * @return	string
	 * 		{description}
	 * 
	 * @deprecated	Use getDefault() instead.
	 */
	public static function _default()
	{
		return self::getDefault();
	}
	
	/**
	 * Gets the system's default locale.
	 * 
	 * @return	string
	 * 		Returns the default locale.
	 */
	public static function getDefault()
	{
		if (empty(self::$defaultLocale))
		{
			$dbc = new interDbConnection();
			$dbq = new dbQuery("641161041", "resources.geoloc.locale");
			$result = $dbc->execute($dbq);
			$defaultLocale = $dbc->fetch($result);
			self::$defaultLocale = $defaultLocale['locale'];
		}
		
		return self::$defaultLocale;
	}
	
	/**
	 * Gets the current's locale info.
	 * 
	 * @return	array
	 * 		An array that contains all the info about the locale, the country and the languace.
	 */
	public static function info()
	{
		$dbc = new interDbConnection();
		$dbq = new dbQuery("637187577", "resources.geoloc.locale");
		$attr = array();
		$attr['locale'] = self::get();
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets all active locale.
	 * 
	 * @return	array
	 * 		An array of all active locale
	 */
	public static function active()
	{
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1632915376", "resources.geoloc.locale");
		$result = $dbc->execute($dbq);
		return $dbc->toFullArray($result);
	}
	
	/**
	 * Gets all available locale.
	 * 
	 * @return	array
	 * 		An array of all available locale.
	 */
	public static function available()
	{
		$dbc = new interDbConnection();
		$dbq = new dbQuery("664120126", "resources.geoloc.locale");
		$result = $dbc->execute($dbq);
		return $dbc->toFullArray($result);
	}
	
	/**
	 * Set the system's current locale for the user.
	 * 
	 * @param	string	$locale_mixed
	 * 		The locale mixed as [region]:[locale].
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function set($locale_mixed)
	{
		list($region, $locale) = explode(":", $locale_mixed);

		// Set Cookies for a year
		cookies::set("region", $region, (365 * 24 * 60 * 60));
		cookies::set("locale", $locale, (365 * 24 * 60 * 60));
		
		session::set("locale", $locale, $namespace = "geoloc");
		session::set("region", $region, $namespace = "geoloc");
	
		if (session::get("locale", NULL, $namespace = "geoloc") == $locale && session::get("region", NULL, $namespace = "geoloc") == $region)
			return TRUE;
		return FALSE;
	}
	
	/**
	 * Get the current system's active locale
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get()
	{
		// Get cookie
		$locale = cookies::get("locale");
		
		// If not set as cookie, check session
		if (!isset($locale) || $locale == NULL)
		{
			// If not set as session, set default
			$locale = session::get("locale", NULL, $namespace = "geoloc");
			if (!isset($locale))
				$locale = self::getDefault();
				
			// Set Cookie for a year
			cookies::set("locale", $locale, (365 * 24 * 60 * 60));
		}
		
		session::set("locale", $locale, "geoloc");
		return $locale;
	}
	
	/**
	 * Add a new locale as inactive.
	 * 
	 * @param	integer	$language
	 * 		The language id
	 * 
	 * @param	integer	$country
	 * 		The country id.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public static function add($language, $country)
	{
		// Get Query
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1999273534", "geo");
		
		$attr = array();
		$attr['language_id'] = $language;
		$attr['country_id'] = $country;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Activate a locale and set available for users.
	 * 
	 * @param	string	$locale
	 * 		The locale to activate.
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public static function activate($locale)
	{
		// Get Query
		$dbc = new interDbConnection();
		$dbq = new dbQuery("65630929", "geo");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Deactivate a locale and remove from user's selection
	 * 
	 * @param	string	$locale
	 * 		The locale to deactivate
	 * 
	 * @return	void
	 */
	public static function deactivate($locale)
	{
		// Get Query
		$dbc = new interDbConnection();
		$dbq = new dbQuery("237479614", "geo");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>