<?php
//#section#[header]
// Namespace
namespace AEL\Identity;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Identity
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("DRVC", "Social", "facebookAccount");

use \API\Profile\team;
use \DRVC\Social\facebookAccount;

/**
 * Application Social Login Manager
 * 
 * Handles all social login for an application using the identity API.
 * 
 * @version	0.1-1
 * @created	November 13, 2015, 12:49 (UTC)
 * @updated	November 13, 2015, 12:49 (UTC)
 */
class SocialLoginManager
{
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
	public static function facebookLogin($code, $clientID, $clientSecret, $redirectURI)
	{
		// Get current team
		$teamName = team::getTeamUName();
		$teamName = strtolower($teamName);
		
		// Create facebook instance and login
		return facebookAccount::getInstance($teamName)->login($code, $clientID, $clientSecret, $redirectURI);
	}
}
//#section_end#
?>