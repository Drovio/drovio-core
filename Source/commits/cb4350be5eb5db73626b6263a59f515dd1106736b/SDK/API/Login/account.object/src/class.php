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
importer::import("API", "Login", "team");
importer::import("API", "Platform", "engine");
importer::import("DRVC", "Profile", "account");
importer::import("DRVC", "Utils", "authToken");

use \ESS\Environment\cookies;
use \API\Login\accountSession;
use \API\Login\team as LoginTeam;
use \API\Platform\engine;
use \DRVC\Profile\account as IDAccount;
use \DRVC\Utils\authToken;

/**
 * Drovio Login Account Interface
 * 
 * Manages the account login for the drovio platform using the drovio identity.
 * 
 * @version	2.0-4
 * @created	November 11, 2015, 18:44 (GMT)
 * @updated	December 13, 2015, 18:58 (GMT)
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
		parent::__construct(LoginTeam::ID_TEAM_NAME);
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
			$this->setCookies($this->getAuthToken(), $rememberme = TRUE);
		
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
			$this->setCookies($this->getAuthToken(), $rememberme = TRUE);
	}
	
	/**
	 * Set the proper cookies for the current account session.
	 * 
	 * @param	string	$authToken
	 * 		The authentication token to set in cookies.
	 * 
	 * @param	boolean	$rememberme
	 * 		Set the cookie duration to session or longer.
	 * 
	 * @return	void
	 */
	public function setCookies($authToken, $rememberme = FALSE)
	{
		// Set local variables
		$this->authToken = $authToken;
		
		// Set auth token cookie
		$duration = ($rememberme ? 30 * 24 * 60 * 60 : 0);
		cookies::set("__dat", $this->authToken, $duration, TRUE);
	}
	
	/**
	 * Gets the current logged in account id.
	 * 
	 * @return	integer
	 * 		The account id.
	 */
	public function getAuthToken()
	{
		// Get token from parent
		$authToken = parent::getAuthToken();
		if (!empty($authToken))
			return $authToken;

		// Get value from cookies
		$authToken = engine::getVar("__dat");
		$authToken = (empty($authToken) ? engine::getVar("auth_token") : $authToken);
		if ($authToken == 'deleted')
			return NULL;
		
		$this->authToken = $authToken;
		if (!empty($this->authToken))
			return $this->authToken;
		
		// BACK COMPATIBILITY
		// Get salt
		$accountID = engine::getVar("__DRVID_ACC");
		$salt = $this->getAccountSessionInstance()->getSalt($accountID);

		// Create auth token
		$payload = array();
		$payload['acc'] = $accountID;
		$payload['prs'] = engine::getVar("__DRVID_PRS");
		$payload['ssid'] = $this->getSessionID();
		$authToken = authToken::generate($payload, $salt);
		
		// Set value and cookies
		$this->setCookies($authToken, $rememberme = FALSE);
		return $this->authToken;
	}
	
	/**
	 * Get the current session id.
	 * This is for backwards compatibility with the old cookies.
	 * 
	 * @return	integer
	 * 		The current session id.
	 */
	public function getSessionID()
	{
		// Check direct access
		if (!empty($this->sessionID))
			return $this->sessionID;
		
		// Get value from cookies
		$mxID = engine::getVar("__DRVID_MX");
		$mxID = (empty($mxID) ? engine::getVar("mx") : $mxID);
		if ($mxID == 'deleted')
			return NULL;
		
		// Break parts
		$parts = explode(":", $mxID);
		return $parts[0];
	}
	
	/**
	 * Validates if the user is logged in.
	 * If session tokens are invalid, the user will be logged out.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function validate()
	{
		return parent::validate($logoutOnFail = TRUE);
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
		cookies::remove("__dat");
		
		// BACK COMPATIBILITY
		cookies::remove("auth_token");
		cookies::remove("__DRVID_AST");
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
		return accountSession::getInstance($this->getSessionID());
	}
}
//#section_end#
?>