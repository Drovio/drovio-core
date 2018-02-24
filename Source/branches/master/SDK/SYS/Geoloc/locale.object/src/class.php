<?php
//#section#[header]
// Namespace
namespace SYS\Geoloc;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Geoloc
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Geoloc", "locale");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Geoloc\locale as APILocale;

/**
 * System Locale Manager
 * 
 * Adds, removes, activates and deactivates locale.
 * Also provides active and available locale listing.
 * 
 * @version	2.0-1
 * @created	October 9, 2014, 19:24 (EEST)
 * @updated	June 8, 2015, 18:01 (EEST)
 */
class locale extends APILocale
{
	/**
	 * Add a new locale as inactive.
	 * 
	 * @param	integer	$language
	 * 		The language id.
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
		$dbq = new dbQuery("30514891478887", "geoloc.locale");
		
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
		$dbq = new dbQuery("22606256195119", "geoloc.locale");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Deactivate a locale and remove from user's selection.
	 * 
	 * @param	string	$locale
	 * 		The locale to deactivate.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($locale)
	{
		// Get Query
		$dbc = new dbConnection();
		$dbq = new dbQuery("19057624772084", "geoloc.locale");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
	
	/**
	 * Set the system's default locale.
	 * 
	 * @param	string	$locale
	 * 		The default locale value.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function setDefault($locale)
	{
		// Get Query
		$dbc = new dbConnection();
		$dbq = new dbQuery("18667590864628", "geoloc.locale");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>