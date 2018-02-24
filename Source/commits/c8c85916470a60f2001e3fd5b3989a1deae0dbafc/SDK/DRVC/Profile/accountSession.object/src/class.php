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
 * Manages account sessions
 * 
 * @version	0.1-2
 * @created	October 8, 2015, 15:11 (EEST)
 * @updated	October 11, 2015, 18:30 (EEST)
 */
class accountSession
{
	/**
	 * The current session id.
	 * 
	 * @type	string
	 */
	private static $sessionID = NULL;
	
	/**
	 * The current mx id.
	 * 
	 * @type	string
	 */
	private static $mxID = NULL;
	
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
	 * Initialize the identity database and the session.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @param	string	$mxID
	 * 		The current mx.
	 * 
	 * @return	void
	 */
	public static function init($teamName, $mxID)
	{
		// Set team name
		self::$teamName = $teamName;
		
		// Initialize mix
		self::$mxID = $mxID;
		
		// Get session id from mix
		$mixArray = explode(":", self::$mxID);
		self::$sessionID = $mixArray[0];
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
	public static function create($accountID, $salt, $personID = "", $rememberme = FALSE)
	{
		// Create new Account Session
		$dbc = new dbConnection();
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
		self::$mxID = self::getSaltedMix(self::$sessionID, $accountID, $salt, $personID);
		
		// Return success
		return TRUE;
	}
	
	/**
	 * Updates the account's data to the session.
	 * 
	 * @return	void
	 */
	public static function update()
	{
		// Get session data and check if cookies must be renewed.
		$sessionData = self::info();
		
		// Get session data
		$lastAccess = $sessionData['lastAccess'];
		$rememberme = $sessionData['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if session needs to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
		{
			// Update session in database
			$dbc = new dbConnection();
			$q = new dbQuery("23782932225255", "identity.session");
			$attr = array();
			$attr['aid'] = account::getAccountID();
			$attr['sid'] = self::getSessionID();
			$attr['ip'] = $_SERVER['REMOTE_ADDR'];
			$attr['date'] = $currentTime;
			$attr['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			$attr['rememberme'] = 1;
			$dbc->execute($q, $attr);
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
	public static function info($sessionID = "")
	{
		$dbc = new dbConnection();
		$q = new dbQuery("35604398472519", "identity.session");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$attr['sid'] = (empty($sessionID) ? self::getSessionID() : $sessionID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
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
	public static function remove($sessionID = "")
	{
		$dbc = new dbConnection();
		$q = new dbQuery("30008014581206", "identity.session");
		$attr = array();
		$attr['sid'] = (empty($sessionID) ? self::getSessionID() : $sessionID);
		$attr['aid'] = account::getAccountID();
		if (empty($attr['sid']) || empty($attr['aid']))
			return FALSE;
		return $dbc->execute($q, $attr);
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
	public static function getSalt()
	{
		// Get salt from variable
		if (!empty(self::$salt))
			return self::$salt;
		
		// Get salt from account session
		$sessionInfo = self::info();
		
		// Check if the session is valid and exists, otherwise, return empty salt
		if (empty($sessionInfo))
			self::$salt = "";
		else
			self::$salt = $sessionInfo['salt'];
		
		// Return salt
		return self::$salt;
	}
	
	/**
	 * Gets the account session id.
	 * 
	 * @return	integer
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
	public static function getActiveSessions($accountID = "")
	{
		// Remove active session
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("27779190319041", "identity.session");
		$attr = array();
		$attr['aid'] = (empty($accountID) ? account::getAccountID() : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>