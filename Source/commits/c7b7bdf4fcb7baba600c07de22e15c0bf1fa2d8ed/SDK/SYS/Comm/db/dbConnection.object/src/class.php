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
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
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
 * @version	0.2-2
 * @created	July 7, 2014, 11:44 (EEST)
 * @revised	December 22, 2014, 10:54 (EET)
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

		// Set Database Options
		$this->options($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
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
	 * 
	 * @return	mixed
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
	 * 		Returns FALSE on failure.
	 */
	public function execute($dbQuery, $attr = array())
	{
		// Escape values
		foreach ($attr as $key => $value)
			$attr[$key] = $this->escape($attr[$key]);
		
		// Execute
		return parent::execute($dbQuery->getQuery($attr));
	}
	
	/**
	 * Gets the active database server.
	 * 
	 * @return	string
	 * 		The database server name.
	 */
	private function getDbServer()
	{
		return "platform";
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
			$serverName = $this->getDbServer();
			$dbSettings = new dbSettings($serverName);
			self::$settings = $dbSettings->get();
		}
		
		return self::$settings;
	}
}
//#section_end#
?>