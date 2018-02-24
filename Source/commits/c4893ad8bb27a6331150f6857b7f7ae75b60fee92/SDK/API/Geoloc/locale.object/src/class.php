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
importer::import("ESS", "Environment", "cookies");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("SYS", "Geoloc", "locale");
importer::import("API", "Model", "sql/dbQuery");

use \ESS\Environment\session;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \SYS\Geoloc\locale as sysLocale;
use \API\Model\sql\dbQuery;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	0.2-3
 * @created	March 24, 2014, 9:50 (EET)
 * @updated	February 13, 2015, 12:21 (EET)
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
	 * 
	 * @deprecated	Use \SYS\Geoloc\locale::active() instead.
	 */
	public static function active()
	{
		return sysLocale::active();
	}
	
	/**
	 * Gets all available locale.
	 * 
	 * @return	array
	 * 		An array of all available locale.
	 * 
	 * @deprecated	Use \SYS\Geoloc\locale::available() instead.
	 */
	public static function available()
	{
		return sysLocale::available();
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
	 * 
	 * @deprecated	Use \SYS\Geoloc\locale::add() instead.
	 */
	public static function add($language, $country)
	{
		return sysLocale::add($language, $country);
	}
	
	/**
	 * Activate a locale and set available for users.
	 * 
	 * @param	string	$locale
	 * 		The locale to activate.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use \SYS\Geoloc\locale::activate() instead.
	 */
	public static function activate($locale)
	{
		return sysLocale::activate($locale);
	}
	
	/**
	 * Deactivate a locale and remove from user's selection
	 * 
	 * @param	string	$locale
	 * 		The locale to deactivate
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use \SYS\Geoloc\locale::deactivate() instead.
	 */
	public static function deactivate($locale)
	{
		return sysLocale::deactivate($locale);
	}
}
//#section_end#
?>