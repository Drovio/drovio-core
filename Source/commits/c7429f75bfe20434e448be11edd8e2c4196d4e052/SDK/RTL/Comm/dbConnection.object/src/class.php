<?php
//#section#[header]
// Namespace
namespace RTL\Comm;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	RTL
 * @package	Comm
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("API", "Comm", "database/dbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \API\Comm\database\dbConnection as APIdbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Redback Retail Database Connection handler
 * 
 * Connects to Redback's retail database and executes all the red sql queries.
 * 
 * @version	0.2-1
 * @created	December 10, 2014, 11:05 (EET)
 * @updated	January 5, 2015, 10:37 (EET)
 */
class dbConnection extends APIdbConnection
{
	/**
	 * The database server settings manager object.
	 * 
	 * @type	settingsManager
	 */
	private static $settings;
	
	/**
	 * Initializes the redback's retail database connector.
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
	 * Executes a red sql query (dbQuery) to the redback retail database.
	 * 
	 * @param	dbQuery	$dbQuery
	 * 		The query to be executed.
	 * 
	 * @param	array	$attr
	 * 		A number of attributes to be passed as arguments to the query.
	 * 		It is an associative array to respond to the sql parameters.
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
	 * Gets the server credentials handler.
	 * 
	 * @return	settingsManager
	 * 		The settingsManager object.
	 */
	private function getServerSettings()
	{
		if (empty(self::$settings))
		{
			$dbSettings = new dbSettings("retail");
			self::$settings = $dbSettings->get();
		}
		
		return self::$settings;
	}
}
//#section_end#
?>