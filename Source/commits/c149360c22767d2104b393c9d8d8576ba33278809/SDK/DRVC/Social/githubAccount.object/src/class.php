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
 * Github Account Login
 * 
 * Manages github login for the identity account.
 * 
 * @version	0.1-1
 * @created	November 12, 2015, 12:59 (GMT)
 * @updated	November 12, 2015, 12:59 (GMT)
 */
class githubAccount extends externalLoginAccount
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
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	githubAccount
	 * 		The githubAccount instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new githubAccount($teamName);
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Login via github login.
	 * It creates an account session.
	 * 
	 * @param	string	$code
	 * 		The github authorization code to get the access token.
	 * 
	 * @param	string	$clientID
	 * 		The github client id.
	 * 
	 * @param	string	$clientSecret
	 * 		The github client secret.
	 * 
	 * @param	string	$redirectURI
	 * 		The github redirect_uri parameter.
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
		$parameters['state'] = "";
		$headers = array();
		$headers[] = "Accept: application/json";
		$response = $this->urlRequest("https://github.com/login/oauth/access_token", $type = self::RQ_TYPE_POST, $parameters, $headers);
		$accessTokenInfo = json_decode($response, TRUE);
		
		// Check valid fields
		if (empty($accessTokenInfo['access_token']))
			return FALSE;
		
		// Get google plus info
		$parameters = array();
		$parameters['access_token'] = $accessTokenInfo['access_token'];
		$headers = array();
		$headers[] = "Accept: application/json";
		$headers[] = "User-Agent: Drovio";
		$response = $this->urlRequest("https://api.github.com/user", $type = self::RQ_TYPE_GET, $parameters, $headers);
		$userProfileInfo = json_decode($response, TRUE);

		// Check github info
		if (empty($userProfileInfo))
			return FALSE;
		
		// Get github account email
		$email = $userProfileInfo['email'];
		if (empty($email))
			return FALSE;
		
		// Get account info by email
		$accountInfo = $this->getAccountByUsername($email, $includeEmail = TRUE, $fullList = FALSE);
		if (empty($accountInfo))
		{
			// Create new account
			$nameParts = explode($userProfileInfo['name']);
			$firstname = $nameParts[0];
			$lastname = $nameParts[count($nameParts) - 1];
			$accountID = $this->create($email, $firstname, $lastname);
			$accountInfo = $this->info($accountID);
		}
		
		// Create session and return status
		return $this->createSession($accountInfo);
	}
}
//#section_end#
?>