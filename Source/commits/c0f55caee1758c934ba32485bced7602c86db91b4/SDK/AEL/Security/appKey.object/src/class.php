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
importer::import("API", "Profile", "team");
importer::import("API", "Security", "akeys/apiKey");

use \AEL\Platform\application;
use \API\Profile\team;
use \API\Security\akeys\apiKey;

class appKey
{
	public static function info($akey)
	{
		// Get key info
		return apiKey::info($akey);
	}
	
	public static function regenerateKey($akey)
	{
		// Validate key
		if (!self::validate($akey))
			return FALSE;
		
		// Regenerate key
		return apiKey::regenerate($akey);
	}
	
	public static function getTeamKeys()
	{
		// Get current running application
		$applicationID = application::init();
		
		// Get current team
		$teamID = team::getTeamID();
		
		// Get all team 
		return apiKey::getProjectTeamKeys($applicationID, $teamID);
	}
}
//#section_end#
?>