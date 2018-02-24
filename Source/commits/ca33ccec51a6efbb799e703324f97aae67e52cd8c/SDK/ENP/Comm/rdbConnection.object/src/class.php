<?php
//#section#[header]
// Namespace
namespace ENP\Comm;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ENP
 * @package	Comm
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Comm", "database/dbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \API\Comm\database\dbConnection as APIdbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Drovio Relations Database Connection Handler
 * 
 * Connects to Drovio relations database and executes all the core sql queries.
 * 
 * @version	0.2-1
 * @created	August 20, 2015, 10:04 (EEST)
 * @updated	October 9, 2015, 13:00 (EEST)
 */
class rdbConnection extends APIdbConnection
{
	/**
	 * The database server settings manager object.
	 * 
	 * @type	settingsManager
	 */
	private static $settings;
	
	/**
	 * Create a database connector instance.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings();

		// Set Database Options
		$this->options($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
	}
	
	/**
	 * Executes a core sql query (dbQuery) to the relations database.
	 * 
	 * @param	dbQuery	$dbQuery
	 * 		The query to be executed.
	 * 
	 * @param	array	$attr
	 * 		A number of attributes to be passed as arguments to the query.
	 * 		It is an associative array to respond to the sql parameters.
	 * 
	 * @param	boolean	$commit
	 * 		Whether to commit the transaction  after the last query or not.
	 * 		It is TRUE by default.
	 * 
	 * @return	mixed
	 * 		The query result as a resource.
	 */
	public function execute($dbQuery, $attr = array(), $commit = TRUE)
	{
		// Execute
		return parent::execute($dbQuery->getQuery(), $attr, $commit);
	}
	
	/**
	 * Gets the server credentials handler.
	 * 
	 * @return	settingsManager
	 * 		The settingsManager object.
	 */
	private function getServerSettings()
	{
		if (empty(self::$settings))
		{
			$dbSettings = new dbSettings("relations");
			self::$settings = $dbSettings->get();
		}
		
		return self::$settings;
	}
}
//#section_end#
?>