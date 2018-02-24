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

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Platform", "accessControl");
importer::import("API", "Profile", "account");
importer::import("API", "Security", "accountKey");
importer::import("API", "Security", "akeys/apiKey");
importer::import("DEV", "Modules", "test/moduleSecurityTester");
importer::import("DEV", "Profile", "tester");
importer::import("DEV", "Profiler", "logger");
importer::import("SYS", "Comm", "db/dbConnection");

use \API\Model\sql\dbQuery;
use \API\Platform\accessControl;
use \API\Profile\account;
use \API\Security\accountKey;
use \API\Security\akeys\apiKey;
use \DEV\Modules\test\moduleSecurityTester;
use \DEV\Profile\tester;
use \DEV\Profiler\logger;
use \SYS\Comm\db\dbConnection;

/**
 * Privileges Manager
 * 
 * Manages all the privileges the account has according to user groups and module access privileges.
 * 
 * @version	2.0-2
 * @created	July 2, 2013, 12:40 (BST)
 * @updated	December 16, 2015, 18:37 (GMT)
 */
class privileges
{
	/**
	 * All module access during this script execution. Expands incrementally.
	 * 
	 * @type	array
	 */
	private static $moduleAccess = array();
	
	/**
	 * All key access during this script execution. Expands incrementally.
	 * 
	 * @type	array
	 */
	private static $keyAccess = array();
	
	/**
	 * All account's user groups.
	 * 
	 * @type	array
	 */
	private static $userGroups = array();
	
	/**
	 * Gets the access of the given module for the logged in user (or guest).
	 * Given a condition key, it can check the group participation under condition.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @return	string
	 * 		The access status.
	 * 		- "Open" for modules that are open o everyone.
	 * 		- "User" for modules that are open for registered users.
	 * 		- "No" to deny access because user is not in user group or guest.
	 * 		- "Uc" when module is under construction and the access is denied for now.
	 * 		- "Off" when the module is set as deleted and it won't be restored.
	 */
	public static function moduleAccess($moduleID)
	{
		// Get Current Account ID
		$accID = pAccount::getAccountID();
		if (empty($accID))
			$accID = NULL;
		
		// Check static
		if (isset(self::$moduleAccess[$moduleID]))
			return self::$moduleAccess[$moduleID];
		
		// Initialize allowed variable as NO
		$access = "no";
		
		// Get access status from database
		$access = self::getDBModuleAccess($moduleID, $accID);
		
		// Log initial access status
		logger::log("Security Privileges: Module [".$moduleID."] account access [".$access."].", logger::INFO);

		// Return status filtered with testers
		$testerAccess = self::testerStatus($moduleID, $access);
		
		// Log tester access status
		logger::log("Security Privileges: Module [".$moduleID."] tester access [".$testerAccess."].", logger::INFO);

		// Set variable
		self::$moduleAccess[$moduleID] = $testerAccess;
		
		// Return access
		return $testerAccess;
	}
	
	/**
	 * Gets the access from the database.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @return	string
	 * 		The access status.
	 */
	private static function getDBModuleAccess($moduleID, $accountID = NULL)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		
		// Set attributes
		$attr = array();
		$attr['moduleID'] = $moduleID;
		
		// Check if this is guest or registered user
		if (!is_null($accountID))
		{
			// Add account
			$attr['accountID'] = $accountID;
			$dbq = new dbQuery("16283295428182", "security.modules");
		}
		else
			$dbq = new dbQuery("1613241932", "security.modules");
		
