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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("SYS", "Comm", "db::dbConnection");
importer::import("API", "Model", "units::sql::dbQuery");
importer::import("API", "Security", "account");

use \SYS\Comm\db\dbConnection;
use \API\Model\units\sql\dbQuery;
use \API\Security\account;

/**
 * Person Class
 * 
 * Manages all person data.
 * 
 * @version	0.1-1
 * @created	December 31, 2013, 12:34 (EET)
 * @revised	August 1, 2014, 11:09 (EEST)
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
		$dbc = new dbConnection();
		$q = new dbQuery("1921568048", "profile.person");
		$attr = array();
		$attr['pid'] = account::getPersonID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result);
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
		$username = self::getPersonValue("username");
		if (empty($username))
			$username = self::getMail();
		
		return $username;
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
		// Check session existance
		if (isset(self::$info[$name]))
			return self::$info[$name];
		
		// If there is no session, update session
		self::$info = self::info();
		
		// Return new value
		return self::$info[$name];
	}
}
//#section_end#
?>