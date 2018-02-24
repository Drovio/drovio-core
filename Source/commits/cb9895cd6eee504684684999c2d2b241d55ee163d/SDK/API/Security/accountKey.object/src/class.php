<?php
//#section#[header]
// Namespace
namespace API\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Security
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("DEV", "Projects", "project");

use \API\Profile\team;
use \DEV\Projects\project;

/**
 * Account's key manager
 * 
 * Manages all the keys for an account.
 * 
 * @version	0.1-1
 * @created	August 12, 2014, 14:27 (EEST)
 * @revised	August 12, 2014, 14:27 (EEST)
 */
class accountKey
{
	/**
	 * Generate an account key.
	 * 
	 * @param	string	$prefix
	 * 		The type prefix for the key.
	 * 
	 * @param	string	$value
	 * 		The key value.
	 * 
	 * @return	string
	 * 		The generated key.
	 */
	public static function get($prefix, $value)
	{
		return md5("accessKey_".$prefix."_".$value);
	}
	
	/**
	 * Get the key context given a key type.
	 * 
	 * @param	integer	$keyType
	 * 		The key type to get the context from.
	 * 
	 * @return	integer
	 * 		The context of the check.
	 */
	public static function getContext($keyType)
	{
		switch ($keyType)
		{
			case 1:
				return team::getTeamID();
			case 2:
				$projectID = $_REQUEST['id'];
				$projectName = $_REQUEST['name'];
				$project = new project($projectID, $projectName);
				
				$projectInfo = $project->info();
				return $projectInfo['id'];
		}
		
		return NULL;
	}
}
//#section_end#
?>