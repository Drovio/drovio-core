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
importer::import("DRVC", "Profile", "account");

use \DRVC\Profile\account;

abstract class externalLoginAccount extends account
{
	const RQ_TYPE_POST = "post";
	const RQ_TYPE_GET = "get";
	
	abstract function login();
	
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
	 * Make the cURL request to mailgun api.
	 * 
	 * @param	string	$url
	 * 		The url value.
	 * 
	 * @param	array	$parameters
	 * 		The post parameters.
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
			$options[CURLOPT_POSTFIELDS] = $parameters;
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