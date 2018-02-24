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

use \API\Comm\database\dbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Abstract system database connection handler
 * 
 * Connects to MySQL database and executes platform's dbQueries.
 * 
 * @version	0.1-1
 * @created	October 9, 2015, 14:47 (EEST)
 * @updated	October 9, 2015, 14:47 (EEST)
 */
abstract class adbConnection extends dbConnection
{
	/**
	 * The database server settings.
	 * 
	 * @type	array
	 */
	private static $settings;
	
	/**
	 * Executes a platform sql query (dbQuery) to the redback database.
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
	 * Get the server connection credentials.
	 * 
	 * @param	string	$settingsName
	 * 		The settings file name to get from.
	 * 
	 * @return	array
	 * 		The server credentials.
	 */
	final protected function getServerSettings($settingsName)
	{
		// Check cache and load settings
		if (empty(self::$settings[$settingsName]))
		{
			$dbSettings = new dbSettings($settingsName);
			self::$settings[$settingsName] = $dbSettings->get();
		}
		
		// Return settings object
		return self::$settings[$settingsName];
	}
}
//#section_end#
?>