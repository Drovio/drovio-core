<?php
//#section#[header]
// Namespace
namespace API\Security;

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
 * @package	Security
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Platform", "engine");
importer::import("API", "Profile", "person");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Resources", "storage::cookies");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Platform\engine;
use \API\Profile\person;
use \API\Resources\storage\session;
use \API\Resources\storage\cookies;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 12:39 (EEST)
 * @revised	January 20, 2014, 10:54 (EET)
 */
class account
{
	/**
	 * Indicates whether the account is logged in for this run.
	 * 
	 * @type	boolean
	 */
	private static $loggedIn = FALSE;
	
	/**
	 * {description}
	 * 
	 * @type	{empty}
	 */
	private static $salt = "";
	
	/**
	 * Authenticates an account with the given username and password.
	 * 
	 * @param	string	$username
	 * 		The person's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @param	string	$accountID
	 * 		The account id to authenticate. If empty, get the current account id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function authenticate($username, $password, $accountID = "")
	{
		// Set Database Connection
		$dbc = new interDbConnection();
		$q = new dbQuery("2064670715", "profile.account");
		
		// Authenticate account
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$result = $dbc->execute($q, $attr);

		// If account not found, return FALSE
		if ($dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Get account ID
		if (empty($accountID))
			$accountID = self::getAccountID();
		
		// If account is found, return account ID
		$accountData = $dbc->fetch($result);
		return ($accountData['accountID'] == $accountID);
	}
	
	/**
	 * Authenticates an account and sets it as logged in.
	 * 
	 * @param	string	$username
	 * 		The person's username.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @param	integer	$rememberDuration
	 * 		The remember duration of the cookies.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function login($username, $password, $rememberDuration = 0)
	{
		// Set Database Connection
		$dbc = new interDbConnection();
		$q = new dbQuery("2064670715", "profile.account");
		
		// Authenticate account
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$result = $dbc->execute($q, $attr);

		// If user not found, return FALSE
		if ($dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Fetch Account
		$accountData = $dbc->fetch($result);
		
		// Get Account Info
		$locked = $accountData['locked'];
		$accountID = $accountData['accountID'];
		$personID = "";
		if (!$locked)
			$personID = $accountData['personID'];
			
		// Create salt
		$salt = hash("md5", time());
		
		// Create Account Session
		self::createSession($accountID, $salt, $personID, $rememberDuration);
		
		// Update Session Account Data
		self::updateSessionData($accountData);
		
		return TRUE;
	}
	
	/**
	 * Validates if the user is logged in.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate()
	{return FALSE;
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;
			
		// Get cookies
		$accountID = cookies::get("acc");
		$mix = cookies::get("mx");
		$personID = cookies::get("person");

		// Check if the cookies exist
		if (empty($accountID) || empty($mix))
		{
			self::logout();
			return FALSE;
		}

		// Get salt from account
		$salt = self::getSalt();

		// If salt is empty, return FALSE
		if (empty($salt))
		{
			self::logout();
			return FALSE;
		}

		// Check mix
		$genMix = self::getSaltedMix(self::getSessionID(), $accountID, $salt, $personID);
		$valid = ($genMix === $mix);
		if (!$valid)
		{
			self::logout();
			return FALSE;
		}
		
		// Update session date modified and reset cookies
		$accountSessionExists = session::get("exists", "", "account");
		if (empty($accountSessionExists))
		{
			// Update database
			$dbc = new interDbConnection();
			$q = new dbQuery("786733301", "profile.account");
			$attr = array();
			$attr['aid'] = self::getAccountID();
			$attr['sid'] = self::getSessionID();
			$attr['ip'] = $_SERVER['REMOTE_ADDR'];
			$attr['date'] = time();
			$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			$dbc->execute($q, $attr);
			
			// Update session data
			self::updateSessionData();
			
			// Update cookies
		}
		
		// Return valid status
		self::$loggedIn = TRUE;
		return TRUE;
	}
	
	/**
	 * Creates a new account session.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$salt
	 * 		The logged in generated salt.
	 * 
	 * @param	integer	$personID
	 * 		The person id (if any).
	 * 
	 * @param	integer	$rememberDuration
	 * 		The remember duration for the cookies.
	 * 
	 * @return	void
	 */
	private static function createSession($accountID, $salt, $personID = "", $rememberDuration = 0)
	{
		// Create new Account Session
		$dbc = new interDbConnection();
		$q = new dbQuery("1557484874", "profile.account");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['salt'] = $salt;
		$attr['ip'] = $_SERVER['REMOTE_ADDR'];
		$attr['date'] = time();
		$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$result = $dbc->execute($q, $attr);
		
		// Database error
		if (!$result)
			return FALSE;
			
		// Get session id
		$accountSession = $dbc->fetch($result);
		$sessionID = $accountSession['id'];
		
		// Get Salted mix
		$mx = self::getSaltedMix($sessionID, $accountID, $salt, $personID);
		
		// Delete all account cookies first
		cookies::delete("acc");
		cookies::delete("person");
		cookies::delete("mx");
		
		// Set Cookies
		cookies::set("acc", $accountID, $rememberDuration);
		cookies::set("person", $personID, $rememberDuration);
		cookies::set("mx", $mx, $rememberDuration);
		
		// Update salt
		self::updateSessionSalt($salt);
		
		// Set static logged in
		self::$loggedIn = TRUE;
	}
	
