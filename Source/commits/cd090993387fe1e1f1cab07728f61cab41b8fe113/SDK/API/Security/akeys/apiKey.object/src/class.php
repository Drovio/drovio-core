<?php
//#section#[header]
// Namespace
namespace API\Security\akeys;

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
 * @namespace	\akeys
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");
importer::import("API", "Profile", "team");
importer::import("API", "Security", "akeys/apiKeyType");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;
use \API\Profile\team;
use \API\Security\akeys\apiKeyType;

/**
 * API key manager
 * 
 * Manages all api keys for the platform.
 * 
 * @version	1.1-3
 * @created	October 10, 2015, 20:53 (BST)
 * @updated	November 14, 2015, 15:40 (GMT)
 */
class apiKey
{
	/**
	 * Create a new API key.
	 * 
	 * @param	integer	$typeID
	 * 		The key type id.
	 * 		Use apiKeyType to get all the key types.
	 * 
	 * @param	integer	$accountID
	 * 		The account id for the key.
	 * 
	 * @param	integer	$teamID
	 * 		The team id for the key.
	 * 
	 * @param	integer	$projectID
	 * 		The project id for the key.
	 * 
	 * @return	mixed
	 * 		The created key on success, false on failure.
	 */
	public static function create($typeID, $accountID = NULL, $teamID = NULL, $projectID = NULL)
	{
		// Check type
		if (empty($typeID))
			return FALSE;
		
		// Generate key
		$time = time();
		$akey = self::generateKey($typeID, $accountID."_".$teamID."_".$projectID."_".$time);
		
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("31521147889393", "security.akeys");
		
		// Create key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['type'] = $typeID;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$attr['tid'] = (empty($teamID) ? "NULL" : $teamID);
		$attr['pid'] = (empty($projectID) ? "NULL" : $projectID);
		$attr['time'] = $time;
		$status = $dbc->execute($q, $attr);
		if ($status)
			return $akey;
		
		return FALSE;
	}
	
	/**
	 * Get key information.
	 * 
	 * @param	string	$akey
	 * 		The API key to get info for.
	 * 
	 * @param	boolean	$includePreviousKey
	 * 		If set to true, it will check if the key is a previous key and match that too.
	 * 		It is FALSE by default.
	 * 
	 * @return	array
	 * 		The key information including the user group id and name that corresponds to (if any).
	 */
	public static function info($akey, $includePreviousKey = FALSE)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("24020695851951", "security.akeys");
		
