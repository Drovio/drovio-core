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

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "account");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\team;
use \API\Security\account;
use \DEV\Projects\project;

/**
 * Account's key manager
 * 
 * Manages all the keys for an account.
 * 
 * @version	2.0-1
 * @created	August 12, 2014, 14:27 (EEST)
 * @revised	August 12, 2014, 23:17 (EEST)
 */
class accountKey
{
	/**
	 * Create an account access key.
	 * 
	 * @param	integer	$userGroup_id
	 * 		The user group id for the access key.
	 * 
	 * @param	integer	$type
	 * 		The key type according to the database key types.
	 * 
	 * @param	integer	$context
	 * 		The key context.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function create($userGroup_id, $type, $context)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("20225145860361", "profile.account");
		
		// Create key
		$attr['aid'] = account::getAccountID();
		$attr['ugid'] = $userGroup_id;
		$attr['type'] = $type;
		$attr['context'] = $context;
		$attr['time'] = time();
		$attr['akey'] = self::key(account::getAccountID()."_".$userGroup_id."_".$type, $context);
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all account access keys for the current account.
	 * 
	 * @return	array
	 * 		An array of all account access keys.
	 */
	public static function get()
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("29518071788493", "security.privileges.keys");
		
		// Get keys
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Generate an account key.
	 * 
	 * @param	string	$prefix
	 * 		The key prefix.
	 * 
	 * @param	string	$value
	 * 		The key main context/value.
	 * 
	 * @return	string
	 * 		The generated key.
	 */
	private static function key($prefix, $value)
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