	/**
	 * Deletes the current active session.
	 * 
	 * @param	string	$sessionID
	 * 		The current account session id.
	 * 
	 * @return	void
	 */
	public static function deleteActiveSession($sessionID)
	{
		// Remove active session
		$dbc = new interDbConnection();
		$q = new dbQuery("445420676", "profile.account");
		$attr = array();
		$attr['sid'] = $sessionID;
		$attr['aid'] = self::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $result;
	}
	
	/**
	 * Returns the salted mix of account, person and salt.
	 * 
	 * @param	string	$sessionID
	 * 		The account session id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$salt
	 * 		The account salt.
	 * 
	 * @param	string	$personID
	 * 		The person id.
	 * 
	 * @return	string
	 * 		The salted mix to store to cookies.
	 */
	private static function getSaltedMix($sessionID, $accountID, $salt, $personID = "")
	{
		// Create account mix
		$mix = $accountID."_".$salt."_".$personID;
		
		// Create mix aray
		$mixArray = array();
		$mixArray[] = $sessionID;
		$mixArray[] = hash("md5", $mix);
		
		// Return mix
		return implode(":", $mixArray);
	}
	
	/**
	 * Gets the stored salt for the current account session.
	 * 
	 * @return	string
	 * 		The stored salt.
	 */
	private static function getSalt()
	{
		// Get salt from variable
		if (!empty(self::$salt))
			return self::$salt;
			
		// Get salt from database
		$dbc = new interDbConnection();
		$q = new dbQuery("554184391", "profile.account");
		$attr = array();
		$attr['sid'] = self::getSessionID();
		$attr['aid'] = self::getAccountID();
		$result = $dbc->execute($q, $attr);
		
		// Check if the session is valid and exists, otherwise, return empty salt
		if ($dbc->get_num_rows($result) == 0)
			$salt = "";
		else
		{
			$row = $dbc->fetch($result);
			$salt = $row['salt'];
		}
		
		// Update Session Salt
		return $salt;
	}
	
	/**
	 * Updates the salt in session variable.
	 * 
	 * @param	string	$salt
	 * 		The new salt value.
	 * 
	 * @return	void
	 */
	private static function updateSessionSalt($salt = "")
	{
		// Update session salt
		session::set("salt", $salt, "account");
	}
	
	/**
	 * Logout the user.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Remove Active Session
		$sessionID = self::getSessionID();
		self::deleteActiveSession($sessionID);
		
		// Delete all account cookies
		cookies::delete("acc");
		cookies::delete("person");
		cookies::delete("mx");
		
		// Clear all session variables
		session::clear_set("account");
	}
	
	/**
	 * Switch from one account to another.
	 * 
	 * @param	integer	$accountID
	 * 		The account to switch to.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @return	boolean
	 * 		Returns TRUE on success and FALSE if the current account is locked and cannot switch.
	 */
	public static function switchAccount($accountID, $password = "")
	{
		// Check if it's a valid account
		if (!self::validate())
			return FALSE;
		
		// Check if current account is locked
		if (self::isLocked())
			return FALSE;
			
		// If is valid, and the account is not locked, switch account
		$username = person::getUsername();
		if (self::authenticate($username, $password, $accountID))
		{
			self::logout();
			return self::login($username, $password);
		}
		
		return FALSE;
	}
	
