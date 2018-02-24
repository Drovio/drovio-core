<?php
//#section#[header]
// Namespace
namespace UBA\Comm;

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
 * @package	Comm
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/adbConnection");

use \SYS\Comm\db\adbConnection;

/**
 * Drovio Identity Database Connection handler
 * 
 * {description}
 * 
 * @version	5.0-1
 * @created	October 7, 2015, 17:03 (BST)
 * @updated	October 23, 2015, 15:17 (BST)
 */
class dbConnection extends adbConnection
{
	/**
	 * Create a new database connection instance.
	 * 
	 * @param	string	$teamName
	 * 		The team name to connect to identity to.
	 * 
	 * @return	void
	 */
	public function __construct($teamName = "drovio")
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings("uba");
		
		// Set database name according to given name
		$DB_NAME = $settings['DB_NAME'].".drovio";
		if (!empty($teamName))
			$DB_NAME = $settings['DB_NAME'].".".$teamName;
		
		// Set null for emptying the database name
		if (is_null($teamName))
			$DB_NAME = NULL;

		// Call parent constructor
		parent::__construct($settings['SERVER_DBMS'], $settings['SERVER_URL'], $DB_NAME, $settings['USERNAME'], $settings['PASSWORD']);
	}
}
//#section_end#
?>