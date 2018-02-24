<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Profile", "account");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Profile\account;

/**
 * Person Class
 * 
 * Manages all person data.
 * 
 * @version	0.1-5
 * @created	December 31, 2013, 12:34 (EET)
 * @updated	July 24, 2015, 11:49 (EEST)
 */
class person
{
	/**
	 * All person info.
	 * 
	 * @type	array
	 */
	private static $info = array();
	
	/**
	 * Get all person info from the logged in person.
	 * 
	 * @return	array
	 * 		An array of all person information.
	 */
	public static function info()
	{
		// Get current person id
		$personID = account::getPersonID();
		
		// Check cache
		if (!isset(self::$info))
		{
			// Get info from database
			$dbc = new dbConnection();
			$q = new dbQuery("1921568048", "profile.person");
			$attr = array();
			$attr['pid'] = $personID;
			$result = $dbc->execute($q, $attr);
			self::$info = $dbc->fetch($result);
		}
		
		// Return information
		return self::$info;
	}
	
	/**
	 * Get the person's username.
	 * If person doesn't have username, return the email.
	 * 
	 * @return	string
	 * 		The person's username.
	 */
	public static function getUsername()
	{
		return self::getPersonValue("username");
	}
	
	/**
	 * Get the person's mail.
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