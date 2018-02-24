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
importer::import("DRVC", "Social", "githubAccount");

use \ESS\Environment\url;
use \API\Profile\account;
use \DRVC\Social\githubAccount as IDGithubAccount;

/**
 * Github Login manager for Drovio platform
 * 
 * Manages github login for the drovio platform.
 * 
 * @version	0.1-1
 * @created	November 12, 2015, 12:58 (GMT)
 * @updated	November 12, 2015, 12:58 (GMT)
 */
class githubAccount extends IDGithubAccount
{
	/**
	 * The github account instance.
	 * 
	 * @type	githubAccount
	 */
	private static $instance;
	
	/**
	 * Get a github account instance.
	 * 
	 * @return	githubAccount
	 * 		The githubAccount instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new githubAccount();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new githubAccount instance.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(account::ID_TEAM_NAME);
	}
	
	/**
	 * Login via github login.
	 * It creates an account session and set the proper cookies.
	 * 
	 * @param	string	$code
	 * 		The github authorization code to get the access token.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function login($code = "")
	{
		// Get login info for drovio google login
		$info = array();
		$info['client_id'] = "62d4412050f4713087bc";
		$info['client_secret'] = "2985741ae98b04a1a29e0fe58cf3dd7181f829b3";
		$info['redirect_uri'] = url::resolve("www", "/ajax/account/oauth/github.php");
		
		// Call parent login
		$status = parent::login($code, $info['client_id'], $info['client_secret'], $info['redirect_uri']);
		if ($status)
			account::getInstance()->setCookies($this->getAccountID(), $this->getPersonID(), $this->getMX(), $rememberme = TRUE);
		
		return $status;
	}
}
//#section_end#
?>