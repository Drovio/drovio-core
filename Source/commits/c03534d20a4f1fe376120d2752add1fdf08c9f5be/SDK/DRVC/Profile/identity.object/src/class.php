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
 * Identity manager
 * 
 * {description}
 * 
 * @version	1.1-1
 * @created	October 10, 2015, 17:04 (BST)
 * @updated	December 17, 2015, 11:50 (GMT)
 */
class identity
{
	/**
	 * The identity database name prefix.
	 * 
	 * @type	string
	 */
	const DB_PREFIX = "drovio.identity";
	
	/**
	 * Setup an identity database for the given team name.
	 * 
	 * @param	string	$teamName
	 * 		The team name to setup the database for.
	 * 
	 * @param	string	$demoPassword
	 * 		The password for the demo account.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setup($teamName, $demoPassword)
	{
		// Normalize team name
		$teamName = strtolower($teamName);
		
		// Setup database
		$dbc = new dbConnection(NULL);
		$q = new dbQuery("2132704406083", "identity.setup");
		$attr = array();
		$attr['db_name'] = self::DB_PREFIX.".".$teamName;
		$result = $dbc->execute($q, $attr);
		if (!$result)
			return FALSE;
		
		// Create demo account
		return account::getInstance($teamName)->create($email = "demo@identity.drov.io", $firstname = "Demo", $lastname = "Account", $demoPassword);
	}
	
	/**
	 * Setup the permissions extension for the identity database.
	 * 
	 * @param	string	$teamName
	 * 		The team name to setup the permissions for.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setupPermissions($teamName)
	{
		// Initialize db connection
		$dbc = new dbConnection($teamName);
		
		// Check if permissions exist
		$q = new dbQuery("31355374165147", "identity.setup");
		return $dbc->execute($q);
	}
}
//#section_end#
?>