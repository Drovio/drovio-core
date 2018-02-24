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
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Geoloc", "region");

use \ESS\Environment\session;
use \ESS\Environment\cookies;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Geoloc\region;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	1.0-1
 * @created	March 24, 2014, 9:50 (EET)
 * @updated	March 6, 2015, 14:58 (EET)
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
		// Get cookie
		$locale = cookies::get("lc");

		// If not set as cookie, check session
		if (!isset($locale) || $locale == NULL)
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