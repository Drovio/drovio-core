<?php
//#section#[header]
// Namespace
namespace DRVC\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DRVC
 * @package	Security
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "team");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "identity");

use \API\Model\sql\dbQuery;
use \API\Profile\team;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\identity;

/**
 * Account permission manager with groups
 * 
 * Handles account permissions using user groups.
 * This class handles accounts into groups.
 * 
 * @version	0.1-1
 * @created	November 22, 2015, 14:50 (GMT)
 * @updated	November 22, 2015, 14:50 (GMT)
 */
class permissions
{
	/**
	 * Activate the permissions feature for the current identity database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate()
	{
		// Check current status
		if (self::status())
			return TRUE;
		
		// Setup identity permissions
		return identity::setupPermissions();
	}
	
	/**
	 * Check if the permissions feature is enabled for the current identity database.
	 * 
	 * @return	boolean
	 * 		True if enabled, false otherwise.
	 */
	public static function status()
	{
		// Check if the permission tables exist
		$dbc = self::getDbConnection();
		$q = new dbQuery("33010913570535", "identity.permissions");
		$result = $dbc->execute($q);
		
		// Check if there are the permission tables in the database
		return ($dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Get all permission groups.
	 * 
	 * @return	array
	 * 		An associative array of groups by id and name.
	 */
	public static function getAllGroups()
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Get all user groups
		$dbc = self::getDbConnection();
		$q = new dbQuery("24833855183425", "identity.permissions");
		$result = $dbc->execute($q);
		return $dbc->toArray($result, "id", "name");
	}
	
	/**
	 * Add a new permission group to the database.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addGroup($groupName)
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Create permissions group
		$dbc = self::getDbConnection();
		$q = new dbQuery("17007473308693", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['gname'] = $groupName;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a permission group from the database.
	 * All account connections to this group will also be removed.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 		Set empty to select by name.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 		Set empty to select by id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeGroup($groupID = "", $groupName = "")
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Remove permissions group
		$dbc = self::getDbConnection();
		$q = new dbQuery("22668881270476", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['gid'] = $groupID;
		$attr['gname'] = $groupName;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get all groups that the given account is member of.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @return	array
	 * 		An associative array of all groups that the given account is member of.
	 */
	public static function getAccountGroups($accountID)
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Get all account groups
		$dbc = self::getDbConnection();
		$q = new dbQuery("25399829914159", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$result = $dbc->execute($q, $attr);
		return $dbc->toArray($result, "id", "name");
	}
	
	/**
	 * Add given account to given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addAccountGroup($accountID, $groupID)
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Add account to group
		$dbc = self::getDbConnection();
		$q = new dbQuery("21385905925147", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['gid'] = $groupID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Remove given account from given group.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function removeAccountGroup($accountID, $groupID)
	{
		// Check status
		if (!self::status())
			return NULL;
		
		// Remove account from group
		$dbc = self::getDbConnection();
		$q = new dbQuery("25513447394645", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['gid'] = $groupID;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Validate if the given account id is member of the given group id.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	integer	$groupID
	 * 		The group id.
	 * 
	 * @return	boolean
	 * 		True if account is member, false otherwise.
	 */
	public static function validateAccountGroup($accountID, $groupID)
	{
		// Get all account groups
		$accountGroups = self::getAccountGroups($accountID);
		
		// Check if group id is in there
		return isset($accountGroups[$groupID]);
	}
	
	/**
	 * Validate if the given account id is member of the given group name.
	 * NOTE: The group name is case sensitive.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$groupName
	 * 		The group name.
	 * 
	 * @return	string
	 * 		True if account is member, false otherwise.
	 */
	public static function validateAccountGroupName($accountID, $groupName)
	{
		// Get all account groups
		$accountGroups = self::getAccountGroups($accountID);
		
		// Check if group id is in there
		return in_array($groupName, $accountGroups);
	}
	
	/**
	 * Get the identity dbConnection instance for the current team.
	 * 
	 * @return	dbConnection
	 * 		The dbConnection instance.
	 */
	private static function getDbConnection()
	{
		// Get team name
		$teamName = strtolower(team::getTeamUname());
		return new dbConnection($teamName);
	}
}
//#section_end#
?>