		// Create key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['include_p_akey'] = ($includePreviousKey ? 1 : 0);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
	}
	
	/**
	 * Remove a given key.
	 * 
	 * @param	string	$akey
	 * 		The API key to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function remove($akey)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("27256137700655", "security.akeys");
		
		// Create key
		$attr = array();
		$attr['akey'] = $akey;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Regenerate a key.
	 * This process will keep the old key for 24 hours.
	 * 
	 * @param	string	$akey
	 * 		The API key to regenerate.
	 * 
	 * @return	mixed
	 * 		The new generaged key on success, false on failure.
	 */
	public static function regenerateKey($akey)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("144551524983", "security.akeys");
		
		// Generate new key and set expiration time in 24 hours
		$akeyInfo = self::info($akey);
		$new_akey = self::generateKey($akeyInfo['type_id'], $akeyInfo['account_id']."_".$akeyInfo['team_id']."_".$akeyInfo['project_id']."_".time());
		$time_expires = time() + 24 * 60 * 60;
		
		// Create key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['new_akey'] = $new_akey;
		$attr['expires'] = $time_expires;
		$status = $dbc->execute($q, $attr);
		if ($status)
			return $new_akey;
		
		return FALSE;
	}
	
	/**
	 * Get all API keys that are connected to the given account.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the keys for.
	 * 
	 * @return	array
	 * 		An array of all API keys and their information.
	 */
	public static function getAccountKeys($accountID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("19336515113049", "security.akeys");
		
		// Get keys
		$attr = array();
		$attr['aid'] = (empty($accountID) ? account::getInstance()->getAccountID() : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all API keys connected to a given team for a given account.
	 * 
	 * @param	integer	$teamID
	 * 		The team id to get the keys for.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get the keys for.
	 * 
	 * @return	array
	 * 		An array of all API keys and their information.
	 */
	public static function getTeamKeys($teamID, $accountID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("25480730106687", "security.akeys");
		
		// Get keys
		$attr = array();
		$attr['tid'] = $teamID;
		$attr['aid'] = (empty($accountID) ? account::getInstance()->getAccountID() : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all API keys between a given project and an account.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @return	array
	 * 		An array of all API keys and their information.
	 */
	public static function getProjectAccountKeys($projectID, $accountID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("27759032808404", "security.akeys");
		
		// Get keys
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['aid'] = (empty($accountID) ? account::getInstance()->getAccountID() : $accountID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all API keys between a given project and a team (public keys, no user group).
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @return	array
	 * 		An array of all API keys and their information.
	 */
	public static function getProjectTeamKeys($projectID, $teamID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("18678823830522", "security.akeys");
		
		// Get keys
		$attr = array();
		$attr['pid'] = $projectID;
		$attr['tid'] = (empty($teamID) ? team::getTeamID() : $teamID);
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Validate whether a given key with the given parameters exist.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validateKey($akey, $accountID = NULL, $teamID = NULL, $projectID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("20388119727084", "security.akeys");
		
		// Validate key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$attr['tid'] = (empty($teamID) ? "NULL" : $teamID);
		$attr['pid'] = (empty($projectID) ? "NULL" : $projectID);
		$attr['etime'] = time();
		$result = $dbc->execute($q, $attr);
		return ($dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Validate a given api key with the given project id.
	 * This function is for the platform API where keys must be matched with the running application.
	 * 
	 * @param	string	$akey
	 * 		The api key.
	 * 		This key can be of any type including public keys.
	 * 
	 * @param	integer	$projectID
	 * 		The project id to validate.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	public static function validateProjectKey($akey, $projectID)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("21813699002127", "security.akeys");
		
		// Validate key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['pid'] = $projectID;
		$attr['etime'] = time();
		$result = $dbc->execute($q, $attr);
		return ($dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Validate whether a given key of a specific type with the given parameters exists.
	 * 
	 * @param	string	$akey
	 * 		The API key.
	 * 
	 * @param	integer	$typeID
	 * 		The API key type id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validateFullKey($akey, $typeID, $accountID = NULL, $teamID = NULL, $projectID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("18687149859761", "security.akeys");
		
		// Validate key
		$attr = array();
		$attr['akey'] = $akey;
		$attr['type'] = $typeID;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$attr['tid'] = (empty($teamID) ? "NULL" : $teamID);
		$attr['pid'] = (empty($projectID) ? "NULL" : $projectID);
		$attr['etime'] = time();
		$result = $dbc->execute($q, $attr);
		return ($dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Validate whether there is any key of the given type and the given parameters.
	 * 
	 * @param	integer	$typeID
	 * 		The API key type id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validateType($typeID, $accountID = NULL, $teamID = NULL, $projectID = NULL)
	{
		// Set Database Connection
		$dbc = new dbConnection();
		$q = new dbQuery("26056232758189", "security.akeys");
		
		// Validate key
		$attr = array();
		$attr['type'] = $typeID;
		$attr['aid'] = (empty($accountID) ? "NULL" : $accountID);
		$attr['tid'] = (empty($teamID) ? "NULL" : $teamID);
		$attr['pid'] = (empty($projectID) ? "NULL" : $projectID);
		$attr['etime'] = time();
		$result = $dbc->execute($q, $attr);
		return ($dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Validate whether the given account id is part of the given group id on the given team or project with an existing key.
	 * 
	 * @param	integer	$groupID
	 * 		The group id to validate.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validateGroup($groupID, $accountID, $teamID = NULL, $projectID = NULL)
	{
		// Get key type from group id
		$typeInfo = apiKeyType::getKeyTypeByGroupID($groupID);
		$typeID = $typeInfo['id'];
		
		// Validate key type
		return self::validateType($typeID, $accountID, $teamID, $projectID);
	}
	
	/**
	 * Validate whether the given account id is part of the given group name on the given team or project with an existing key.
	 * 
	 * @param	string	$groupName
	 * 		The group name to validate the account for.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$teamID
	 * 		The team id.
	 * 
	 * @param	integer	$projectID
	 * 		The project id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function validateGroupName($groupName, $accountID, $teamID = NULL, $projectID = NULL)
	{
		// Get key type from group name
		$typeInfo = apiKeyType::getKeyTypeByGroupName($groupName);
		$typeID = $typeInfo['id'];
		
		// Validate key type
		return self::validateType($typeID, $accountID, $teamID, $projectID);
	}
	
	/**
	 * Generate a key.
	 * 
	 * @param	string	$prefix
	 * 		The key prefix.
	 * 
	 * @param	string	$value
	 * 		The key value.
	 * 
	 * @return	string
	 * 		The generated key.
	 */
	private static function generateKey($prefix, $value)
	{
		return hash("sha256", "api_key_".$prefix."_".$value."_".time()."_".mt_rand());
	}
}
//#section_end#
?>