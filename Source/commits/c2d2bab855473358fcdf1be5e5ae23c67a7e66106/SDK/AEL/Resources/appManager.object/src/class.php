<?php
//#section#[header]
// Namespace
namespace AEL\Resources;

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
 * @package	Resources
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Model", "apps/application");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");

use \API\Model\apps\application;
use \API\Profile\account;
use \API\Profile\team;

/**
 * Application General Resource Manager
 * 
 * Provides some interface about the application resources.
 * 
 * @version	0.1-4
 * @created	January 14, 2015, 17:07 (EET)
 * @updated	August 29, 2015, 18:57 (EEST)
 */
class appManager
{
	/**
	 * The account file mode.
	 * 
	 * @type	integer
	 */
	const ACCOUNT_MODE = 1;
	
	/**
	 * The team file mode.
	 * 
	 * @type	integer
	 */
	const TEAM_MODE = 2;
	
	/**
	 * Get the application root folder.
	 * Choose between
	 * 
	 * @param	integer	$mode
	 * 		The file mode.
	 * 		See class constants for options.
	 * 		It is in account mode by default.
	 * 
	 * @param	boolean	$shared
	 * 		If set to true, it will return the shared root folder for applications.
	 * 		It is FALSE by default.
	 * 
	 * @return	string
	 * 		The application root folder according to the given parameters.
	 */
	public static function getRootFolder($mode = ACCOUNT_MODE, $shared = FALSE)
	{
		if ($mode == self::ACCOUNT_MODE)
		{
			if ($shared)
				return account::getAccountFolder()."/SharedAppData/";
			else
				return application::getAccountApplicationFolder();
		}
		else
		{
			if ($shared)
				return team::getTeamFolder()."/SharedAppData/";
			else
				return application::getTeamApplicationFolder();
		}
	}
}
//#section_end#
?>