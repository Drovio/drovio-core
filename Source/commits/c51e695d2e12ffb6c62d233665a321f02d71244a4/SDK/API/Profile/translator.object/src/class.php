<?php
//#section#[header]
// Namespace
namespace API\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "geoloc::locale");
importer::import("API", "Security", "account");
importer::import("API", "Security", "privileges");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Resources\storage\session;
use \API\Resources\geoloc\locale;
use \API\Security\account;
use \API\Security\privileges;

/**
 * User Translator
 * 
 * Manager class for system translators.
 * 
 * @version	{empty}
 * @created	July 2, 2013, 14:34 (EEST)
 * @revised	October 17, 2013, 15:50 (EEST)
 */
class translator
{
	/**
	 * Returns the user's translator status.
	 * 
	 * @return	boolean
	 * 		Returns whether the user is a translator and the translaction locale is the current locale of the system.
	 */
	public static function status()
	{
		// Inner Check
		$check = self::sessionStatus();
		
		// Check Status
		if (!is_null($check))
			return $check;
			
		// Check if user is in the translator group
		if (!privileges::accountToGroup('TRANSLATOR'))
			return FALSE;
		
		// If user is in the group, check if is an active translator and set translation locale
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1992952947", "profile.translator");
		
		$accountID = account::getAccountID();
		if (empty($accountID))
			return FALSE;
			
		$attr = array();
		$attr['id'] = $accountID;
		$result = $dbc->execute($dbq, $attr);
		$translator = $dbc->get_num_rows($result);

		if ($translator > 0)
		{
			// Fetch Data
			$translatorData = $dbc->fetch($result);
			
			// Set Session Data
			session::set('status', $translatorData['active'], $namespace = 'translator');
			session::set('locale', $translatorData['locale'], $namespace = 'translator');
		}
		else
			session::set('status', FALSE, $namespace = 'translator');
		
		return self::sessionStatus();
	}
	
	/**
	 * Performes an inner check of the translator's status.
	 * 
	 * @return	boolean
	 * 		Returns whether the user is a translator and the translaction locale is the current locale of the system.
	 */
	private static function sessionStatus()
	{
		// Get Session Translator Status	
		$sessionStatus = session::get('status', $default = NULL, $namespace = 'translator');
		$sessionLocale = session::get('locale', $default = NULL, $namespace = 'translator');
		
		// If there is session status, return status
		if (!is_null($sessionStatus))
			return ($sessionStatus && $sessionLocale == locale::get());
		
		// Return NULL if not in session
		return NULL;
	}
	
	/**
	 * Joins the translator user group.
	 * 
	 * @param	string	$locale
	 * 		The locale to translate to.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function join($locale)
	{
		// Join User to Translator User Group 'TRANSLATOR'
		$accountID = account::getAccountID();
		privileges::addAccountToGroup($accountID, 'TRANSLATOR');
		
		// Join User To Translator Group Stats
		$dbc = new interDbConnection();
		$dbq = new dbQuery("660376081", "profile.translator");
		
		$attr = array();
		$attr['id'] = $accountID;
		$attr['locale'] = $locale;
		if ($dbc->execute($dbq, $attr))
			return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Leaves the translator user group.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function leave()
	{
		// Leave User from Translator User Group 'TRANSLATOR'
		$accountID = account::getAccountID();
		privileges::leaveAccountFromGroup($accountID, 'TRANSLATOR');
		
		// Deactivate User from Translator Group Stats
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1537132710", "profile.translator");
		
		$attr = array();
		$attr['id'] = $accountID;
		if ($dbc->execute($dbq, $attr))
			return TRUE;
		
		return FALSE;
	}
	
	/**
	 * Checks if the user is translator and returns the profile.
	 * 
	 * @return	array
	 * 		Returns in array the profile of the translator (id, locale, languageName etc).
	 */
	public static function profile()
	{
		// Get translator profile
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1992952947", "profile.translator");
		
		$attr = array();
		$attr['id'] = account::getAccountID();
		$result = $dbc->execute($dbq, $attr);
		$translatorData = $dbc->fetch($result);
		
		return $translatorData;
	}
}
//#section_end#
?>