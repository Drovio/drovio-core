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
 * @version	6.0-1
 * @created	October 8, 2015, 15:03 (EEST)
 * @updated	October 18, 2015, 20:41 (EEST)
 */
class account
{
	/**
	 * Indicates whether the account is logged in for this run.
	 * 
	 * @type	boolean
	 */
	private $loggedIn = FALSE;
	
	/**
	 * The current account id.
	 * 
	 * @type	integer
	 */
	private $accountID = NULL;
	
	/**
	 * The current person id (if any).
	 * 
	 * @type	integer
	 */
	private $personID = NULL;
	
	/**
	 * The current session id.
	 * 
	 * @type	integer
	 */
	private $sessionID = NULL;
	
	/**
	 * The current mx id.
	 * 
	 * @type	integer
	 */
	private $mxID = NULL;
	
	/**
	 * All the stored account data.
	 * 
	 * @type	array
	 */
	private $accountData = array();
	
	/**
	 * The session salt.
	 * 
	 * @type	string
	 */
	private $salt = "";
	
	/**
	 * The team to access the identity database.
	 * 
	 * @type	string
	 */
	protected $teamName = "";
	
	/**
	 * The identity database connection.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * An array of instances for each team identity (in case of multiple instances).
	 * 
	 * @type	array
	 */
	private static $instances = array();
	
	/**
	 * Static team name for compatibility.
	 * 
	 * @type	string
	 */
	private static $staticTeamName = "";
	
