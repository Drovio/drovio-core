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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "storage::cookies");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\storage\session;
use \API\Resources\storage\cookies;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	0.1-2
 * @created	March 24, 2014, 9:50 (EET)
 * @revised	July 31, 2014, 17:53 (EEST)
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
	 * Gets the system's default locale.
	 * 
	 * @return	string
	 * 		The default locale.
	 */
	public static function getDefault()
	{
		if (empty(self::$defaultLocale))
		{
			$dbc = new dbConnection();
			$dbq = new dbQuery("641161041", "resources.geoloc.locale");
			$result = $dbc->execute($dbq);
			$defaultLocale = $dbc->fetch($result);
			self::$defaultLocale = $defaultLocale['locale'];
		}
		
		return self::$defaultLocale;
	}
	
	/**
	 * Gets the locale's info.
	 * If attribute is left empty, gets the current locale's info.
	 * 
	 * @param	string	$locale
	 * 		The requested locale.
	 * 		If left empty, get the current locale.
	 * 
	 * @return	array
	 * 		An array of information about the current locale, the country and the language of this locale.
	 */
	public static function info($locale = "")
	{
		$dbc = new dbConnection();
		$dbq = new dbQuery("637187577", "resources.geoloc.locale");
		
		$attr = array();
		$attr['locale'] = (empty($locale) ? self::get() : $locale);
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets all active locale.
	 * 
	 * @return	array
	 * 		An array of all active locale.
	 */
	public static function active()
	{
		$dbc = new dbConnection();
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
		$dbc = new dbConnection();
		$dbq = new dbQuery("664120126", "resources.geoloc.locale");
		$result = $dbc->execute($dbq);
		return $dbc->toFullArray($result);
	}
	
	/**
	 * Set the system's current locale for the user.
	 * 
	 * @param	sring	$locale_mixed
	 * 		The locale mixed as [region]:[locale].
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
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
	 * Get the current active locale.
	 * 
	 * @return	string
	 * 		The locale string.
	 */
	public static function get()
	{
		// Get cookie
		$locale = cookies::get("locale");

		// If not set as cookie, check session
		if (!isset($locale) || $locale == NULL)
		{
			// Get from session
			$locale = session::get("locale", NULL, $namespace = "geoloc");
			
			// If not set as session, set default
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function add($language, $country)
	{
		// Get Query
		$dbc = new dbConnection();
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($locale)
	{
		// Get Query
		$dbc = new dbConnection();
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
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($locale)
	{
		// Get Query
		$dbc = new dbConnection();
		$dbq = new dbQuery("237479614", "geo");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>