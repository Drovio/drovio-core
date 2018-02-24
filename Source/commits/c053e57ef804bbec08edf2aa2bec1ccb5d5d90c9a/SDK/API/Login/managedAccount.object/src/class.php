<?php
//#section#[header]
// Namespace
namespace API\Login;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Login
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Login", "account");
importer::import("DRVC", "Profile", "managedAccount");

use \API\Login\account;
use \DRVC\Profile\managedAccount as IDManagedAccount;

/**
 * Managed Account Handler for Drovio
 * 
 * This class is responsible for managed accounts (not admin) for the drovio platform.
 * 
 * @version	0.1-1
 * @created	November 11, 2015, 18:57 (GMT)
 * @updated	November 11, 2015, 18:57 (GMT)
 */
class managedAccount extends IDManagedAccount
{
	/**
	 * The platform managed account instance.
	 * 
	 * @type	managedAccount
	 */
	private static $instance;
	
	/**
	 * Get a managed account instance.
	 * 
	 * @return	managedAccount
	 * 		The managedAccount instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new managedAccount();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new managed account instance for the platform identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(account::ID_TEAM_NAME);
		$this->account = account::getInstance();
	}
}
//#section_end#
?>