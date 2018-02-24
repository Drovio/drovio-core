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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "sql::dbQuery");
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
 * @version	0.1-1
 * @created	October 9, 2014, 19:24 (EEST)
 * @revised	October 9, 2014, 19:24 (EEST)
 */
class locale extends APILocale
{
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
		$dbq = new dbQuery("237479614", "geo");
		
		$attr = array();
		$attr['locale'] = $locale;
		return $dbc->execute($dbq, $attr);
	}
}
//#section_end#
?>