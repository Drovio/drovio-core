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
 * @library	UBA
 * @package	Comm
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/adbConnection");

use \SYS\Comm\db\adbConnection;

/**
 * dbConnection
 * 
 * Manages the connection to the database.
 * 
 * @version	0.1-1
 * @created	December 1, 2015, 18:18 (GMT)
 * @updated	December 1, 2015, 18:18 (GMT)
 */
class dbConnection extends adbConnection
{
	/**
	 * Creates a dbConnection object.
	 * 
	 * @param	string	$teamName
	 * 		The team name.
	 * 
	 * @return	dbConnection
	 * 		{description}
	 */
	public function __construct($teamName = "drovio")
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings("uba");
		
		// Set database name according to given name
		$DB_NAME = $settings['DB_NAME'].".".$teamName;
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