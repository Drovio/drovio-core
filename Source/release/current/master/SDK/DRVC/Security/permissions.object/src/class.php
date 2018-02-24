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
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "identity");

use \API\Model\sql\dbQuery;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\identity;

/**
 * Account permission manager with groups
 * 
 * Handles account permissions using user groups.
 * This class handles accounts into groups.
 * 
 * @version	1.0-1
 * @created	November 22, 2015, 14:50 (GMT)
 * @updated	December 17, 2015, 12:53 (GMT)
 */
class permissions
{
	/**
	 * The team to access the identity database.
	 * 
	 * @type	string
	 */
	private $teamName = "";
	
	/**
	 * The identity database connection.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * Array to keep the account's group like cache.
	 * 
	 * @type	array
	 */
	private $accountGroups = array();
	
	/**
	 * An array of instances for each team identity (in case of multiple instances).
	 * 
	 * @type	array
	 */
	private static $instances = array();
	
	/**
	 * Get the identity dbConnection instance for the current team.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	dbConnection
	 * 		The dbConnection instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new permissions($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Create a new permissions instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	void
	 */
	protected function __construct($teamName)
	{
		$this->teamName = $teamName;
		$this->dbc = new dbConnection($this->teamName);
	}
	
	/**
	 * Activate the permissions feature for the current identity database.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function activate()
	{
		// Check current status
		if ($this->status())
			return TRUE;
		
		// Setup identity permissions
		return identity::setupPermissions($this->teamName);
	}
	
	/**
	 * Check if the permissions feature is enabled for the current identity database.
	 * 
	 * @return	boolean
	 * 		True if enabled, false otherwise.
	 */
	public function status()
	{
		// Check if the permission tables exist
		$q = new dbQuery("33010913570535", "identity.permissions");
		$result = $this->dbc->execute($q);
		
		// Check if there are the permission tables in the database
		return ($this->dbc->get_num_rows($result) > 0);
	}
	
	/**
	 * Get all permission groups.
	 * 
	 * @return	array
	 * 		An associative array of groups by id and name.
	 */
	public function getAllGroups()
	{
		// Check status
		if (!$this->status())
			return NULL;
		
		// Get all user groups
		$q = new dbQuery("24833855183425", "identity.permissions");
		$result = $this->dbc->execute($q);
		return $this->dbc->toArray($result, "id", "name");
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
	public function addGroup($groupName)
	{
		// Check status
		if (!$this->status())
			return FALSE;
		
		// Create permissions group
		$q = new dbQuery("17007473308693", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['gname'] = $groupName;
		return $this->dbc->execute($q, $attr);
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
	public function removeGroup($groupID = "", $groupName = "")
	{
		// Check status
		if (!$this->status())
			return FALSE;
		
		// Remove permissions group
		$q = new dbQuery("22668881270476", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['gid'] = $groupID;
		$attr['gname'] = $groupName;
		return $this->dbc->execute($q, $attr);
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
	public function getAccountGroups($accountID)
	{
		// Check status
		if (!$this->status())
			return FALSE;
		
		// Get all account groups
		$q = new dbQuery("25399829914159", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->toArray($result, "id", "name");
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
	public function addAccountGroup($accountID, $groupID)
	{
		// Check status
		if (!$this->status())
			return FALSE;
		
		// Add account to group
		$q = new dbQuery("21385905925147", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['gid'] = $groupID;
		return $this->dbc->execute($q, $attr);
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
	public function removeAccountGroup($accountID, $groupID)
	{
		// Check status
		if (!$this->status())
			return FALSE;
		
		// Remove account from group
		$q = new dbQuery("25513447394645", "identity.permissions");
		
		// Set attributes and execute
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['gid'] = $groupID;
		return $this->dbc->execute($q, $attr);
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
	public function validateAccountGroup($accountID, $groupID)
	{
		// Get all account groups
		if (empty($this->accountGroups[$accountID]))
			$this->accountGroups[$accountID] = $this->getAccountGroups($accountID);
		
		// Check if group id is in there
		return isset($this->accountGroups[$accountID][$groupID]);
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
	public function validateAccountGroupName($accountID, $groupName)
	{
		// Get all account groups
		if (empty($this->accountGroups[$accountID]))
			$this->accountGroups[$accountID] = $this->getAccountGroups($accountID);
		
		// Check if group id is in there
		return in_array($groupName, $this->accountGroups[$accountID]);
	}
}
//#section_end#
?>