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

importer::import("DRVC", "Profile", "account");
importer::import("DRVC", "Profile", "accountSession");

use \DRVC\Profile\account;
use \DRVC\Profile\accountSession;

/**
 * Abstract External (Social) Login Account Manager
 * 
 * Manages external login using social networks and creates the proper sessions needed.
 * 
 * @version	0.1-1
 * @created	November 11, 2015, 19:29 (GMT)
 * @updated	November 11, 2015, 19:29 (GMT)
 */
abstract class externalLoginAccount extends account
{
	/**
	 * Post type request.
	 * 
	 * @type	string
	 */
	const RQ_TYPE_POST = "post";
	/**
	 * Get type request.
	 * 
	 * @type	string
	 */
	const RQ_TYPE_GET = "get";
	
	/**
	 * Create a new login session.
	 * 
	 * @param	array	$accountInfo
	 * 		The account information given by the inherited class (social login, facebook login etc.).
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected function createSession($accountInfo)
	{
		// Get account id
		$this->accountID = $accountInfo['id'];
		$this->personID = $accountInfo['person_id'];
		
		// Create salt
		$salt = hash("md5", time());
		
		// Create Account Session
		$this->getAccountSessionInstance()->create($salt, $this->accountID, $this->personID, $rememberme = TRUE);
		$this->sessionID = $this->getAccountSessionInstance()->getSessionID();
		$this->mxID = accountSession::getSaltedMix($this->getSessionID(), $this->accountID, $salt, $this->personID);
		
		// Update session
		$this->updateSession();
		
		return TRUE;
	}
	
	/**
	 * Make a cURL request to the given url.
	 * 
	 * @param	string	$url
	 * 		The url to make the request to.
	 * 
	 * @param	string	$type
	 * 		The request type.
	 * 		It is post by default.
	 * 
	 * @param	array	$parameters
	 * 		An associative array for request parameters.
	 * 
	 * @return	mixed
	 * 		The cURL response.
	 */
	protected function urlRequest($url, $type = self::RQ_TYPE_POST, $parameters = array())
	{
		// Initialize cURL
		$curl = curl_init();
		
		// Initialize options
		$options = array();
		
		// Choose request method
		if ($type == self::RQ_TYPE_POST)
		{
			// Set post parameters
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = http_build_query($parameters);
		}
		else
		{
			// Format url with parameters
			$url .= "?".http_build_query($parameters);
		}
		
		// Set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_RETURNTRANSFER] = 1;
		
		// Set options array
		curl_setopt_array($curl, $options);
		
		// Execute and close url
		$response = curl_exec($curl);
		curl_close($curl);
		
		// Return response
		return $response;
	}
}
//#section_end#
?>