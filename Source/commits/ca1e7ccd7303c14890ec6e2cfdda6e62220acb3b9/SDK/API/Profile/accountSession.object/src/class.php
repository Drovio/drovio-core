<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("DRVC", "Profile", "accountSession");

use \API\Profile\account;
use \DRVC\Profile\accountSession as IDAccountSession;

/**
 * Account session manager for drovio users.
 * 
 * It extends the drovio identity account session to manage sessions for the drovio platform.
 * 
 * @version	2.0-1
 * @created	October 29, 2015, 23:45 (GMT)
 * @updated	November 11, 2015, 13:28 (GMT)
 */
class accountSession extends IDAccountSession
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
	/**
	 * The platform accountSession instance.
	 * 
	 * @type	accountSession
	 */
	private static $instance;
	
	/**
	 * Get the accountSession instance.
	 * 
	 * @param	string	$mxID
	 * 		The account's mx id.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	public static function getInstance($mxID = "")
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new accountSession($mxID);
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new accountSession instance for the platform identity.
	 * 
	 * @param	string	$mxID
	 * 		The account's mx id.
	 * 
	 * @return	void
	 */
	protected function __construct($mxID = "")
	{
		parent::__construct(self::ID_TEAM_NAME, $mxID);
	}
	
	/**
	 * Creates a new account session.
	 * It works only for the current account.
	 * 
	 * @param	string	$salt
	 * 		The logged in generated salt.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		It will be ignored and get the current account id instead.
	 * 
	 * @param	integer	$personID
	 * 		The person id (if any).
	 * 		It will be ignored and get the current person id instead.
	 * 
	 * @param	boolean	$rememberme
	 * 		Whether to remember the user in the database or not.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($salt, $accountID, $personID = "", $rememberme = FALSE)
	{
		// Get current account id and person id
		$accountID = account::getInstance()->getAccountID();
		$personID = account::getInstance()->getPersonID();
		
		// Create session
		return parent::create($salt, $accountID, $personID, $rememberme);
	}
	
	/**
	 * Updates the current account's data to the session.
	 * 
	 * @return	void
	 */
	public function update()
	{
		// Get current account id
		$accountID = account::getInstance()->getAccountID();
		
		// Update session
		return parent::update($accountID);
	}
	
	/**
	 * Get all current session data from the database for the current account.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		It will be ignored and get the current account id instead.
	 * 
	 * @param	integer	$sessionID
	 * 		The session id.
	 * 		Leave empty for current session.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all session data.
	 */
	public function info($accountID, $sessionID = "")
	{
		// Get current account id
		$accountID = account::getInstance()->getAccountID();
		
		// Get session info
		return parent::info($accountID, $sessionID);
	}
	
	/**
	 * Deletes a given account session.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 		It will be ignored and get the current account id instead.
	 * 
	 * @param	integer	$sessionID
	 * 		The session id.
	 * 		Leave empty for current session.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($accountID, $sessionID = "")
	{
		// Get current account id
		$accountID = account::getInstance()->getAccountID();
		
		// Remove the session
		return parent::remove($accountID, $sessionID);
	}
	
	/**
	 * Gets the stored salt for the current account session.
	 * 
	 * @return	string
	 * 		The stored session salt.
	 */
	public function getSalt()
	{
		// Get current account id
		$accountID = account::getInstance()->getAccountID();
		
		// Get session salt
		return parent::getSalt($accountID);
	}
	
	/**
	 * Get all active sessions of the current account.
	 * 
	 * @return	array
	 * 		An array of all active sessions' details.
	 */
	public function getActiveSessions()
	{
		// Get current account id
		$accountID = account::getInstance()->getAccountID();
		
		// Get current account active sessions
		return parent::getActiveSessions($accountID);
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	string
	 * 		The current mx id.
	 */
	public function getMX()
	{
		// Get parent mx
		$mx = parent::getMX();
		if (!empty($mx))
			return $mx;

		// Get value from cookies
		$mx = engine::getVar("__DRVID_MX");
		$mx = (empty($mx) ? engine::getVar("mx") : $mx);
		if ($mx == 'deleted')
			return NULL;
		
		return $mx;
	}
}
//#section_end#
?>