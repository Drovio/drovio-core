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

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "account");

use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\account;

/**
 * Identity Account Session
 * 
 * Singleton class to manage account session for the given team identity
 * 
 * @version	1.0-3
 * @created	October 8, 2015, 13:11 (BST)
 * @updated	October 21, 2015, 16:05 (BST)
 */
class accountSession
{
	/**
	 * The current session id.
	 * 
	 * @type	string
	 */
	private $sessionID = NULL;
	
	/**
	 * The current mx id.
	 * 
	 * @type	string
	 */
	private $mxID = NULL;
	
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
	private $teamName = "";
	
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
	 * Static mx id for compatibility.
	 * 
	 * @type	string
	 */
	private static $staticMxID = "";
	
	/**
	 * Get an accountSession instance for the given attributes.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @param	string	$mxID
	 * 		The current account's mx id.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	public static function getInstance($teamName, $mxID = "")
	{
		// Check for an existing instance
		$instanceIdentifier = $teamName."_".$mxID;
		if (!isset(self::$instances[$instanceIdentifier]))
			self::$instances[$instanceIdentifier] = new accountSession($teamName, $mxID);
		
		// Return instance
		return self::$instances[$instanceIdentifier];
	}
	
	/**
	 * Create a new accountSession instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name.
	 * 
	 * @param	string	$mxID
	 * 		The account's mx id.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName, $mxID)
	{
		// Initialize basics
		$this->teamName = $teamName;
		$this->mxID = $mxID;
	 	$this->dbc = new dbConnection($this->teamName);
		
		// Get session id from mix
		$mixArray = explode(":", $this->mxID);
		$this->sessionID = $mixArray[0];
	}
	
	/**
	 * Initialize the identity database and the session.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @param	string	$mxID
	 * 		The current mx.
	 * 
	 * @return	void
	 * 
	 * @deprecated	Use getInstance() to get the singleton.
	 */
	public static function init($teamName, $mxID)
	{
		// Set team name
		self::$staticTeamName = $teamName;
		
		// Initialize mix
		self::$staticMxID = $mxID;
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
	 * @return	boolean
	 * 		{description}
	 */
	public function create($accountID, $salt, $personID = "", $rememberme = FALSE)
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->create($accountID, $salt, $personID, $rememberme);
		
		// Create new Account Session
		$q = new dbQuery("25218440938487", "identity.session");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['salt'] = $salt;
		$attr['ip'] = $_SERVER['REMOTE_ADDR'];
		$attr['date'] = time();
		$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		$attr['rememberme'] = ($rememberme ? 1 : 0);
		$result = $this->dbc->execute($q, $attr);
		
		// Database error
		if (!$result)
			return FALSE;
			
		// Get session id
		$accountSession = $this->dbc->fetch($result);
		$this->sessionID = $accountSession['id'];
		
		// Get Salted mix
		$this->mxID = $this->getSaltedMix($this->sessionID, $accountID, $salt, $personID);
		
		// Return success
		return TRUE;
	}
	
	/**
	 * Updates the account's data to the session.
	 * 
	 * @return	void
	 */
	public function update()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->update();
		
		// Get session data and check if cookies must be renewed.
		$sessionData = $this->info();
		
		// Get session data
		$lastAccess = $sessionData['lastAccess'];
		$rememberme = $sessionData['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if session needs to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
		{
			// Update session in database
			$q = new dbQuery("23782932225255", "identity.session");
			$attr = array();
			$attr['aid'] = account::getInstance($this->teamName)->getAccountID();
			$attr['sid'] = $this->getSessionID();
			$attr['ip'] = $_SERVER['REMOTE_ADDR'];
			$attr['date'] = $currentTime;
			$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			$attr['rememberme'] = 1;
			$this->dbc->execute($q, $attr);
		}
	}
	
	/**
	 * Get all current session data from the database for the current account.
	 * 
	 * @param	integer	$sessionID
	 * 		The session id.
	 * 		Leave empty for current session.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all session data.
	 */
	public function info($sessionID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->info($sessionID);
		
		// Get session info
		$q = new dbQuery("35604398472519", "identity.session");
		$attr = array();
		$attr['aid'] = account::getInstance($this->teamName)->getAccountID();
		$attr['sid'] = (empty($sessionID) ? $this->getSessionID() : $sessionID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
	}
	
	/**
	 * Deletes a given account session.
	 * 
	 * @param	integer	$sessionID
	 * 		The session id.
	 * 		Leave empty for current session.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($sessionID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->remove($sessionID);
		
		// Remove account session
		$q = new dbQuery("30008014581206", "identity.session");
		$attr = array();
		$attr['sid'] = (empty($sessionID) ? $this->getSessionID() : $sessionID);
		$attr['aid'] = account::getInstance($this->teamName)->getAccountID();
		if (empty($attr['sid']) || empty($attr['aid']))
			return FALSE;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Returns the salted mix of account, person and salt.
	 * 
	 * @param	integer	$sessionID
	 * 		The session id.
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
	public static function getSaltedMix($sessionID, $accountID, $salt, $personID = "")
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
	 * 		The stored session salt.
	 */
	public function getSalt()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->getSalt();
		
		// Get salt from variable
		if (!empty($this->salt))
			return $this->salt;
		
		// Get salt from account session
		$sessionInfo = $this->info();
		
		// Check if the session is valid and exists, otherwise, return empty salt
		if (empty($sessionInfo))
			$this->salt = "";
		else
			$this->salt = $sessionInfo['salt'];
		
		// Return salt
		return $this->salt;
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	integer
	 * 		The account session id.
	 */
	public function getSessionID()
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->getSessionID();
		
		if (isset($this->sessionID))
			return $this->sessionID;
			
		$mix = $this->getMX();
		$mixArray = explode(":", $mix);
		return $mixArray[0];
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
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->getMX();
		
		if (isset($this->mxID))
			return $this->mxID;

		$mx = engine::getVar("__DRVID_MX");
		$mx = (empty($mx) ? engine::getVar("mx") : $mx);
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
	}
	
	/**
	 * Get all active sessions of the given account.
	 * 
	 * @param	integer	$accountID
	 * 		The account to get the active sessions for.
	 * 		If empty, get the current account.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		{description}
	 */
	public function getActiveSessions($accountID = "")
	{
		// STATIC COMPATIBILITY CHECK
		if (!(isset($this) && get_class($this) == __CLASS__))
			return accountSession::getInstance(self::$staticTeamName, self::$staticMxID)->getActiveSessions($accountID);
		
		// Remove active session
		$q = new dbQuery("27779190319041", "identity.session");
		$attr = array();
		$attr['aid'] = (empty($accountID) ? account::getInstance($this->teamName)->getAccountID() : $accountID);
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>