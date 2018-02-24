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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "engine");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("DEV", "Projects", "project");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Platform\engine;
use \API\Profile\account;
use \API\Profile\team;
use \DEV\Projects\project;

importer::import("API", "Security", "akeys/apiKey");
importer::import("API", "Security", "akeys/apiKeyType");

use \API\Security\akeys\apiKey;
use \API\Security\akeys\apiKeyType;

/**
 * Account's key manager
 * 
 * Manages all the keys for an account.
 * 
 * NOTE: This class is partially deprecated. Use \API\Security\akeys\apiKey.
 * 
 * @version	11.0-5
 * @created	August 12, 2014, 12:27 (BST)
 * @updated	November 12, 2015, 16:56 (GMT)
 * 
 * @deprecated	Use \API\Security\akeys\apiKey instead.
 */
class accountKey extends apiKey
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
		// Get type from user group id
		$typeInfo = apiKeyType::getKeyTypeByGroupID($userGroup_id);
		$typeID = $typeInfo['id'];
		$accountID = (empty($accountID) ? account::getInstance()->getAccountID() : $accountID);
		$teamID = ($type == 1 ? $context : NULL);
		$projectID = ($type == 2 ? $context : NULL);
		
		// Create key
		return parent::create($typeID, $accountID, $teamID, $projectID);
	}
	
	/**
	 * Remove an existing account key.
	 * 
	 * @param	string	$key
	 * 		The key to be removed.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to validate the key.
	 * 		Leave empty for current account.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($key, $accountID = "")
	{
		return parent::remove($key);
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
		return parent::getAccountKeys($accountID);
	}
	
	/**
	 * Get all account project keys for the current account.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to get keys for.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the keys for.
	 * 		If empty, get the current account.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all account access keys.
	 */
	public static function getProjectKeys($projectID, $accountID = NULL)
	{
		return parent::getProjectAccountKeys($projectID, $accountID);
	}
	
	/**
	 * Get all account team keys for the current account.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the keys for.
	 * 		If empty, get the current account.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all account access keys.
	 */
	public static function getTeamKeys($teamID, $accountID = NULL)
	{
		return parent::getTeamKeys($teamID, $accountID);
	}
	
	/**
	 * Validate a given key in the given project or team.
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
	 * 		Use class constants for this.
	 * 
	 * @return	boolean
	 * 		True if the given API key is valid, false otherwise.
	 */
	public static function validate($akey, $context, $type)
	{
		// Transform parameters for the new key
		$accountID = account::getInstance()->getAccountID();
		$teamID = ($type == 1 ? $context : NULL);
		$projectID = ($type == 2 ? $context : NULL);
		
		// Validate key
		return parent::validateKey($akey, $accountID, $teamID, $projectID);
	}
	
	/**
	 * Validate an account with the given key in the given project or team.
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
	 * 		Use class constants for this.
	 * 
	 * @return	boolean
	 * 		True if the account has the given API key, false otherwise.
	 */
	public static function validateAccount($akey, $context, $type)
	{
		// Transform parameters for the new key
		$teamID = ($type == 1 ? $context : NULL);
		$projectID = ($type == 2 ? $context : NULL);
		
		// Get key info
		$keyInfo = parent::info($akey);
		// Validate
		return ($keyInfo['account_id'] == account::getInstance()->getAccountID() && ($keyInfo['team_id'] == $teamID || $keyInfo['project_id'] == $projectID));
	}
	
	/**
	 * Validate an account that is part of a group in the given project or team.
	 * 
	 * @param	string	$groupName
	 * 		The group name that the account should be part of.
	 * 
	 * @param	string	$context
	 * 		The key context.
	 * 		It is either the team id or the project id, depending on the request.
	 * 
	 * @param	string	$type
	 * 		The key context type.
	 * 		Use class constants for this.
	 * 
	 * @return	boolean
	 * 		True if the account has an API key for the specified combination, false otherwise.
	 */
	public static function validateGroup($groupName, $context, $type)
	{
		// Transform parameters for the new key
		$accountID = account::getInstance()->getAccountID();
		$teamID = ($type == 1 ? $context : NULL);
		$projectID = ($type == 2 ? $context : NULL);
		
		// Validate group
		return parent::validateGroupName($groupName, $accountID, $teamID, $projectID);
	}
	
	/**
	 * Validate an account that is part of a group in the given project or team.
	 * 
	 * @param	integer	$groupID
	 * 		The group id that the account should be part of.
	 * 
	 * @param	string	$context
	 * 		The key context.
	 * 		It is either the team id or the project id, depending on the request.
	 * 
	 * @param	string	$type
	 * 		The key context type.
	 * 		Use class constants for this.
	 * 
	 * @return	boolean
	 * 		True if the account has an API key for the specified combination, false otherwise.
	 */
	public static function validateGroupID($groupID, $context, $type)
	{
		// Transform parameters for the new key
		$teamID = ($type == 1 ? $context : NULL);
		$projectID = ($type == 2 ? $context : NULL);
		
		// Validate group
		return parent::validateGroup($groupID, account::getInstance()->getAccountID(), $teamID, $projectID);
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
				// Get request variables
				$projectID = engine::getVar('id');
				$projectName = engine::getVar('name');
				
				// Check variables existence
				if (empty($projectID) && empty($projectName))
					return NULL;
				
				// Create project and get id
				$project = new project($projectID, $projectName);
				return $project->getID();
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
				// Get team information from database
				$dbc = new dbConnection();
				$q = new dbQuery("3457285831128", "profile.team");
				$attr = array();
				$attr['id'] = $context;
				$result = $dbc->execute($q, $attr);
				
				// Return team name
				$teamData = $dbc->fetch($result);
				return $teamData['name'];
			case 2:
				// Initialize project
				$project = new project($context);
				
				// Return project title
				$projectInfo = $project->info();
				return $projectInfo['title'];
		}
		
		return NULL;
	}
}
//#section_end#
?>