<?php
//#section#[header]
// Namespace
namespace DRVC\Social;

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
 * @package	Social
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("DRVC", "Social", "externalLoginAccount");

use \DRVC\Social\externalLoginAccount;

/**
 * Google Account login
 * 
 * Manages google login for the identity account.
 * 
 * @version	0.1-1
 * @created	November 11, 2015, 19:43 (GMT)
 * @updated	November 11, 2015, 19:43 (GMT)
 */
class googleAccount extends externalLoginAccount
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
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	googleAccount
	 * 		The googleAccount instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new googleAccount($teamName);
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new googleAccount instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName)
	{
		parent::__construct($teamName);
	}
	
	/**
	 * Login via google login.
	 * It creates an account session.
	 * 
	 * @param	string	$code
	 * 		The google authorization code to get the access token.
	 * 
	 * @param	integer	$clientID
	 * 		The google client id.
	 * 
	 * @param	string	$clientSecret
	 * 		The google client secret.
	 * 
	 * @param	string	$redirectURI
	 * 		The google redirect_uri parameter.
	 * 
	 * @return	void
	 */
	public function login($code = "", $clientID = "", $clientSecret = "", $redirectURI = "")
	{
		// Check if there is a user already logged in
		if ($this->validate())
			return FALSE;
		
		// Get access token
		$parameters = array();
		$parameters['code'] = $code;
		$parameters['client_id'] = $clientID;
		$parameters['client_secret'] = $clientSecret;
		$parameters['redirect_uri'] = $redirectURI;
		$parameters['grant_type'] = "authorization_code";
		$response = $this->urlRequest("https://www.googleapis.com/oauth2/v3/token", $type = self::RQ_TYPE_POST, $parameters);
		$googleInfo = json_decode($response, TRUE);
		
		// Check valid fields
		if (empty($googleInfo['access_token']))
			return FALSE;
		
		// Get google plus info
		$parameters = array();
		$parameters['access_token'] = $googleInfo['access_token'];
		$response = $this->urlRequest("https://www.googleapis.com/plus/v1/people/me", $type = self::RQ_TYPE_GET, $parameters);
		$googleUserInfo = json_decode($response, TRUE);

		// Check google info
		if (empty($googleUserInfo))
			return FALSE;
		
		// Get google account email
		$email = $googleUserInfo['emails'][0]['value'];
		if (empty($email))
			return FALSE;
		
		// Get account info by email
		$accountInfo = $this->getAccountByUsername($email, $includeEmail = TRUE, $fullList = FALSE);
		if (empty($accountInfo))
		{
			// Create new account
			$accountID = $this->create($email, $firstname = "", $lastname = "", $password = "");
			$accountInfo = $this->info($accountID);
		}
		
		return $this->createSession($accountInfo);
	}
}
//#section_end#
?>