	/**
	 * Get an identity account instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	account
	 * 		The account instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new account($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Create a new account instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName)
	{
		$this->teamName = $teamName;
		$this->dbc = new dbConnection($this->teamName);
	}
	
	/**
	 * Initialize the identity.
	 * 
	 * @param	string	$teamName
	 * 		The team name to access the identity database.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getInstance() to get the singleton.
	 */
	public static function init($teamName)
	{
		self::$staticTeamName = $teamName;
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
	public function authenticate($username, $password)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->authenticate($username, $password);
		
		// Set Database Connection
		$q = new dbQuery("26228169684124", "identity.account");
		
		// Authenticate account
		$attr = array();
		$attr['username'] = $username;
		$result = $this->dbc->execute($q, $attr);

		// If account not found, return FALSE
		if ($this->dbc->get_num_rows($result) == 0)
			return FALSE;
		
		// Get account info
		$accountInfo = $this->dbc->fetch($result);
		
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
	public function login($username, $password, $rememberme = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->login($username, $password, $rememberme);
		
		// Check if there is a user already logged in
		if ($this->validate())
			return FALSE;
		
		// Authenticate user
		if (!$this->authenticate($username, $password))
			return FALSE;
		
		// Get account info from username
		$accountInfo = $this->getAccountByUsername($username, $includeEmail = TRUE, $fullList = FALSE);
		
		// Get Account Info
		$this->accountID = $accountInfo['id'];
		$this->personID = NULL;
		if (!$accountInfo['locked'])
			$this->personID = $accountInfo['person_id'];
			
		// Create salt
		$salt = hash("md5", time());
		
		// Create Account Session
		$this->getAccountSessionInstance()->create($this->accountID, $salt, $this->personID, $rememberme);
		$this->sessionID = $this->getAccountSessionInstance()->getSessionID();
		
		// Update Session Account Data
		$this->updateSessionData();
		
		return TRUE;
	}
	
	/**
	 * Validates if the user is logged in.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function validate()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->validate();
		
		// Check loggedIn
		if ($this->loggedIn)
			return $this->loggedIn;

		// Get cookies
		$this->accountID = $this->getAccountID();
		$this->mxID = $this->getMX();
		$this->personID = $this->getPersonID();

		// Check if the cookies exist
		if (empty($this->accountID) || empty($this->mxID))
		{
			$this->logout();
			return FALSE;
		}

		// Get salt from account
		$salt = $this->getAccountSessionInstance()->getSalt();

		// If salt is empty, return FALSE
		if (empty($salt))
		{
			$this->logout();
			return FALSE;
		}
		
		// Check mix
		$genMix = accountSession::getSaltedMix($this->getSessionID(), $this->accountID, $salt, $this->personID);
		if (!($genMix === $this->mxID))
		{
			$this->logout();
			return FALSE;
		}
		
		// Update account session
		$this->getAccountSessionInstance()->update();
		
		// Return valid status
		$this->loggedIn = TRUE;
		return TRUE;
	}
	
	/**
	 * Logout the account from the system.
	 * Delete active session.
	 * Delete cookies.
	 * 
	 * @return	void
	 */
	public function logout()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->logout();
		
		// Remove Active Session
		$sessionID = $this->getSessionID();
		if (!empty($sessionID))
			$this->getAccountSessionInstance()->remove($sessionID);
		
		// Set class variables to null
		$this->accountID = NULL;
		$this->personID = NULL;
		$this->sessionID = NULL;
		$this->mxID = NULL;
		$this->loggedIn = FALSE;
		
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
	public function switchAccount($accountID, $password)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->switchAccount($accountID, $password);
		
		// Check if it's a valid account
		if (!$this->validate())
			return FALSE;
		
		// Check if current account is locked
		if ($this->isLocked())
			return FALSE;
		
		// If is valid, and the account is not locked, switch account
		$accountInfo = $this->info($accountID);
		$username = $accountInfo['username'];
		if ($this->authenticate($username, $password, $accountID))
		{
			$this->logout();
			return $this->login($username, $password);
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
	public function updatePassword($currentPassword, $newPassword)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->updatePassword($currentPassword, $newPassword);
		
		// Check if it's a valid account
		if (!$this->validate())
			return FALSE;
		
		// Check if current account is locked
		if ($this->isLocked())
			return FALSE;
			
		// If is valid, and the account is not locked, switch account
		$username = $this->getUsername(TRUE);
		if ($this->authenticate($username, $currentPassword))
		{
			// Update password in database
			$q = new dbQuery("35676347294164", "identity.account");
			$attr = array();
			$attr['aid'] = $this->getAccountID();
			$attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
			return $this->dbc->execute($q, $attr);
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
	public function updatePasswordByReset($resetID, $newPassword)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->updatePasswordByReset($resetID, $newPassword);
		
		// Get account by reset password id
		$q = new dbQuery("2378186783046", "identity.account");
		$attr = array();
		$attr['reset'] = $resetID;
		$result = $this->dbc->execute($q, $attr);
		$accountInfo = $this->dbc->fetch($result);
			
		// If account is valid, update password
		if ($result && is_array($accountInfo))
		{
			// Update password in database
			$q = new dbQuery("35676347294164", "identity.account");
			$attr = array();
			$attr['aid'] = $accountInfo['id'];
			$attr['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
			return $this->dbc->execute($q, $attr);
		}
		
		return FALSE;
	}
	
	/**
	 * Generate a reset id token for the current account.
	 * 
	 * @param	integer	$aid
	 * 		The account id to generate the token for.
	 * 
	 * @return	mixed
	 * 		The generated token on success, false on failure.
	 */
	public function generateResetId($aid)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->generateResetId($aid);
		
		// Generate resetID.
		$resetID = md5($aid.time());
		
		// Put reset in the database.
		$q = new dbQuery("18841240149912", "identity.account");
		$attr = array();
		$attr['new_reset'] = $resetID;
		$attr['aid'] = $aid;
		$result = $this->dbc->execute($q, $attr);
		
		if ($result)
		{
			// Update password in database
			return $resetID;
		}
		
		return $result;
	}
	
	/**
	 * Gets whether the system remembers the logged in account.
	 * 
	 * @return	boolean
	 * 		True if the system remembers the account, false otherwise.
	 */
	public function rememberme()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->rememberme();
		
		// Get session data
		$sessionData = $this->getAccountSessionInstance()->info($this->getSessionID());
		
		// Return remember me value
		return $sessionData['rememberme'];
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public function getAccountID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getAccountID();
		
		if (isset($this->accountID))
			return $this->accountID;

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
	public function getMX()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getMX();
		
		if (isset($this->mxID))
			return $this->mxID;

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
	public function getSessionID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getSessionID();
		
		if (isset($this->sessionID))
			return $this->sessionID;
			
		$mix = $this->getMX();
		$mixArray = explode(":", $mix);
		return $mixArray[0];
	}
	
	/**
	 * Gets the person id of the logged in account.
	 * 
	 * @return	integer
	 * 		The person id.
	 */
	public function getPersonID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getPersonID();
		
		if (isset($this->personID))
			return $this->personID;
		
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
	public function isLocked()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->isLocked();
		
		return ($this->getAccountValue("locked") == TRUE);
	}
	
