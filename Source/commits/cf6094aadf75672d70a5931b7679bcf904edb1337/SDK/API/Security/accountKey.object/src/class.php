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
importer::import("API", "Model", "sql::dbQuery");
importer::import("API", "Profile", "team");
importer::import("API", "Profile", "account");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \API\Profile\account as sAccount;
use \DEV\Projects\project;

/**
 * Account's key manager
 * 
 * Manages all the keys for an account.
 * 
 * @version	6.0-2
 * @created	August 12, 2014, 14:27 (EEST)
 * @revised	November 27, 2014, 16:46 (EET)
 */
class accountKey
{
	/**
	 * The team key type value.
	 * 
	 * @type	integer
	 */
	const TEAM_KEY_TYPE = 1;
	
	/**
	 * The project key type value.
	 * 
	 * @type	integer
	 */
	const PROJECT_KEY_TYPE = 2;
	
	/**
	 * Create an account access key for the given account.
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
	 * @param	integer	$accountID
	 * 		The account id to create the key for.
	 * 		If empty, get the current account.
	 * 		It is empty by default.
	 * 
	 * @return	mixed
	 * 		The generated key on success, false on failure.
	 */
	public static function create($userGroup_id, $type, $context, $accountID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("20225145860361", "security.privileges.keys");
		
		// Create key
		$accountID = (empty($accountID) ? sAccount::getAccountID() : $accountID);
		$time = time();
		$akey = self::key($accountID."_".$userGroup_id."_".$type."_".$time, $context);
		
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['ugid'] = $userGroup_id;
		$attr['type'] = $type;
		$attr['context'] = $context;
		$attr['time'] = $time;
		$attr['akey'] = $akey;
		$status = $dbc->execute($q, $attr);
		if ($status)
			return $akey;
		
		return FALSE;
	}
	
	/**
	 * Remove an existing account key.
	 * 
	 * @param	string	$key
	 * 		The key to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($key)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("22517941705946", "security.privileges.keys");
		
		// Create key
		$accountID = (empty($accountID) ? sAccount::getAccountID() : $accountID);
		$attr['akey'] = $key;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all account access keys for the current account.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the keys for.
	 * 		If empty, get the current account.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all account access keys.
	 */
	public static function get($accountID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("29518071788493", "security.privileges.keys");
		
		// Get keys
		$accountID = (empty($accountID) ? sAccount::getAccountID() : $accountID);
		$attr['aid'] = $accountID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Validate an account with the given key.
	 * 
	 * @param	string	$akey
	 * 		The API key to validate.
	 * 
	 * @param	string	$context
	 * 		The key context.
	 * 		It is either the team id or the project id, depending on the request.
	 * 
	 * @param	string	$type
	 * 		The key context type.
	 * 		1: team keys.
	 * 		2: project keys.
	 * 
	 * @return	boolean
	 * 		True if the account has the given API key, false otherwise.
	 */
	public static function validate($akey, $context, $type)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("14380003014956", "security.privileges.keys");
		
		// Validate key
		$attr['aid'] = sAccount::getAccountID();
		$attr['key'] = $akey;
		$attr['context'] = $context;
		$attr['type'] = $type;
		$result = $dbc->execute($q, $attr);
		return ($dbc->get_num_rows($result) > 0);
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
	
	/**
	 * Get a key context description (the name of the team or the title of the project) for a given key type and context id.
	 * 
	 * @param	integer	$keyType
	 * 		The key type to get the context from.
	 * 
	 * @param	string	$context
	 * 		The key context (team or project id).
	 * 
	 * @return	string
	 * 		The context description.
	 */
	public static function getContextDescription($keyType, $context)
	{
		switch ($keyType)
		{
			case 1:
				$dbc = new dbConnection();
				$q = new dbQuery("3457285831128", "profile.team");
				$attr = array();
				$attr['id'] = $context;
				$result = $dbc->execute($q, $attr);
				$teamData = $dbc->fetch($result);
				return $teamData['name'];
			case 2:
				$project = new project($context);
				
				$projectInfo = $project->info();
				return $projectInfo['title'];
		}
		
		return NULL;
	}
}
//#section_end#
?>