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

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "accountSettings");

use \ESS\Environment\cookies;
use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \API\Profile\account;
use \API\Profile\accountSettings;

/**
 * System Locale Manager
 * 
 * Handles all about languages and locales
 * 
 * @version	4.0-2
 * @created	March 24, 2014, 9:50 (EET)
 * @updated	July 5, 2015, 23:15 (EEST)
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
	 * The current locale as class cache.
	 * 
	 * @type	string
	 */
	private static $currentLocale = "";
	
	/**
	 * Whether locale is already initiated.
	 * 
	 * @type	boolean
	 */
	private static $initiated = FALSE;
	
	/**
	 * Initialize locale for the current script and get account settings.
	 * 
	 * @return	void
	 */
	public static function init()
	{
		// Check if locale is already initiated
		if (self::$initiated)
			return;
		
		// Check if there is a locale cookie already
		$lc_cookie = cookies::get("lc");
		if (!empty($lc_cookie))
			return;
			
		// Get browser accept locale (get first available)
		$browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		$browserLang = str_replace("-", "_", $browserLang);
		
		// Get all active locale to validate
		$activeLocale = self::active();
		$allLocale = array();
		foreach ($activeLocale as $localeInfo)
		{
			$locale = $localeInfo['locale'];
			$short = substr($locale, 0, 2);
			$allLocale[$locale] = $locale;
			$allLocale[$short] = $locale;
		}
		$browserLocale = $allLocale[$browserLang];
		
		// Check logged in account
		if (account::validate())
		{
			// Get locale from account settings
			$pAccount = new accountSettings();
			
			// Load timezone from user settings
			$currentLocale = $pAccount->get("locale");
			if (empty($currentLocale))
				$currentLocale = (empty($browserLocale) ? locale::getDefault() : $browserLocale);
			
			// Set locale as cookie
			self::set($currentLocale);
		}
		else
			$currentLocale = (empty($browserLocale) ? locale::getDefault() : $browserLocale);
		
		// Set current locale
		self::$currentLocale = $currentLocale;
		self::$initiated = TRUE;
	}
	
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
	 * @param	string	$locale
	 * 		The locale to set.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function set($locale)
	{
		// COMPATIBILITY
		list($region, $locale) = explode(":", $locale);
		if (empty($locale))
			$locale = $region;

		// Set Cookies for a year
		cookies::set("lc", $locale, (365 * 24 * 60 * 60));
		
		// Update account settings
		if (account::validate())
		{
			// Get locale from account settings
			$pAccount = new accountSettings();
			$pAccount->set("locale", $locale);
		}
		
		// Set class cache
		self::$currentLocale = $locale;
	}
	
	/**
	 * Get the current active locale.
	 * 
	 * @return	string
	 * 		The locale string.
	 */
	public static function get()
	{
		// Get cache
		if (!empty(self::$currentLocale))
			return self::$currentLocale;
			
		// Get locale
		$locale = engine::getVar("lc");

		// If not set as cookie, check session
		if (!isset($locale) || $locale == NULL || $locale == 'deleted')
			return self::getDefault();
		
		return $locale;
	}
	
	/**
	 * Get all the active locales of the system.
	 * 
	 * @return	array
	 * 		An array of all active locale.
	 */
	public static function active()
	{
		// Check session
		$activeLocale = session::get("active_locale", NULL, $namespace = "geoloc");
		if (empty($activeLocale))
		{
			$dbc = new dbConnection();
			$dbq = new dbQuery("30254451052635", "geoloc.locale");
			$result = $dbc->execute($dbq);
			$activeLocale = $dbc->fetch($result, TRUE);
			session::set("active_locale", $activeLocale, $namespace = "geoloc");
		}
		
		return $activeLocale;
	}
	
	/**
	 * Get all the available locale of the system.
	 * This array will include all the locales that have been added but are not available for public use.
	 * 
	 * @return	array
	 * 		An array of all available locale.
	 */
	public static function available()
	{
		// Check session
		$availableLocale = session::get("available_locale", NULL, $namespace = "geoloc");
		if (empty($availableLocale))
		{
			$dbc = new dbConnection();
			$dbq = new dbQuery("28944040528601", "geoloc.locale");
			$result = $dbc->execute($dbq);
			$availableLocale = $dbc->fetch($result, TRUE);
			session::set("available_locale", $availableLocale, $namespace = "geoloc");
		}
		
		return $availableLocale;
	}
}
//#section_end#
?>