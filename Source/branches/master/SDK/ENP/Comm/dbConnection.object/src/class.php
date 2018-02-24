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

importer::import("SYS", "Comm", "db/adbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \SYS\Comm\db\adbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Drovio Enterprise Database Connection Handler
 * 
 * Connects to Drovio enterprise database and executes all the core sql queries.
 * 
 * @version	1.0-1
 * @created	July 24, 2015, 11:24 (EEST)
 * @updated	October 9, 2015, 14:46 (EEST)
 */
class dbConnection extends adbConnection
{
	/**
	 * Create a database instance.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings("enterprise");

		// Call parent constructor
		parent::__construct($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
	}
}
//#section_end#
?>