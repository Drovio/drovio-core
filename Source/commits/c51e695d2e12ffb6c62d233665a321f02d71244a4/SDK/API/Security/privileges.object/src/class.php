<?php
//#section#[header]
// Namespace
namespace API\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Security
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Comm", "database::connections::interDbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Profile", "tester");
importer::import("API", "Resources", "storage::session");
importer::import("API", "Security", "account");
importer::import("API", "Security", "accessControl");

use \API\Comm\database\connections\interDbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Profile\tester;
use \API\Resources\storage\session;
use \API\Security\account;
use \API\Security\accessControl;

/**
 * Privileges Manager
 * 
 * Manages all the privileges the account has according to user groups and module access privileges.
 * 
 * @version	{empty}
 * @created	July 2, 2013, 14:40 (EEST)
 * @revised	November 27, 2013, 10:30 (EET)
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
	 * All account's user groups.
	 * 
	 * @type	array
	 */
	private static $userGroups = array();
	/**
	 * Gets the access of the given module of the given account. If no account given, its the logged in user (or guest).
	 * 
	 * @param	integer	$moduleID
	 * 		The module id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id requesting access.
	 * 
	 * @return	string
	 * 		The access status.
	 * 		"user", "auth", "onauth", "uc", "off", "no".
	 */
	public static function moduleAccess($moduleID, $accountID = NULL)
	{
		// Get Current Account ID
		$currentAccountID = account::getAccountID();
		
		// If accountID is null, logged in account requests access
		// Otherwise, it is an auth request.
		$accID = $accountID;
		if (is_null($accID) && isset($currentAccountID))
			$accID = $currentAccountID;
		
		// Check static
		if (isset(self::$moduleAccess[$moduleID."_".$accID]))
			return self::$moduleAccess[$moduleID."_".$accID];
		
		// Initialize allowed variable as NO
		$access = "no";
		
		// Get access status from database
		$access = self::getDBModuleAccess($moduleID, $accID);

		// Return status filtered with testers
		$testerAccess = self::testerStatus($moduleID, $access);
		
		// Set variable
		self::$moduleAccess[$moduleID."_".$accID] = $testerAccess;
		
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
		$dbc = new interDbConnection();
		
		// Set attributes
		$attr = array();
		$attr['moduleID'] = $moduleID;
		
		// Check if this is guest or registered user
		if (!is_null($accountID))
		{
			$dbq = new dbQuery("381376174", "security.modules");
			$attr['accountID'] = $accountID;
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
		$testerStatus = tester::testerModule($moduleID);
		
		// If status is not off, tester can execute as user
		if ($testerStatus && $access != "off" && $access != "open")
			return "user";
			
		return $access;
	}
	
	/**
	 * Returns whether the module can proceed with executtion according to given access.
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
			
			case "open":		//_____ Open command
			case "user":		//_____ User can execute
			case "user_open":	//_____ User can execute after authentication
				return TRUE;
			case "onauth":		//_____ User can execute but system needs user's authorization (protected module)
			case "auth":		//_____ User cannot execute and system needs authorization (public module)
			case "no":		//_____ User cannot execute and system denies access (protected|private module or not exists)
			case "uc":		//_____ Module : Under Construction
			case "off":		//_____ Module : Module removed
				return FALSE;
		}
		
		return FALSE;
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
			$dbc = new interDbConnection();
			$dbq = new dbQuery("2085666253", "security.privileges.accounts");
			
			$attr = array();
			$attr['id'] = account::getAccountID();
			$result = $dbc->execute($dbq, $attr);
			self::$userGroups = $dbc->toArray($result, "id", "name");
		}
		
		return in_array($groupName, self::$userGroups);
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
		$dbc = new interDbConnection();
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
		$dbc = new interDbConnection();
		$dbq = new dbQuery("509989320", "security.privileges.accounts");
		
		$attr = array();
		$attr['account_id'] = $accountID;
		$attr['groupName'] = $groupName;
		$dbc->execute($dbq, $attr);
	}
	
	/**
	 * Checks if the logged in user is a member of the given group.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @param	integer	$company_id
	 * 		The company id.
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use accountToGroup() instead.
	 */
	public static function get_userToGroup($groupName, $company_id = NULL)
	{
		return TRUE;
	}
	
	/**
	 * Add a user to a group.
	 * 
	 * @param	string	$user_id
	 * 		The user's id
	 * 
	 * @param	string	$groupName
	 * 		The group's name
	 * 
	 * @param	integer	$company_id
	 * 		The company's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	This function is deprecated.
	 */
	public static function add_userToGroup($user_id, $groupName, $company_id = NULL)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
		
		// Initialize Database Connection
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1212820538", "security.privileges.user");
		
		$attr = array();
		$attr['user_id'] = $user_id;
		$attr['groupName'] = $groupName;
		$attr['company_id'] = (is_null($company_id) ? "NULL" : $company_id);
		$dbc->execute_query($dbq, $attr);
	}
	
	/**
	 * Add a user's account to a group
	 * 
	 * @param	integer	$account_id
	 * 		The user's account id
	 * 
	 * @param	string	$groupName
	 * 		The group's name
	 * 
	 * @param	integer	$company_id
	 * 		The company's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use addAccountToGroup() instead.
	 */
	public static function add_accountToGroup($account_id, $groupName, $company_id = NULL)
	{
		return self::addAccountToGroup($account_id, $groupName);
	}
	
	/**
	 * Leave a user's primary account from given userGroup
	 * 
	 * @param	string	$user_id
	 * 		The user's id
	 * 
	 * @param	string	$groupName
	 * 		The group's name
	 * 
	 * @param	integer	$company_id
	 * 		The company's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	This function is deprecated.
	 */
	public static function leave_userFromGroup($user_id, $groupName, $company_id = NULL)
	{
		// Check core functionality
		if (!accessControl::internalCall())
			return FALSE;
			
		// Initialize Database Connection
		$dbc = new interDbConnection();
		$dbq = new dbQuery("1400229168", "security.privileges.user");
		
		$attr = array();
		$attr['user_id'] = $user_id;
		$attr['groupName'] = $groupName;
		$attr['company_id'] = (is_null($company_id) ? "NULL" : $company_id);
		$dbc->execute_query($dbq, $attr);
		
		return TRUE;
	}
	
	/**
	 * Remove an account from the given group
	 * 
	 * @param	integer	$account_id
	 * 		The user's account id
	 * 
	 * @param	string	$groupName
	 * 		The group's name
	 * 
	 * @param	integer	$company_id
	 * 		The company's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use leaveAccountFromGroup() instead.
	 */
	public static function leave_accountFromGroup($account_id, $groupName, $company_id = NULL)
	{
		return self::leaveAccountFromGroup($account_id, $groupName);
	}
	
	
}
//#section_end#
?>