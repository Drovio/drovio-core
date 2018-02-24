<?php
//#section#[header]
// Namespace
namespace API\Login;

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
 * @package	Login
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "cookies");
importer::import("API", "Login", "accountSession");
importer::import("API", "Platform", "engine");
importer::import("DRVC", "Profile", "account");

use \ESS\Environment\cookies;
use \API\Login\accountSession;
use \API\Platform\engine;
use \DRVC\Profile\account as IDAccount;

/**
 * Drovio Login Account Interface
 * 
 * Manages the account login for the drovio platform using the drovio identity.
 * 
 * @version	0.1-2
 * @created	November 11, 2015, 18:44 (GMT)
 * @updated	November 11, 2015, 20:02 (GMT)
 */
class account extends IDAccount
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
	/**
	 * The platform account instance.
	 * 
	 * @type	account
	 */
	private static $instance;
	
	/**
	 * Get an account instance.
	 * 
	 * @return	account
	 * 		The account instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new account();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new account instance for the platform identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(self::ID_TEAM_NAME);
	}
	
	/**
	 * Login the account using the drovio identity.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 		It supports the account email too.
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
		// Login account id
		$status = parent::login($username, $password, $rememberme);
		if ($status)
			$this->setCookies($this->getAccountID(), $this->getPersonID(), $this->getMX(), $rememberme = TRUE);
		
		return $status;
	}
	
	/**
	 * Update the current account session and renew cookies if necessary.
	 * 
	 * @return	void
	 */
	public function updateSession()
	{
		// Update session
		parent::updateSession();
		
		// Set session info
		$sessionInfo = parent::info($this->getAccountID());
		$lastAccess = $sessionInfo['lastAccess'];
		$rememberme = $sessionInfo['rememberme'];
		
		// Get current time
		$currentTime = time();
		
		// Check if cookies need to be renewed
		if ($rememberme && $currentTime - $lastAccess > 7 * 24 * 60 * 60)
			$this->setCookies($this->getAccountID(), $this->getPersonID(), $this->getMX(), $rememberme = TRUE);
	}
	
	/**
	 * Set the proper cookies for the current account session.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$personID
	 * 		The person id.
	 * 
	 * @param	string	$mxID
	 * 		The mx id.
	 * 
	 * @param	boolean	$rememberme
	 * 		Set the cookie duration to session or longer.
	 * 
	 * @return	void
	 */
	public function setCookies($accountID, $personID, $mxID, $rememberme = FALSE)
	{
		// Set cookies
		$duration = ($rememberme ? 30 * 24 * 60 * 60 : 0);
		cookies::set("__DRVID_ACC", $accountID, $duration, TRUE);
		cookies::set("__DRVID_PRS", $personID, $duration, TRUE);
		cookies::set("__DRVID_MX", $mxID, $duration, TRUE);
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public function getAccountID()
	{
		// Get mx from parent
		$accountID = parent::getAccountID();
		if (!empty($accountID))
			return $accountID;

		// Get value from cookies
		$accountID = engine::getVar("__DRVID_ACC");
		$accountID = (empty($accountID) ? engine::getVar("acc") : $accountID);
		if ($accountID == 'deleted')
			return NULL;
		
		return $accountID;
	}
	
	/**
	 * Gets the current mx id.
	 * 
	 * @return	integer
	 * 		The current mx id.
	 */
	public function getMX()
	{
		// Get mx from parent
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
	
	/**
	 * Gets the person id of the logged in account.
	 * 
	 * @return	integer
	 * 		The person id.
	 */
	public function getPersonID()
	{
		// Get person id
		$personID = parent::getPersonID();
		if (!empty($personID))
			return $personID;
		
		// Get value from cookies
		$personID = engine::getVar("__DRVID_PRS");
		$personID = (empty($personID) ? engine::getVar("person") : $personID);
		if ($personID == 'deleted')
			return NULL;
		
		return $personID;
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
		// Logout account id
		parent::logout();
		
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
	 * Update the account's password given the reset id token.
	 * 
	 * @param	string	$resetID
	 * 		The reset id.
	 * 
	 * @param	string	$newPassword
	 * 		The new account password.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function resetPassword($resetID, $newPassword)
	{
		return parent::updatePasswordByReset($resetID, $newPassword);
	}
	
	/**
	 * Get an accountSession instance for the current account.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
	 */
	protected function getAccountSessionInstance()
	{
		return accountSession::getInstance($this->getMX());
	}
}
//#section_end#
?>