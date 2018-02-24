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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
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
 * @version	0.1-1
 * @created	October 8, 2015, 14:21 (EEST)
 * @updated	October 8, 2015, 14:21 (EEST)
 */
class managedAccount
{
	/**
	 * The team name for the identity database.
	 * 
	 * @type	string
	 */
	private static $teamName = "";
	
	/**
	 * Initialze the environment for the identity database.
	 * 
	 * @param	string	$teamName
	 * 		The team name.
	 * 
	 * @return	void
	 */
	public static function init($teamName)
	{
		self::$teamName = $teamName;
	}
	
	/**
	 * Get all managed accounts.
	 * 
	 * @return	array
	 * 		An array of all managed account information.
	 */
	public static function getManagedAccounts()
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("28925474633954", "identity.account.managed");
		$attr = array();
		$attr['aid'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
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
	public static function create($title, $description, $username, $password, $locked)
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("30560478974513", "identity.account.managed");
		$attr = array();
		$attr['parent_id'] = account::getAccountID();
		$attr['pid'] = account::getPersonID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['username'] = $username;
		$attr['password'] = hash("SHA256", $password);
		$attr['locked'] = ($locked ? 1 : 0);
		$result = $dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		$accountInfo = $dbc->fetch($result);
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
	public static function info($accountID)
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("17108822764189", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = account::getAccountID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
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
	public static function remove($accountID)
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("31807713853814", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = account::getAccountID();
		return $dbc->execute($q, $attr);
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
	public static function updateInfo($accountID, $title, $description, $username, $locked)
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("2239103499014", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = account::getAccountID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		$attr['username'] = $username;
		$attr['locked'] = ($locked ? 1 : 0);
		return $dbc->execute($q, $attr);
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
	public static function updatePassword($accountID, $password, $currentPassword)
	{
		// Authenticate current user
		if (!account::authenticate(account::getUsername(TRUE), $currentPassword))
			return FALSE;
			
		// Update password
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("19986531147984", "identity.account.managed");
		$attr = array();
		$attr['aid'] = $accountID;
		$attr['parent_id'] = account::getAccountID();
		$attr['password'] = hash("SHA256", $password);
		return $dbc->execute($q, $attr);
	}
}
//#section_end#
?>