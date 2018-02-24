<?php
//#section#[header]
// Namespace
namespace DEV\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Geoloc", "locale");
importer::import("API", "Profile", "account");
importer::import("API", "Security", "privileges");
importer::import("DEV", "Profiler", "tester");

use \ESS\Environment\session;
use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Geoloc\locale;
use \API\Profile\account;
use \API\Security\privileges;
use \DEV\Profiler\tester;

/**
 * User Translator
 * 
 * Manager class for system translators.
 * 
 * @version	1.0-1
 * @created	October 22, 2014, 12:24 (EEST)
 * @updated	July 15, 2015, 10:00 (EEST)
 */
class translator extends tester
{
	/**
	 * Activates the literal translator mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		return parent::activate("lc_translator");
	}
	
	/**
	 * Deactivates the literal translator mode.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate()
	{
		return parent::deactivate("lc_translator");
	}
	
	/**
	 * Check the literal translator mode.
	 * 
	 * @param	string	$locale
	 * 		The locale to check the status for.
	 * 		If NULL, check only if translator is activated.
	 * 		It is NULL by default.
	 * 
	 * @return	boolean
	 * 		Returns whether the user is a translator and the translaction locale is the current locale of the system.
	 */
	public static function status($locale = NULL)
	{
		// Check cookie status
		if (!parent::status("lc_translator"))
			return FALSE;
		
		// Check specific locale to check
		if (empty($locale))
			return parent::status("lc_translator");
		//echo "check session\n";
		// Check session
		$sessionStatus = self::sessionStatus($locale);
		if (empty($sessionStatus))
		{//echo "empty\n";
			// Check account id
			$accountID = account::getAccountID();
			if (empty($accountID) || !account::validate())
				return FALSE;
			//echo "c1\n";
			// Check if user is in the translator group
			if (!privileges::accountToGroup('TRANSLATOR'))
				return FALSE;
			//echo "c2\n";
			// If user is in the group, check if is an active translator and set translation locale
			$dbc = new dbConnection();
			$dbq = new dbQuery("1992952947", "profile.translator");
				
			$attr = array();
			$attr['id'] = $accountID;
			$result = $dbc->execute($dbq, $attr);
			$translator = $dbc->get_num_rows($result);
	
			if ($translator > 0)
			{//echo "c3\n";
				// Fetch Data and set session value
				$translatorData = $dbc->fetch($result);
				session::set("locale", $translatorData['locale'], $namespace = 'translator');
			}
			else
				session::remove("locale", $namespace = 'translator');
		}
		
		return self::sessionStatus($locale);
	}
	
	/**
	 * Performs an inner check of the translator's status.
	 * 
	 * @param	string	$locale
	 * 		The locale value to check against session value.
	 * 
	 * @return	boolean
	 * 		Returns whether the user is a translator and the translaction locale is the current locale of the system.
	 */
	private static function sessionStatus($locale)
	{
		// Empty locale invalidates this
		if (empty($locale))
			return FALSE;
			
		// Get Session Translator Status
		$sessionLocale = session::get('locale', $default = NULL, $namespace = 'translator');
		return ($sessionLocale == $locale);
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
		$dbc = new dbConnection();
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
	 * @return	void
	 */
	public static function leave()
	{
		// Leave User from Translator User Group 'TRANSLATOR'
		$accountID = account::getAccountID();
		privileges::leaveAccountFromGroup($accountID, 'TRANSLATOR');
		
		// Deactivate User from Translator Group Stats
		$dbc = new dbConnection();
		$dbq = new dbQuery("1537132710", "profile.translator");
		
		$attr = array();
		$attr['id'] = $accountID;
		if ($dbc->execute($dbq, $attr))
		{
			session::remove("profile", $namespace = 'translator');
			session::remove("locale", $namespace = 'translator');
			return TRUE;
		}
		
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
		// Check session
		$sessionProfile = session::get("profile", NULL, $namespace = 'translator');
		if (empty($sessionProfile))
		{
			// Get translator profile
			$dbc = new dbConnection();
			$dbq = new dbQuery("1992952947", "profile.translator");
			
			$attr = array();
			$attr['id'] = account::getAccountID();
			$result = $dbc->execute($dbq, $attr);
			$sessionProfile = $dbc->fetch($result);
			session::set("profile", $sessionProfile, $namespace = 'translator');
		}
		
		// Return profile
		return $sessionProfile;
	}
}
//#section_end#
?>