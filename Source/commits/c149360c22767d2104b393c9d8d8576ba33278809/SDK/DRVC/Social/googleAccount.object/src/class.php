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
 * @version	1.0-3
 * @created	November 11, 2015, 19:43 (GMT)
 * @updated	November 12, 2015, 12:36 (GMT)
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
	 * @return	boolean
	 * 		True on success, false on failure.
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
		$accessTokenInfo = json_decode($response, TRUE);
		
		// Check valid fields
		if (empty($accessTokenInfo['access_token']))
			return FALSE;
		
		// Get google plus info
		$parameters = array();
		$parameters['access_token'] = $accessTokenInfo['access_token'];
		$response = $this->urlRequest("https://www.googleapis.com/plus/v1/people/me", $type = self::RQ_TYPE_GET, $parameters);
		$userProfileInfo = json_decode($response, TRUE);

		// Check google info
		if (empty($userProfileInfo))
			return FALSE;
		
		// Get google account email
		$email = $userProfileInfo['emails'][0]['value'];
		if (empty($email))
			return FALSE;
		
		// Get account info by email
		$accountInfo = $this->getAccountByUsername($email, $includeEmail = TRUE, $fullList = FALSE);
		if (empty($accountInfo))
		{
			// Create new account
			$accountID = $this->create($email, $userProfileInfo['name']['givenName'], $userProfileInfo['name']['familyName']);
			$accountInfo = $this->info($accountID);
		}
		
		// Create session and return status
		return $this->createSession($accountInfo);
	}
}
//#section_end#
?>