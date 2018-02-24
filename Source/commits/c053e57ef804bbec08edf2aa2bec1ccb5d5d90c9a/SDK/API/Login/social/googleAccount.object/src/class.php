<?php
//#section#[header]
// Namespace
namespace API\Login\social;

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
 * @namespace	\social
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Login", "account");
importer::import("DRVC", "Social", "googleAccount");

use \API\Login\account;
use \DRVC\Social\googleAccount as IDGoogleAccount;

/**
 * Google Login manager for Drovio platform
 * 
 * Manages google login for the drovio platform.
 * 
 * @version	0.1-2
 * @created	November 11, 2015, 19:44 (GMT)
 * @updated	November 11, 2015, 19:45 (GMT)
 */
class googleAccount extends IDGoogleAccount
{
	/**
	 * The google account instance.
	 * 
	 * @type	googleAccount
	 */
	private static $instance;
	
	/**
	 * Get a google account instance.
	 * 
	 * @return	googleAccount
	 * 		The googleAccount instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new googleAccount();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new googleAccount instance.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(account::ID_TEAM_NAME);
	}
	
	/**
	 * Login via google login.
	 * It creates an account session and set the proper cookies.
	 * 
	 * @param	string	$code
	 * 		The google authorization code to get the access token.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function login($code = "")
	{
		// Get login info for drovio google login
		$info = array();
		$info['client_id'] = "1088239925512-t6knpm8l3q1cclfpobpg834sl9v9fo5r.apps.googleusercontent.com";
		$info['client_secret'] = "JX9THjwjTNbyz0_TFdl2hxZP";
		$info['redirect_uri'] = "http://drov.io/login/?social=google";
		
		// Call parent login
		$status = parent::login($code, $info['client_id'], $info['client_secret'], $info['redirect_uri']);
		if ($status)
			account::getInstance()->setCookies($this->getAccountID(), $this->getPersonID(), $this->getMX(), $rememberme = TRUE);
		
		return $status;
	}
}
//#section_end#
?>