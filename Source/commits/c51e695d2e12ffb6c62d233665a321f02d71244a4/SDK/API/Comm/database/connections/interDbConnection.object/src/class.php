<?php
//#section#[header]
// Namespace
namespace API\Comm\database\connections;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Comm
 * @namespace	\database\connections
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");
importer::import("API", "Resources", "settingsManager");
importer::import("API", "Resources", "settings::configSettings");
importer::import("API", "Comm", "database::connections::dbConnection");
importer::import("API", "Developer", "profiler::logger");

use \ESS\Protocol\client\environment\Url;
use \API\Resources\settingsManager;
use \API\Resources\settings\configSettings;
use \API\Comm\database\connections\dbConnection;
use \API\Developer\profiler\logger;

/**
 * Internal Database Connection
 * 
 * Connects to system's database and executes the queries
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:32 (EEST)
 * @revised	October 15, 2013, 18:42 (EEST)
 */
class interDbConnection extends dbConnection
{
	/**
	 * The database server settings.
	 * 
	 * @type	array
	 */
	private static $settings;
	
	/**
	 * Constructor Method.
	 * Initializes the connection.
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
	 * Executes a dbQuery to the redback database.
	 * 
	 * @param	dbQuery	$dbQuery
	 * 		The query to be executed.
	 * 
	 * @param	array	$attr
	 * 		A number of attributes to be passed as arguments to the query.
	 * 		It is an associative array.
	 * 
	 * @return	mixed
	 * 		Returns FALSE on failure.
	 * 		For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query() will return a mysqli_result object.
	 * 		For other successful queries mysqli_query() will return TRUE.
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
	 * Gets the active database server according to url.
	 * 
	 * @return	string
	 * 		Returns the database server.
	 */
	private function getDbServer()
	{
		$subdomain = Url::getSubDomain();
		if (empty($subdomain))
			return "local.testing";
		else
			return "db10.grserver.gr";
	}
	
	/**
	 * Gets the server settings from the credentials.
	 * 
	 * @return	array
	 * 		An array of settings according to xml schema.
	 */
	private function getServerSettings()
	{
		if (empty(self::$settings))
		{
			$server = $this->getDbServer();
			$settingsMan = new settingsManager("/System/Configuration/Settings/Databases/", $server, $rootRelative = TRUE);
			self::$settings = $settingsMan->get();
		}
		
		return self::$settings;
	}
}
//#section_end#
?>