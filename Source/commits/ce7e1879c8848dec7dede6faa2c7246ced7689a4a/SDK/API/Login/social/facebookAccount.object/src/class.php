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

importer::import("ESS", "Environment", "url");
importer::import("API", "Profile", "account");
importer::import("DRVC", "Social", "facebookAccount");

use \ESS\Environment\url;
use \API\Profile\account;
use \DRVC\Social\facebookAccount as IDFacebookAccount;

/**
 * Facebook Login manager for Drovio platform
 * 
 * Manages facebook login for the drovio platform.
 * 
 * @version	0.1-3
 * @created	November 12, 2015, 11:37 (GMT)
 * @updated	November 14, 2015, 20:19 (GMT)
 */
class facebookAccount extends IDFacebookAccount
{
	/**
	 * The facebook account instance.
	 * 
	 * @type	facebookAccount
	 */
	private static $instance;
	
	/**
	 * Get a facebook account instance.
	 * 
	 * @return	facebookAccount
	 * 		The facebookAccount instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new facebookAccount();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new facebookAccount instance.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(account::ID_TEAM_NAME);
	}
	
	/**
	 * Login via facebook login.
	 * It creates an account session and set the proper cookies.
	 * 
	 * @param	string	$code
	 * 		The facebook authorization code to get the access token.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function login($code = "")
	{
		// Get login info for drovio google login
		$parameters = array();
		$parameters['code'] = $code;
		$parameters['client_id'] = "1528296957491399";
		$parameters['client_secret'] = "e7d5acbd7810e3ffe3898c9cb3e2e7c8";
		$parameters['redirect_uri'] = url::resolve("www", "/ajax/account/oauth/facebook.php");
		
		// Call parent login
		$status = parent::login($code, $info['client_id'], $info['client_secret'], $info['redirect_uri']);
		if ($status)
			account::getInstance()->setCookies($this->getAuthToken(), $rememberme = TRUE);
		
		return $status;
	}
}
//#section_end#
?>