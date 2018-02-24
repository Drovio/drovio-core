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
 * Facebook Account Login
 * 
 * Manages facebook login for the identity account.
 * 
 * @version	1.0-1
 * @created	November 12, 2015, 11:37 (UTC)
 * @updated	November 13, 2015, 12:48 (UTC)
 */
class facebookAccount extends externalLoginAccount
{
	/**
	 * The facebookAccount instance.
	 * 
	 * @type	facebookAccount
	 */
	private static $instance;
	
	/**
	 * Get the facebookAccount instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	facebookAccount
	 * 		The facebookAccount instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new facebookAccount($teamName);
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Login via facebook login.
	 * It creates an account session.
	 * 
	 * @param	string	$code
	 * 		The facebook authorization code to get the access token.
	 * 
	 * @param	string	$clientID
	 * 		The facebook client id.
	 * 
	 * @param	string	$clientSecret
	 * 		The facebook client secret.
	 * 
	 * @param	string	$redirectURI
	 * 		The facebook redirect_uri parameter.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function login($code, $clientID, $clientSecret, $redirectURI)
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
		$response = $this->urlRequest("https://graph.facebook.com/v2.3/oauth/access_token", $type = self::RQ_TYPE_GET, $parameters);
		$accessTokenInfo = json_decode($response, TRUE);
		
		// Check valid fields
		if (empty($accessTokenInfo['access_token']))
			return FALSE;
		
		// Get facebook email (and other info)
		$parameters = array();
		$parameters['fields'] = "id,name,email";
		$parameters['access_token'] = $accessTokenInfo['access_token'];
		$response = $this->urlRequest("https://graph.facebook.com/v2.3/me", $type = self::RQ_TYPE_GET, $parameters);
		$userProfileInfo = json_decode($response, TRUE);
		
		// Check facebook info
		if (empty($userProfileInfo))
			return FALSE;
		
		// Get account by email
		$accountInfo = $this->getAccountByUsername($userProfileInfo['email'], $includeEmail = TRUE, $fullList = FALSE);
		if (empty($accountInfo))
		{
			// Create new account
			$nameParts = explode($userProfileInfo['name']);
			$firstname = $nameParts[0];
			$lastname = $nameParts[count($nameParts) - 1];
			$accountID = $this->create($userProfileInfo['email'], $firstname, $lastname);
			$accountInfo = $this->info($accountID);
		}
		
		// Create session
		return $this->createSession($accountInfo);
	}
}
//#section_end#
?>