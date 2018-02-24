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
importer::import("DRVC", "Social", "externalLoginAccount");

use \DRVC\Social\externalLoginAccount;

class facebookAccount extends externalLoginAccount
{
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
			self::$instance = new facebookLogin();
		
		// Return instance
		return self::$instance;
	}
	
	public function login($code)
	{
		// Check if there is a user already logged in
		if ($this->validate())
			return FALSE;
		
		// Get access token
		$parameters = array();
		$parameters['code'] = $code;
		$response = $this->urlRequest("https://graph.facebook.com/v2.3/oauth/access_token", $type = self::RQ_TYPE_GET, $parameters);
		$facebookInfo = json_decode($response, TRUE);
		
		// Check valid fields
		if (empty($facebookInfo['access_token']))
			return FALSE;
		
		// Get facebook email (and other info)
		$parameters = array();
		$parameters['fields'] = "id,name,email";
		$parameters['access_token'] = $facebookInfo['access_token'];
		$response = $this->urlRequest("https://graph.facebook.com/v2.3/me", $type = self::RQ_TYPE_GET, $parameters);
		$facebookUserInfo = json_decode($response, TRUE);
		
		// Check facebook info
		if (empty($facebookUserInfo))
			return FALSE;
		
		// Get account by email
		$accountInfo = $this->getAccountByUsername($facebookUserInfo['email'], $includeEmail = TRUE, $fullList = FALSE);
		if (empty($accountInfo))
		{
			// Create new account
			$accountID = $this->create($facebookUserInfo['email'], $firstname = "", $lastname = "", $password = "");
			$accountInfo = $this->info($accountID);
		}
		
		return $this->createSession($accountInfo);
	}
}
//#section_end#
?>