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
 * Person Identity
 * 
 * Manages a person identity
 * 
 * @version	0.1-1
 * @created	October 8, 2015, 14:29 (EEST)
 * @updated	October 8, 2015, 14:29 (EEST)
 */
class person
{
	/**
	 * All person information.
	 * 
	 * @type	array
	 */
	private static $info = array();
	
	/**
	 * The team name to access the identity database.
	 * 
	 * @type	string
	 */
	private static $teamName = "";
	
	/**
	 * Initialize the identity.
	 * 
	 * @param	string	$teamName
	 * 		The team to access the identity database.
	 * 
	 * @return	void
	 */
	public static function init($teamName)
	{
		self::$teamName = $teamName;
	}
	
	/**
	 * Get all person info from the logged in person.
	 * 
	 * @param	integer	$personID
	 * 		The person id to get information for.
	 * 		Leave empty for current person.
	 * 		It is empty by default.
	 * 
	 * @return	array
	 * 		An array of all person information.
	 */
	public static function info($personID = "")
	{
		// Get current person id
		$personID = (empty($personID) ? account::getPersonID() : $personID);

		// Check cache
		if (empty(self::$info[$personID]))
		{
			// Get database connection
			$dbc = new dbConnection(self::$teamName);
			
			// Get account info from database
			$q = new dbQuery("25113553281301", "identity.account");
			$attr = array();
			$attr['pid'] = $personID;
			$result = $dbc->execute($q, $attr);
			$info = $dbc->fetch($result);
			$accountInfo = account::info($info['id']);
			$accountInfo['accountID'] = $accountInfo['id'];
			$accountInfo['accountTitle'] = $accountInfo['title'];
			
			// Get person info
			$q = new dbQuery("27676107119787", "identity.person");
			$attr = array();
			$attr['pid'] = $personID;
			$result = $dbc->execute($q, $attr);
			$personInfo = $dbc->fetch($result);
			
			self::$info[$personID] = array_merge($accountInfo, $personInfo);
		}

		// Return information
		return self::$info[$personID];
	}
	
	/**
	 * Update person basic information.
	 * 
	 * @param	string	$firstname
	 * 		The person firstname.
	 * 
	 * @param	string	$lastname
	 * 		The person lastname.
	 * 
	 * @param	string	$middle_name
	 * 		The person middle name.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function updateInfo($firstname, $lastname, $middle_name = "")
	{
		$dbc = new dbConnection(self::$teamName);
		$q = new dbQuery("17222292713474", "identity.person");
		$attr = array();
		$attr['pid'] = account::getPersonID();
		$attr['firstname'] = $firstname;
		$attr['lastname'] = $lastname;
		$attr['middle_name'] = $middle_name;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get person's email.
	 * 
	 * @return	string
	 * 		The person's mail.
	 */
	public static function getMail()
	{
		return self::getPersonValue("mail");
	}
	
	/**
	 * Get the person's firstname.
	 * 
	 * @return	string
	 * 		The person's firstname.
	 */
	public static function getFirstname()
	{
		return self::getPersonValue("firstname");
	}
	
	/**
	 * Get the person's lastname.
	 * 
	 * @return	string
	 * 		The person's lastname.
	 */
	public static function getLastname()
	{
		return self::getPersonValue("lastname");
	}
	
	/**
	 * Get a person's information value from the database.
	 * 
	 * @param	string	$name
	 * 		The information name.
	 * 
	 * @return	string
	 * 		The information value.
	 */
	private static function getPersonValue($name)
	{
		// Get person information
		$personInfo = self::info();
		
		// Return requested value
		return $personInfo[$name];
	}
}
//#section_end#
?>