<?php
//#section#[header]
// Namespace
namespace DRVC\Comm;

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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("API", "Comm", "database/dbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \API\Comm\database\dbConnection as APIdbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Drovio Identity Database Connection handler
 * 
 * {description}
 * 
 * @version	2.0-1
 * @created	October 7, 2015, 19:03 (EEST)
 * @updated	October 8, 2015, 1:55 (EEST)
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
		$settings = $this->getServerSettings();
		
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
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$dbQuery
	 * 		{description}
	 * 
	 * @param	{type}	$attr
	 * 		{description}
	 * 
	 * @param	{type}	$commit
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function execute($dbQuery, $attr = array(), $commit = TRUE)
	{
		// Execute
		return parent::execute($dbQuery->getQuery(), $attr, $commit);
	}
	
	/**
	 * Load server settings from settings.
	 * 
	 * @return	array
	 * 		All database credential settings.
	 */
	private function getServerSettings()
	{
		// Check cache and load settings
		if (empty(self::$settings))
		{
			$dbSettings = new dbSettings("identity");
			self::$settings = $dbSettings->get();
		}
		
		// Return settings object
		return self::$settings;
	}
}
//#section_end#
?>