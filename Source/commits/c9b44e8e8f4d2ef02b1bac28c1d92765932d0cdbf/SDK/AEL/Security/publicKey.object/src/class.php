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
importer::import("AEL", "Security", "appKey");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "akeys/apiKey");

use \AEL\Security\appKey;
use \AEL\Platform\application;
use \API\Profile\team;
use \API\Security\akeys\apiKey;

/**
 * Application Public Key Handler
 * 
 * This class provides an interface for editing the application public keys.
 * Public keys allow developer teams to provide authentication on the client side using javascript to communicate with the api.
 * 
 * @version	3.0-1
 * @created	October 14, 2015, 14:27 (BST)
 * @updated	November 22, 2015, 0:58 (GMT)
 */
class publicKey extends appKey
{
	/**
	 * The application public key type id.
	 * 
	 * @type	integer
	 */
	const APP_PUBLIC_KEY = 7;
	
	/**
	 * Create a new public key between the team and the application.
	 * 
	 * @return	mixed
	 * 		The key created on success, false on failure.
	 */
	public static function create()
	{
		// Get current running application
		$applicationID = application::init();
		
		// Get current team
		$teamID = team::getTeamID();
		
		// Create a API_PUBLIC_KEY key
		return apiKey::create(self::APP_PUBLIC_KEY, $accountID = NULL, $teamID, $applicationID);
	}
	
	/**
	 * Get the team id that is connected to the given api key.
	 * 
	 * @param	string	$akey
	 * 		The api key.
	 * 
	 * @return	integer
	 * 		The team id or null if the key is invalid.
	 */
	public static function getTeamID($akey)
	{
		// Get key info
		$keyInfo = parent::info($akey);
		
		// Return team id
		return $keyInfo['team_id'];
	}
	
	/**
	 * Validate if the given key is public and connected to the given team (for the current running application).
	 * 
	 * @param	string	$akey
	 * 		The key to validate.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to validate.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function validate($akey, $teamID)
	{
		// Get key info to validate
		$keyInfo = apiKey::info($akey, $includePreviousKey = TRUE);
		
		// Get current running application and current team
		$applicationID = application::init();
		$teamID = (empty($teamID) ? team::getTeamID() : $teamID);
		
		// Validate that key is public, for the current team and application
		if (!($keyInfo['type_id'] == self::APP_PUBLIC_KEY && $keyInfo['team_id'] == $teamID && $keyInfo['project_id'] == $applicationID))
			return FALSE;
		
		return TRUE;
	}
	
	
	
	/**
	 * Regenerate the given api key.
	 * This function will keep the current key valid for 24 hours.
	 * 
	 * @param	string	$akey
	 * 		The key to regenerate.
	 * 
	 * @return	mixed
	 * 		The new key on success, false on failure.
	 */
	public static function regenerateKey($akey)
	{
		// Validate key
		if (!self::validate($akey))
			return FALSE;
		
		// Regenerate key
		return apiKey::regenerateKey($akey);
	}
	
	/**
	 * Remove the api key from the application (and the database).
	 * 
	 * @param	string	$akey
	 * 		The api key to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($akey)
	{
		// Validate key
		if (!self::validate($akey))
			return FALSE;
		
		// Regenerate key
		return apiKey::remove($akey);
	}
	
	/**
	 * Get all public api keys that are between the current application and the current team.
	 * 
	 * @return	array
	 * 		An array of all public keys.
	 */
	public static function getTeamKeys()
	{
		// Get all keys
		$allTeamKeys = parent::getTeamKeys();
		
		// Filter all private keys
		$publicKeys = array();
		foreach ($allTeamKeys as $keyInfo)
			if ($keyInfo['type_id'] == self::APP_PUBLIC_KEY)
				$publicKeys[] = $keyInfo;
		
		// Return key list
		return $publicKeys;
	}
}
//#section_end#
?>