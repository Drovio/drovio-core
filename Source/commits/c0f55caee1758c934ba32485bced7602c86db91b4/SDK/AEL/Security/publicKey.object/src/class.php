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
importer::import("AEL", "Platform", "application");
importer::import("AEL", "Security", "appKey");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "akeys/apiKey");

use \AEL\Security\appKey;
use \AEL\Platform\application;
use \API\Profile\team;
use \API\Security\akeys\apiKey;

class publicKey extends appKey
{
	const APP_PUBLIC_KEY = 6;
	
	public static function create()
	{
		// Get current running application
		$applicationID = application::init();
		
		// Get current team
		$teamID = team::getTeamID();
		
		// Create a API_PUBLIC_KEY key
		return apiKey::create(self::APP_PUBLIC_KEY, $accountID = NULL, $teamID, $applicationID);
	}
	
	public static function getTeamID($akey)
	{
		// Get key info
		$keyInfo = parent::info($akey);
		
		// Return team id
		return $keyInfo['team_id'];
	}
	
	public static function validate($akey, $teamID)
	{
		// Get key info to validate
		$keyInfo = apiKey::info($akey);
		
		// Get current running application and current team
		$applicationID = application::init();
		$teamID = team::getTeamID();
		
		// Validate that key is public, for the current team and application
		if (!($keyInfo['type_id'] == self::APP_PUBLIC_KEY && $keyInfo['team_id'] == $teamID && $keyInfo['project_id'] == $applicationID))
			return FALSE;
		
		return TRUE;
	}
}
//#section_end#
?>