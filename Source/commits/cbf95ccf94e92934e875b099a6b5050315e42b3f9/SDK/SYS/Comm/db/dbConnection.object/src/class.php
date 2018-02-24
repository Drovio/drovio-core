<?php
//#section#[header]
// Namespace
namespace SYS\Comm\db;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Comm
 * @namespace	\db
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Comm", "database/dbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \API\Comm\database\dbConnection as APIdbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Redback's Database Connection handler
 * 
 * Connects to Redback's database and executes all the red sql queries.
 * 
 * @version	0.5-1
 * @created	July 7, 2014, 11:44 (EEST)
 * @updated	October 7, 2015, 19:03 (EEST)
 */
class dbConnection extends APIdbConnection
{
	/**
	 * The database server settings.
	 * 
	 * @type	array
	 */
	private static $settings;
	
	/**
	 * Initializes the redback's connector.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings();

		// Call parent constructor
		parent::__construct($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
	}
	
	/**
	 * Executes a red sql query (dbQuery) to the redback database.
	 * 
	 * @param	dbQuery	$dbQuery
	 * 		The query to be executed.
	 * 
	 * @param	array	$attr
	 * 		A number of attributes to be passed as arguments to the query.
	 * 		It is an associative array.
	 * 		The array is empty by default.
	 * 
	 * @param	boolean	$commit
	 * 		Whether to commit the transaction  after the last query or not.
	 * 		It is TRUE by default.
	 * 
	 * @return	mixed
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
	 * 		Returns FALSE on failure.
	 */
	public function execute($dbQuery, $attr = array(), $commit = TRUE)
	{
		// Execute
		return parent::execute($dbQuery->getQuery(), $attr, $commit);
	}
	
	/**
	 * Gets the server credentials handler.
	 * 
	 * @return	array
	 * 		The server credentials.
	 */
	private function getServerSettings()
	{
		// Check cache and load settings
		if (empty(self::$settings))
		{
			$dbSettings = new dbSettings("platform");
			self::$settings = $dbSettings->get();
		}
		
		// Return settings object
		return self::$settings;
	}
}
//#section_end#
?>