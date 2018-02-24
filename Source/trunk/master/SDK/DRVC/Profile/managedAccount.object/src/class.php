<?php
//#section#[header]
// Namespace
namespace DRVC\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Model", "sql/dbQuery");
importer::import("DRVC", "Comm", "dbConnection");
importer::import("DRVC", "Profile", "account");

use \API\Model\sql\dbQuery;
use \DRVC\Comm\dbConnection;
use \DRVC\Profile\account;

/**
 * Managed Account Handler
 * 
 * This class is responsible for managed accounts (not admin).
 * 
 * @version	1.0-1
 * @created	October 8, 2015, 12:21 (BST)
 * @updated	November 10, 2015, 14:50 (GMT)
 */
class managedAccount
{
	/**
	 * The team name for the identity database.
	 * 
	 * @type	string
	 */
	protected $teamName = "";
	
	/**
	 * The account instance.
	 * 
	 * @type	account
	 */
	protected $account;
	
	/**
	 * The identity database connection.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * An array of instances for each team identity (in case of multiple instances).
	 * 
	 * @type	array
	 */
	private static $instances = array();
	
	/**
	 * Static team name for compatibility.
	 * 
	 * @type	string
	 */
	private static $staticTeamName = "";
	
	/**
	 * Get an identity managed account instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name for the identity database.
	 * 
	 * @return	managedAccount
	 * 		The managed account instance.
	 */
	public static function getInstance($teamName)
	{
		// Check for an existing instance
		if (!isset(self::$instances[$teamName]))
			self::$instances[$teamName] = new managedAccount($teamName);
		
		// Return instance
		return self::$instances[$teamName];
	}
	
	/**
	 * Create a new managed account instance.
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
		$this->account = account::getInstance($this->teamName);
	}
	
	/**
	 * Initialze the environment for the identity database.
	 * 
	 * @param	string	$teamName
	 * 		The team name.
	 * 
	 * @return	void
	 */
	public function init($teamName)
	{
		self::$staticTeamName = $teamName;
	}
	
	/**
	 * Get all managed accounts.
	 * 
	 * @return	array
	 * 		An array of all managed account information.
	 */
	public function getManagedAccounts()
	{
		$q = new dbQuery("28925474633954", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $this->account->getAccountID();
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Create a new managed account for the current logged in account.
	 * 
	 * @param	string	$title
	 * 		The account title.
	 * 
	 * @param	string	$description
	 * 		The account description.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 
	 * @param	string	$password
	 * 		The account password.
	 * 
	 * @param	boolean	$locked
	 * 		Whether the account is locked or not.
	 * 
	 * @return	boolean
	 * 		The account id on success, false on failure.
	 */
	public function create($title, $description, $username, $password, $locked)
	{
		$q = new dbQuery("30560478974513", "identity.account.managed");
		$attr = array();
		$attr['parent_id'] = $this->account->getAccountID();
		$attr['pid'] = $this->account->getPersonID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$attr['locked'] = ($locked ? 1 : 0);
		$result = $this->dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		$accountInfo = $this->dbc->fetch($result);
		return $accountInfo['id'];
	}
	
	/**
	 * Get all managed account information.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to get information for.
	 * 
	 * @return	array
	 * 		An array of all account information.
	 */
	public function info($accountID)
	{
		$q = new dbQuery("17108822764189", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = $this->account->getAccountID();
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
	}
	
	/**
	 * Remove the given account from the identity database.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($accountID)
	{
		$q = new dbQuery("31807713853814", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = $this->account->getAccountID();
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Update account info.
	 * 
	 * @param	integer	$accountID
	 * 		The account id to update.
	 * 
	 * @param	string	$title
	 * 		The account title.
	 * 
	 * @param	string	$description
	 * 		The account description.
	 * 
	 * @param	string	$username
	 * 		The account username.
	 * 
	 * @param	boolean	$locked
	 * 		Whether the account is locked or not.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure (usually conflict in username).
	 */
	public function updateInfo($accountID, $title, $description, $username, $locked)
	{
		$q = new dbQuery("2239103499014", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = $this->account->getAccountID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['username'] = $username;
		$attr['locked'] = ($locked ? 1 : 0);
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Update account password.
	 * 
	 * @param	integer	$accountID
	 * 		The account id.
	 * 
	 * @param	string	$password
	 * 		The new account password.
	 * 
	 * @param	string	$currentPassword
	 * 		The admin (current) account password to authenticate.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updatePassword($accountID, $password, $currentPassword)
	{
		// Authenticate current user
		if (!$this->account->authenticate($this->account->getUsername(TRUE), $currentPassword))
			return FALSE;
			
		// Update password
		$q = new dbQuery("19986531147984", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = $this->account->getAccountID();
		$attr['password'] = hash("SHA256", $password);
		return $this->dbc->execute($q, $attr);
	}
}
//#section_end#
?>