	/**
	 * Gets the current account's information.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @return	array
	 * 		Array of account data.
	 */
	private static function getInfo($accountID)
	{
		$dbc = new interDbConnection();
		$q = new dbQuery("177361907", "profile.account");
		$attr = array();
		$attr['id'] = $accountID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Gets the account info.
	 * 
	 * @return	array
	 * 		Returns an array of the account data.
	 */
	public static function info()
	{
		if (!self::validate())
			return NULL;
		
		return self::getInfo(self::getAccountID());
	}
	
	/**
	 * Updates the account's data to the session.
	 * 
	 * @param	mixed	$accountData
	 * 		The account data. If not given, it is loaded from the database.
	 * 
	 * @return	void
	 */
	private static function updateSessionData($accountData = NULL)
	{
		// Set session exist
		session::set("exists", TRUE, "account");
		
		// Get account info
		if (empty($accountData))
			$accountData = self::info();

		// Update session
		session::set("username", $accountData['username'], "account");
		session::set("firstname", $accountData['firstname'], "account");
		session::set("accountTitle", $accountData['accountTitle'], "account");
		session::set("lastname", $accountData['lastname'], "account");
		session::set("locked", $accountData['locked'], "account");
		session::set("company", $accountData['company_id'], "account");
		session::set("administrator", $accountData['administrator'], "account");
		
	}
	
	/**
	 * Gets the account id of the logged in person.
	 * 
	 * @return	string
	 * 		The account id.
	 */
	public static function getAccountID()
	{
		return cookies::get("acc");
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	string
	 * 		The account session id.
	 */
	public static function getSessionID()
	{
		$mix = cookies::get("mx");
		$mixArray = explode(":", $mix);
		return $mixArray[0];
	}
	
	/**
	 * Gets the person id of the logged in person.
	 * 
	 * @return	string
	 * 		The person id.
	 */
	public static function getPersonID()
	{
		return cookies::get("person");
	}
	
	/**
	 * Gets the user's username.
	 * 
	 * @return	string
	 * 		Returns the user's username.
	 * 
	 * @deprecated	Use person::getUsername() instead.
	 */
	public static function getUsername()
	{
		return self::getAccountValue("username");
	}
	
	/**
	 * Get the user's firstname.
	 * 
	 * @return	string
	 * 		Returns the user's firstname.
	 * 
	 * @deprecated	Use person::getFirstname() instead.
	 */
	public static function getFirstname()
	{
		return self::getAccountValue("firstname");
	}
	
	/**
	 * Get the user's firstname.
	 * 
	 * @return	string
	 * 		Returns the user's firstname.
	 * 
	 * @deprecated	Use person::getLastname() instead.
	 */
	public static function getLastname()
	{
		return self::getAccountValue("lastname");
	}
	
	/**
	 * Get the account's company.
	 * 
	 * @return	string
	 * 		Returns the account's company.
	 */
	public static function getCompany()
	{
		return self::getAccountValue("company");
	}
	
	/**
	 * Checks whether this account is locked.
	 * 
	 * @return	boolean
	 * 		True if locked, false otherwise.
	 */
	public static function isLocked()
	{
		return (self::getAccountValue("locked") == TRUE);
	}
	
	/**
	 * Checks whether the account is admin.
	 * 
	 * @return	boolean
	 * 		True if admin, false otherwise (shared).
	 */
	public static function isAdmin()
	{
		return (self::getAccountValue("administrator") == TRUE);
	}
	
	/**
	 * Gets the account title for the logged in account.
	 * 
	 * @return	string
	 * 		The account title.
	 */
	public static function getAccountTitle()
	{
		return self::getAccountValue("accountTitle");
	}
	
	/**
	 * Gets an account value from the session. If the session is not set yet, updates from the database.
	 * 
	 * @param	string	$name
	 * 		The value name.
	 * 
	 * @return	string
	 * 		The account value.
	 */
	private static function getAccountValue($name)
	{
		// Check session existance
		$exists = session::get("exists", FALSE, "account");
		if ($exists)
			return session::get($name, "", "account");
		
		// If there is no session, update session
		self::updateSessionData();
		
		// Return new value
		return self::getAccountValue($name);
	}
}
//#section_end#
?>