	/**
	 * Checks whether the account is admin.
	 * 
	 * @return	boolean
	 * 		True if admin, false otherwise (shared).
	 */
	public function isAdmin()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->isAdmin();
		
		return ($this->getAccountValue("administrator") == TRUE);
	}
	
	/**
	 * Gets the account title for the logged in account.
	 * 
	 * @return	string
	 * 		The account display title.
	 */
	public function getAccountTitle()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getAccountTitle();
		
		return $this->getAccountValue("title");
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
	public function getUsername($emailFallback = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getUsername($emailFallback);
		
		// Get account username
		$username = $this->getAccountValue("username");
		
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
	private function getAccountValue($name)
	{
		// Check session existance
		if (!isset($this->accountData[$name]))
			$this->accountData = $this->info();
			
		return $this->accountData[$name];
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
	public function info($accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->info($accountID);
		
		// Get account info
		$q = new dbQuery("34485211600886", "identity.account");
		$attr = array();
		$attr['id'] = (empty($accountID) ? $this->getAccountID() : $accountID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
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
	public function updateInfo($title)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->updateInfo($title);
		
		// Update account info
		$q = new dbQuery("24016942594207", "identity.account");
		$attr = array();
		$attr['aid'] = $this->getAccountID();
		$attr['title'] = $title;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
	}
	
	/**
	 * Update the account's username.
	 * 
	 * @param	string	$username
	 * 		The new account username.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to update the username.
	 * 		Leave empty for current account.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateUsername($username, $accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->updateUsername($username, $accountID);
		
		// Update account username
		$q = new dbQuery("22262988258939", "identity.account");
		$attr = array();
		$attr['aid'] = (empty($accountID) ? $this->getAccountID() : $accountID);
		$attr['username'] = $username;
		return $this->dbc->execute($q, $attr);
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
	public function create($email, $firstname = "", $lastname = "", $password = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->create($email, $firstname, $lastname, $password);
		
		// Create new account
		$dbq = new dbQuery("26765983177913", "identity.account");
		$attr = array();
		$attr["firstname"] = trim($firstname);
		$attr["lastname"] = trim($lastname);
		$attr["password"] = (empty($password) ? "NULL" : password_hash($password, PASSWORD_BCRYPT));
		$attr['title'] = trim($firstname." ".$lastname);
		$attr["email"] = trim($email);
		$attr['username'] = trim(explode("@", $email, 2)[0].rand().time());
		$result = $this->dbc->execute($dbq, $attr);
		if (!$result)
			return FALSE;
		
		// Get account id
		$accountInfo = $this->dbc->fetch($result);
		return $accountInfo['id'];
	}
	
	/**
	 * Get all team accounts.
	 * 
	 * @return	array
	 * 		An array of all team accounts.
	 */
	public function getAllAccounts()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getAllAccounts();
		
		// Get all accounts
		$dbq = new dbQuery("14794507272004", "identity.account");
		$result = $this->dbc->execute($dbq);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get the number of accounts in the database.
	 * 
	 * @return	integer
	 * 		The number of accouns in the identity database.
	 */
	public function getAccountsCount()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getAccountsCount();
		
		// Get accounts count
		$dbq = new dbQuery("25607866701287", "identity.account");
		$result = $this->dbc->execute($dbq);
		$countInfo = $this->dbc->fetch($result);
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
	public function getAccountByUsername($username, $includeEmail = FALSE, $fullList = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return account::getInstance(self::$staticTeamName)->getAccountByUsername($username, $includeEmail, $fullList);
		
		// Get account by username
		$dbq = new dbQuery("18866259871831", "identity.account");
		$attr = array();
		$attr["username"] = trim($username);
		$attr["wmail"] = ($includeEmail ? 1 : 0);
		$result = $this->dbc->execute($dbq, $attr);
		return $this->dbc->fetch($result, $fullList);
	}
	
	/**
	 * Get an accountSession instance for the current account.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	private function getAccountSessionInstance()
	{
		return accountSession::getInstance($this->teamName, $this->getMX());
	}
}
//#section_end#
?>