		// Execute Query
		$result = $dbc->execute($dbq, $attr);
		$moduleAccess = $dbc->fetch($result);
		return $moduleAccess['access'];
	}
	
	/**
	 * Fixes the access status if the user is tester of the given module.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	string	$access
	 * 		The old access status.
	 * 
	 * @return	string
	 * 		The new access status.
	 */
	private static function testerStatus($moduleID, $access)
	{
		// Check if the security tester mode is on to proceed
		$securityTesterStatus = moduleSecurityTester::status();
		if (!$securityTesterStatus)
			return $access;
		
		// Check if the module is marked for testing for the current developer
		// And whether the module tester mode is on
		$testerStatus = tester::testerModule($moduleID);
		
		// If status is not off, tester can execute as user
		if ($testerStatus && $access != "off" && $access != "open")
			return "user";
			
		return $access;
	}
	
	/**
	 * Returns whether the module can proceed with execution according to given access.
	 * 
	 * @param	string	$access
	 * 		The module access.
	 * 
	 * @return	boolean
	 * 		True if can proceed, false otherwise.
	 */
	public static function canProceed($access)
	{
		switch ($access)
		{
			case "open":
			case "user":		// User can execute
				return TRUE;
			case "no":		// User cannot execute (Guest or protected with no access)
			case "uc":		// Module : Under Construction
			case "off":		// Module : Module removed
				return FALSE;
		}
		
		return FALSE;
	}
	
	/**
	 * Checks if the current account has specific access with the key type given.
	 * The key type defines a context that the function gets from the core and the check is based on this context.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to check for key access.
	 * 
	 * @param	integer	$keyType
	 * 		The key type according to the database.
	 * 
	 * @param	string	$keyContext
	 * 		The key context value to check access.
	 * 
	 * @return	mixed
	 * 		False if there is no specific access.
	 * 		True if there is a key matching the given key or if there are multiple keys.
	 * 		The key string if there is no given key.
	 */
	public static function keyAccess($moduleID, $keyType, $keyContext = "")
	{
		// Get Current Account ID
		$accID = pAccount::getAccountID();
		if (empty($accID))
		{
			// User is guest, log and return false
			logger::log("Security Privileges: Trying to get key access for guest. Aborting.", logger::ERROR);
			return FALSE;
		}
		
		// Get key context (if empty)
		if (empty($keyContext))
			$keyContext = accountKey::getContext($keyType);
		// Check key context
		if (empty($keyContext))
		{
			logger::log("Security Privileges: No context for the requested key access type. Returning FALSE.", logger::ERROR);
			return FALSE;
		}
		
		// Check tester
		if (moduleSecurityTester::status())
		{
			logger::log("Security Privileges: Module [".$moduleID."] is in tester mode, allow execution.", logger::INFO);
			return TRUE;
		}

		// Log activity
		logger::log("Security Privileges: Getting key access for module [".$moduleID."] with key [type, context]:[".$keyType.",".$keyContext."].", logger::INFO);
		
		// Check static and get from database
		$accessKey = $moduleID."_".$keyType."_".$keyContext;
		if (!isset(self::$keyAccess[$accessKey]))
			self::$keyAccess[$accessKey] = self::getDBKeyAccess($moduleID, $accID, $keyType, $keyContext);
		
		// Return key
		return self::$keyAccess[$accessKey];
	}
	
	/**
	 * Gets the key access from the database.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to check for key access.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to check for the key.
	 * 
	 * @param	integer	$keyType
	 * 		The key type to check.
	 * 
	 * @param	integer	$keyContext
	 * 		The key context.
	 * 
	 * @return	mixed
	 * 		False if there is no specific access.
	 * 		True if there is a key matching the given key or if there are multiple keys.
	 * 		The key string if there is no given key.
	 */
	private static function getDBKeyAccess($moduleID, $accountID, $keyType, $keyContext)
	{
		// Get given project (or team) keys
		$keyList = array();
		if ($keyType == 1)
			$keyList = apiKey::getTeamKeys($keyContext, $accountID);
		else
			$keyList = apiKey::getProjectAccountKeys($keyContext, $accountID);

		// Get all module user groups
		$userGroups = self::getModuleUserGroups($moduleID);

		$validKeys = array();
		foreach ($keyList as $keyInfo)
			if (isset($userGroups[$keyInfo['user_group_id']]))
				$validKeys[] = $keyInfo;

		// Check if there are valid keys
		if (count($validKeys) == 0)
			return FALSE;
		else if (count($validKeys) > 1)
			return TRUE;
		
		// Return first key
		return $validKeys[0]['akey'];
	}
	
	/**
	 * Get all user groups that allow the given module to execute.
	 * 
	 * @param	integer	$moduleID
	 * 		The module id to check.
	 * 
	 * @return	array
	 * 		An array of all user group ids by key and value.
	 */
	private static function getModuleUserGroups($moduleID)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("33342876866922", "security.privileges.modules");
		
		// Set attributes
		$attr = array();
		$attr['mid'] = $moduleID;
		
		// Execute Query
		$result = $dbc->execute($dbq, $attr);
		return $dbc->toArray($result, "userGroup_id", "userGroup_id");
	}
	
	/**
	 * Checks whether the current account exists in a given groupName.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	boolean
	 * 		True if exists, false otherwise.
	 */
	public static function accountToGroup($groupName)
	{
		// If users groups are not loaded, load from database
		if (empty(self::$userGroups))
		{
			// Validate account id
			$accountID = pAccount::getAccountID();
			if (empty($accountID))
				return FALSE;
				
			$dbc = new dbConnection();
			$dbq = new dbQuery("2085666253", "security.privileges.accounts");
			
			$attr = array();
			$attr['id'] = $accountID;
			$result = $dbc->execute($dbq, $attr);
			self::$userGroups = $dbc->toArray($result, "id", "name");
		}
		
		return in_array($groupName, self::$userGroups);
	}
	
	/**
	 * Adds an account to a given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to add to the group.
	 * 
	 * @param	integer	$groupID
	 * 		The group id to add the account to.
	 * 
	 * @return	void
	 */
	public static function addAccountToGroupID($accountID, $groupID)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("27660071666378", "security.privileges.accounts");
		
		$attr = array();
		$attr['group_id'] = $groupID;
		$attr['account_id'] = $accountID;
		$dbc->execute($dbq, $attr);
	}
	
	/**
	 * Adds an account to a given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	void
	 */
	public static function addAccountToGroup($accountID, $groupName)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("988949753", "security.privileges.accounts");
		
		$attr = array();
		$attr['groupName'] = $groupName;
		$attr['account_id'] = $accountID;
		$dbc->execute($dbq, $attr);
	}
	
	/**
	 * Removes an account from a given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to remove from the group.
	 * 
	 * @param	integer	$groupID
	 * 		The group id to remove the account from.
	 * 
	 * @return	void
	 */
	public static function leaveAccountFromGroupID($accountID, $groupID)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("1490764371047", "security.privileges.accounts");
		
		$attr = array();
		$attr['account_id'] = $accountID;
		$attr['group_id'] = $groupID;
		$dbc->execute($dbq, $attr);
	}
	
	/**
	 * Removes an account from a given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	void
	 */
	public static function leaveAccountFromGroup($accountID, $groupName)
	{
		// Initialize Database Connection
		$dbc = new dbConnection();
		$dbq = new dbQuery("509989320", "security.privileges.accounts");
		
		$attr = array();
		$attr['account_id'] = $accountID;
		$attr['groupName'] = $groupName;
		$dbc->execute($dbq, $attr);
	}
	
	
}
//#section_end#
?>