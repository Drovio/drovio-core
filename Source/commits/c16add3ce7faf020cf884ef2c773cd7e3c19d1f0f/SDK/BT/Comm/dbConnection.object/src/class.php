<?php
//#section#[header]
// Namespace
namespace BT\Comm;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BT
 * @package	Comm
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("SYS", "Comm", "db/adbConnection");
importer::import("SYS", "Resources", "settings/dbSettings");

use \SYS\Comm\db\adbConnection;
use \SYS\Resources\settings\dbSettings;

/**
 * Redback Issues Database Connection handler
 * 
 * Connects to Redback's issue tracker database and executes all the red sql queries.
 * 
 * @version	1.0-1
 * @created	January 5, 2015, 10:39 (EET)
 * @updated	October 9, 2015, 14:38 (EEST)
 */
class dbConnection extends adbConnection
{
	/**
	 * Initializes the redback's issues database connector.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Load Redback Database Credentials
		$settings = $this->getServerSettings("issues");

		// Call parent constructor
		parent::__construct($settings['SERVER_DBMS'], $settings['SERVER_URL'], $settings['DB_NAME'], $settings['USERNAME'], $settings['PASSWORD']);
	}
}
//#section_end#
?>