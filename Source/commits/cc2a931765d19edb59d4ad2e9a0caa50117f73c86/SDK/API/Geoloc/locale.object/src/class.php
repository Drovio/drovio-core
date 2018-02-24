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

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");

use \ESS\Environment\cookies;
use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	2.0-1
 * @created	March 24, 2014, 9:50 (EET)
 * @updated	April 14, 2015, 10:52 (EEST)
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
		// Check instant cache
		if (!empty(self::$defaultLocale))
			return self::$defaultLocale;
		
		// Check session
		$sessionValue = session::get("locale_default", NULL, "geoloc");
		if (!empty($sessionValue))
		{
			self::$defaultLocale = $sessionValue;
			return self::$defaultLocale;
		}
		
		// Get from database
		$dbc = new dbConnection();
		$dbq = new dbQuery("16422460380945", "geoloc.locale");
		$result = $dbc->execute($dbq);
		$defaultLocale = $dbc->fetch($result);
		self::$defaultLocale = $defaultLocale['locale'];
		
		// Update session and return value
		session::set("locale_default", self::$defaultLocale, "geoloc");
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
		// Get requested locale
		$locale = (empty($locale) ? self::get() : $locale);
		
		// Check session
		$sessionInfo = session::get("locale_info_".$locale, NULL, "geoloc");
		if (!empty($sessionInfo))
			return $sessionInfo;
		
		// Get from database
		$dbc = new dbConnection();
		$dbq = new dbQuery("20214377411845", "geoloc.locale");
		
		$attr = array();
		$attr['locale'] = $locale;
		$result = $dbc->execute($dbq, $attr);
		$localeInfo = $dbc->fetch($result);
		
		// Set session
		session::set("locale_info_".$locale, $localeInfo, "geoloc");
		return $localeInfo;
	}
	
	/**
	 * Set the system's current locale for the user.
	 * 
	 * @param	string	$locale_mixed
	 * 		The locale mixed as [region]:[locale].
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function set($locale_mixed)
	{
		list($region, $locale) = explode(":", $locale_mixed);

		// Set Cookies for a year
		cookies::set("lc", $locale, (365 * 24 * 60 * 60));
		session::set("lc", $locale, $namespace = "geoloc");
	
		if (session::get("lc", NULL, $namespace = "geoloc") == $locale && session::set($region))
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
		// Get locale
		$locale = engine::getVar("lc");

		// If not set as cookie, check session
		if (!isset($locale) || $locale == NULL || $locale == 'deleted')
		{
			// Get from session
			$locale = session::get("lc", NULL, $namespace = "geoloc");
			
			// If not set as session, set default
			if (!isset($locale))
				$locale = self::getDefault();
				
			// Set Cookie for a year
			cookies::set("lc", $locale, (365 * 24 * 60 * 60));
		}
		
		session::set("lc", $locale, "geoloc");
		return $locale;
	}
}
//#section_end#
?>