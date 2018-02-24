<?php
//#section#[header]
// Namespace
namespace AEL\Security;

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
 * @package	Security
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("AEL", "Platform", "application");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "akeys/apiKey");

use \AEL\Platform\application;
use \API\Profile\team;
use \API\Security\akeys\apiKey;

/**
 * Application API keys manager.
 * 
 * This class provides an interface to read application team keys.
 * 
 * @version	1.0-2
 * @created	October 16, 2015, 18:47 (BST)
 * @updated	November 22, 2015, 0:58 (GMT)
 */
class appKey
{
	/**
	 * Get API key information.
	 * 
	 * @param	string	$akey
	 * 		The api key to get information for.
	 * 
	 * @return	array
	 * 		All key information including type, time created etc.
	 */
	public static function info($akey)
	{
		// Get key info
		return apiKey::info($akey);
	}
	
	
	/**
	 * Get all api keys that are between the current application and the current team.
	 * 
	 * @return	array
	 * 		An array of all keys.
	 */
	public static function getTeamKeys()
	{
		// Get current running application
		$applicationID = application::init();
		
		// Get current team
		$teamID = team::getTeamID();
		
		// Get all team 
		return apiKey::getProjectTeamKeys($applicationID, $teamID);
	}
	
	/**
	 * Validate whether the given key is valid for the given origin.
	 * 
	 * @param	string	$akey
	 * 		The key to validate.
	 * 
	 * @param	string	$origin
	 * 		The origin to check.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function validateOrigin($akey, $origin)
	{
		return apiKey::validateOrigin($akey, $origin);
	}
}
//#section_end#
?>