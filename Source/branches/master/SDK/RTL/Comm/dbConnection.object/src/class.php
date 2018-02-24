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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/adbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \SYS\Comm\db\adbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Redback Retail Database Connection handler
 * 
 * Connects to Redback's retail database and executes all the red sql queries.
 * 
 * @version	3.0-1
 * @created	December 10, 2014, 11:05 (EET)
 * @updated	October 9, 2015, 14:45 (EEST)
 */
class dbConnection extends adbConnection
{
	/**
	 * Create a database connector instance.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings("retail");

		// Call parent constructor
		parent::__construct($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
	}
}
//#section_end#
?>