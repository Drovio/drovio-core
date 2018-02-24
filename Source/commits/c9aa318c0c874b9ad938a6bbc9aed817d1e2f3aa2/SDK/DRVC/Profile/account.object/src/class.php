<?php
//#section#[header]
// Namespace
namespace DRVC\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DRVC
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("ESS", "Environment", "session");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "accountSession");
importer::import("DRVC", "Profile", "person");

use \ESS\Environment\cookies;
use \ESS\Environment\session;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\accountSession;
use \DRVC\Profile\person;

/**
 * Account Identity
 * 
 * Manages an account identity
 * 
 * @version	4.1-1
 * @created	October 8, 2015, 15:03 (EEST)
 * @updated	October 16, 2015, 15:26 (EEST)
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
	 * The current account id.
	 * 
	 * @type	integer
	 */
	private static $accountID = NULL;
	
	/**
	 * The current person id (if any).
	 * 
	 * @type	integer
	 */
	private static $personID = NULL;
	
	/**
	 * The current session id.
	 * 
	 * @type	integer
	 */
	private static $sessionID = NULL;
	
	/**
	 * The current mx id.
	 * 
	 * @type	integer
	 */
	private static $mxID = NULL;
	
	/**
	 * All the stored account data.
	 * 
	 * @type	array
	 */
	private static $accountData = array();
	
	/**
	 * The session salt.
	 * 
	 * @type	string
	 */
	private static $salt = "";
	
	/**
	 * The team to access the identity database.
	 * 
	 * @type	string
	 */
	private static $teamName = "";
	
	/**
	 * Initialize the identity.
	 * 
	 * @param	string	$teamName
	 * 		The team name to access the identity database.
	 * 
	 * @return	void
	 */
	public static function init($teamName)
	{
		self::$teamName = $teamName;
	}
	
	/**
	 * Authenticates an account with the given username and password.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 		Email is also supported.
	 * 
	 * @param	string	$password
	 * 		The account's password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function authenticate($username, $password)
	{
		// Set Database Connection
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("26228169684124", "identity.account");
		
		// Authenticate account
		$attr = array();
		$attr['username'] = $username;
		$result = $dbc->execute($q, $attr);

		// If account not found, return FALSE
		if ($dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Get account info
		$accountInfo = $dbc->fetch($result);
		
		// Validate password hash
		$hash_256 = hash("SHA256", $password);
		return ($accountInfo['password'] == $hash_256 || password_verify($password, $accountInfo['password']));
	}
	
	/**
	 * Authenticates the account and creates an active account session.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 
	 * @param	string	$password
	 * 		The account password.
	 * 
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user or not.
	 * 		Duration: 1 month.
	 * 
	 * @return	boolean
	 * 		True on success, false on authentication failure.
	 */
	public static function login($username, $password, $rememberme = FALSE)
	{
		// Check if there is a user already logged in
		if (self::validate())
			return FALSE;
		
		// Authenticate user
		if (!self::authenticate($username, $password))
			return FALSE;
		
		// Get account info from username
		$accountInfo = self::getAccountByUsername($username, $includeEmail = TRUE, $fullList = FALSE);
		
		// Get Account Info
		self::$accountID = $accountInfo['id'];
		self::$personID = NULL;
		if (!$accountInfo['locked'])
			self::$personID = $accountInfo['person_id'];
			
		// Create salt
		$salt = hash("md5", time());
		
		// Create Account Session
		self::createSession(self::$accountID, $salt, self::$personID, $rememberme);
		
		// Update Session Account Data
		self::updateSessionData();
		
		return TRUE;
	}
	
	/**
	 * Validates if the user is logged in.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validate()
	{
		// Check loggedIn
		if (self::$loggedIn)
			return self::$loggedIn;

		// Get cookies
		self::$accountID = self::getAccountID();
		self::$mxID = self::getMX();
		self::$personID = self::getPersonID();

		// Check if the cookies exist
		if (empty(self::$accountID) || empty(self::$mxID))
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
		$genMix = accountSession::getSaltedMix(self::getSessionID(), self::$accountID, $salt, self::$personID);
		if (!($genMix === self::$mxID))
		{
			self::logout();
			return FALSE;
		}
		
		// Update session date modified and reset cookies
		self::updateSessionData();
		
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
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user in the database or not.
	 * 
	 * @return	void
	 */
	private static function createSession($accountID, $salt, $personID = "", $rememberme = FALSE)
	{
		// Create new Account Session
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("25218440938487", "identity.session");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['salt'] = $salt;
		$attr['ip'] = $_SERVER['REMOTE_ADDR'];
		$attr['date'] = time();
		$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$attr['rememberme'] = ($rememberme ? 1 : 0);
		$result = $dbc->execute($q, $attr);
		
		// Database error
		if (!$result)
			return FALSE;
			
		// Get session id
		$accountSession = $dbc->fetch($result);
		self::$sessionID = $accountSession['id'];
		
		// Get Salted mix
		$mx = accountSession::getSaltedMix(self::$sessionID, $accountID, $salt, $personID);
		self::$mxID = $mx;
		
		// Set Cookies (and duration for one month if rememberme)
		$duration = ($rememberme ? 30 * 24 * 60 * 60 : 0);
		cookies::set("__DRVID_ACC", $accountID, $duration, TRUE);
		cookies::set("__DRVID_PRS", $personID, $duration, TRUE);
		cookies::set("__DRVID_MX", $mx, $duration, TRUE);
		
		// COMPATIBILITY
		cookies::set("acc", $accountID, $duration, TRUE);
		cookies::set("person", $personID, $duration, TRUE);
		cookies::set("mx", $mx, $duration, TRUE);
		
		// Set static logged in
		self::$loggedIn = TRUE;
	}
	
	/**
	 * Gets the stored salt for the current account session.
	 * 
	 * @return	string
	 * 		The stored session salt.
	 */
	private static function getSalt()
	{
		// Get salt from variable
		if (!empty(self::$salt))
			return self::$salt;

		// Get salt from account session
		$sessionInfo = accountSession::info(self::getSessionID());

		// Check if the session is valid and exists, otherwise, return empty salt
		if (empty($sessionInfo))
			self::$salt = "";
		else
			self::$salt = $sessionInfo['salt'];
		
		// Return salt
		return self::$salt;
	}
	
	/**
	 * Logout the account from the system.
	 * Delete active session.
	 * Delete cookies.
	 * 
	 * @return	void
	 */
	public static function logout()
	{
		// Remove Active Session
		$sessionID = self::getSessionID();
		if (!empty($sessionID))
			accountSession::remove($sessionID);
		
		// Set class variables to null
		self::$accountID = NULL;
		self::$personID = NULL;
		self::$sessionID = NULL;
		self::$mxID = NULL;
		self::$loggedIn = FALSE;
		
		// Delete all account cookies
		cookies::remove("__DRVID_ACC");
		cookies::remove("__DRVID_PRS");
		cookies::remove("__DRVID_MX");
		
		// COMPATIBILITY
		cookies::remove("acc");
		cookies::remove("person");
		cookies::remove("mx");
	}
	
	/**
	 * Switch from one account to another.
	 * 
	 * @param	integer	$accountID
	 * 		The new account id to switch to.
	 * 
	 * @param	string	$password
	 * 		The new account's password.
	 * 
	 * @return	boolean
	 * 		Returns true on success and false if the current account is locked and cannot switch or the authentication fails.
	 */
	public static function switchAccount($accountID, $password)
	{
		// Check if it's a valid account
		if (!self::validate())
			return FALSE;
		
		// Check if current account is locked
		if (self::isLocked())
			return FALSE;
		
		// If is valid, and the account is not locked, switch account
		$accountInfo = self::info($accountID);
		$username = $accountInfo['username'];
		if (self::authenticate($username, $password, $accountID))
		{
			self::logout();
			return self::login($username, $password);
		}
		
		return FALSE;
	}
	
	/**
	 * Update current account password.
	 * 
	 * @param	string	$currentPassword
	 * 		The current account password.
	 * 
	 * @param	st	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updatePassword($currentPassword, $newPassword)
	{
		// Check if it's a valid account
		if (!self::validate())
			return FALSE;
		
		// Check if current account is locked
		if (self::isLocked())
			return FALSE;
			
		// If is valid, and the account is not locked, switch account
		$username = self::getUsername(TRUE);
		if (self::authenticate($username, $currentPassword))
		{
			// Update password in database
			$dbc = new dbConnection(self::$teamName);
			$q = new dbQuery("35676347294164", "identity.account");
			$attr = array();
			$attr['aid'] = self::getAccountID();
			$attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
			return $dbc->execute($q, $attr);
		}
		
		return FALSE;
	}
	
	/**
	 * Update the account's password using the reset id from the recovery process.
	 * 
	 * @param	string	$resetID
	 * 		The reset id hash token.
	 * 
	 * @param	string	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updatePasswordByReset($resetID, $newPassword)
	{
		// Get account by reset password id
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("2378186783046", "identity.account");
		$attr = array();
		$attr['reset'] = $resetID;
		$result = $dbc->execute($q, $attr);
		$accountInfo = $dbc->fetch($result);
			
		// If account is valid, update password
		if ($result && is_array($accountInfo))
		{
			// Update password in database
			$q = new dbQuery("35676347294164", "identity.account");
			$attr = array();
			$attr['aid'] = $accountInfo['id'];
			$attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
			return $dbc->execute($q, $attr);
		}
		
		return FALSE;
	}
	
	/**
	 * Updates the account's data to the session.
	 * 
	 * @return	void
	 */
	private static function updateSessionData()
	{
		// Get session data and check if cookies must be renewed.
		$sessionData = accountSession::info(self::getSessionID());
		
		// Get session data
		$lastAccess = $sessionData['lastAccess'];
		$rememberme = $sessionData['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if session needs to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
		{
			// Get session info
			$accountID = engine::getVar("__DRVID_ACC");
			$personID = engine::getVar("__DRVID_PRS");
			$mx = engine::getVar("__DRVID_MX");
			
			// COMPATIBILITY
			$accountID = (empty($accountID) ? engine::getVar("acc") : $accountID);
			$personID = (empty($personID) ? engine::getVar("person") : $personID);
			$mx = (empty($mx) ? engine::getVar("mx") : $mx);
			
			// Set Cookies again
			$duration = 30 * 24 * 60 * 60;
			cookies::set("__DRVID_ACC", $accountID, $duration, TRUE);
			cookies::set("__DRVID_PRS", $personID, $duration, TRUE);
			cookies::set("__DRVID_MX", $mx, $duration, TRUE);
			
			// COMPATIBILITY
			cookies::set("acc", $accountID, $duration, TRUE);
			cookies::set("person", $personID, $duration, TRUE);
			cookies::set("mx", $mx, $duration, TRUE);
			
			// Update session in database
			$dbc = new dbConnection(self::$teamName);
			$q = new dbQuery("23782932225255", "identity.session");
			$attr = array();
			$attr['aid'] = self::getAccountID();
			$attr['sid'] = self::getSessionID();
			$attr['ip'] = $_SERVER['REMOTE_ADDR'];
			$attr['date'] = $currentTime;
			$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			$attr['rememberme'] = 1;
			$dbc->execute($q, $attr);
		}
		
	}
	
	/**
	 * Gets whether the system remembers the logged in account.
	 * 
	 * @return	boolean
	 * 		True if the system remembers the account, false otherwise.
	 */
	public static function rememberme()
	{
		// Get session data
		$sessionData = accountSession::info(self::getSessionID());
		
		// Return remember me value
		return $sessionData['rememberme'];
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public static function getAccountID()
	{
		if (isset(self::$accountID))
			return self::$accountID;

		$accountID = engine::getVar("__DRVID_ACC");
		$accountID = (empty($accountID) ? engine::getVar("acc") : $accountID);
		if ($accountID == 'deleted')
			return NULL;
		
		return $accountID;
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	string
	 * 		The current mx id.
	 */
	public static function getMX()
	{
		if (isset(self::$mxID))
			return self::$mxID;

		$mx = engine::getVar("__DRVID_MX");
		$mx = (empty($mx) ? engine::getVar("mx") : $mx);
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	string
	 * 		The account session id.
	 */
	public static function getSessionID()
	{
		if (isset(self::$sessionID))
			return self::$sessionID;
			
		$mix = self::getMX();
		$mixArray = explode(":", $mix);
		return $mixArray[0];
	}
	
	/**
	 * Gets the person id of the logged in account.
	 * 
	 * @return	integer
	 * 		The person id.
	 */
	public static function getPersonID()
	{
		if (isset(self::$personID))
			return self::$personID;
		
		$personID = engine::getVar("__DRVID_PRS");
		$personID = (empty($personID) ? engine::getVar("person") : $personID);
		if ($personID == 'deleted')
			return NULL;
		
		return $personID;
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
	 * 		The account display title.
	 */
	public static function getAccountTitle()
	{
		return self::getAccountValue("title");
	}
	
	/**
	 * Get the account's username.
	 * 
	 * @param	boolean	$emailFallback
	 * 		Set TRUE to return the connected person's email if the account's username is empty.
	 * 		It is FALSE by default.
	 * 
	 * @return	mixed
	 * 		Return the account username.
	 * 		If account doesn't have username, return the email of the person connected to this account.
	 * 		If there is no connected account, return NULL.
	 */
	public static function getUsername($emailFallback = FALSE)
	{
		// Get account username
		$username = self::getAccountValue("username");
		
		// If username is empty and email fallback is active,
		// get person's mail
		if (empty($username) && $emailFallback)
			$username = person::getMail();
		
		// Return username
		return $username;
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
		if (!isset(self::$accountData[$name]))
			self::$accountData = self::info();
			
		return self::$accountData[$name];
	}
	
	/**
	 * Gets the account info.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the information for.
	 * 		Leave empty for the current account.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		Returns an array of the account information.
	 */
	public static function info($accountID = "")
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("34485211600886", "identity.account");
		$attr = array();
		$attr['id'] = (empty($accountID) ? self::getAccountID() : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Update account information.
	 * 
	 * @param	string	$title
	 * 		The account title.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($title)
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("24016942594207", "identity.account");
		$attr = array();
		$attr['aid'] = self::getAccountID();
		$attr['title'] = $title;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Update the account's username.
	 * 
	 * @param	string	$username
	 * 		The new account username.
	 * 
	 * @param	{type}	$accountID
	 * 		{description}
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateUsername($username, $accountID = "")
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("22262988258939", "identity.account");
		$attr = array();
		$attr['aid'] = (empty($accountID) ? self::getAccountID() : $accountID);
		$attr['username'] = $username;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Create a new account into the identity system.
	 * (Registration)
	 * This process will create a person and a connected account.
	 * 
	 * @param	string	$email
	 * 		The person's email.
	 * 
	 * @param	string	$firstname
	 * 		The person's firstname.
	 * 
	 * @param	string	$lastname
	 * 		The person's lastname.
	 * 
	 * @param	string	$password
	 * 		The account password.
	 * 
	 * @return	boolean
	 * 		The account id created on success, false on failure.
	 */
	public static function create($email, $firstname = "", $lastname = "", $password = "")
	{
		// Register account
		$dbc = new dbConnection(self::$teamName);
		$dbq = new dbQuery("26765983177913", "identity.account");
		$attr = array();
		$attr["firstname"] = trim($firstname);
		$attr["lastname"] = trim($lastname);
		$attr["password"] = (empty($password) ? "NULL" : password_hash($password, PASSWORD_BCRYPT));
		$attr['title'] = trim($firstname." ".$lastname);
		$attr["email"] = trim($email);
		$attr['username'] = trim(explode("@", $email, 2)[0].rand().time());
		$result = $dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// Get account id
		$accountInfo = $dbc->fetch($result);
		return $accountInfo['id'];
	}
	
	/**
	 * Get all team accounts.
	 * 
	 * @return	array
	 * 		An array of all team accounts.
	 */
	public static function getAllAccounts()
	{
		$dbc = new dbConnection(self::$teamName);
		$dbq = new dbQuery("14794507272004", "identity.account");
		$result = $dbc->execute($dbq);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get the number of accounts in the database.
	 * 
	 * @return	integer
	 * 		The number of accouns in the identity database.
	 */
	public static function getAccountsCount()
	{
		$dbc = new dbConnection(self::$teamName);
		$dbq = new dbQuery("25607866701287", "identity.account");
		$result = $dbc->execute($dbq);
		$countInfo = $dbc->fetch($result);
		return $countInfo['acc_count'];
	}
	
	/**
	 * Get an account (or a list of them) by username.
	 * 
	 * @param	string	$username
	 * 		The username to search.
	 * 
	 * @param	boolean	$includeEmail
	 * 		If set to true, search for person emails also.
	 * 		It is FALSE by default.
	 * 
	 * @param	boolean	$fullList
	 * 		If true, return a full list (if available) instead of only the first result.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		Array of accounts or account information in array.
	 */
	public static function getAccountByUsername($username, $includeEmail = FALSE, $fullList = FALSE)
	{
		$dbc = new dbConnection(self::$teamName);
		$dbq = new dbQuery("18866259871831", "identity.account");
		$attr = array();
		$attr["username"] = trim($username);
		$attr["wmail"] = ($includeEmail ? 1 : 0);
		$result = $dbc->execute($dbq, $attr);
		return $dbc->fetch($result, $fullList);
	}
}
//#section_end#
?>