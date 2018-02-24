<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Profile", "account");
importer::import("DRVC", "Profile", "managedAccount");

use \API\Profile\account;
use \DRVC\Profile\managedAccount as IDManagedAccount;

/**
 * Managed Account Handler for Drovio
 * 
 * This class is responsible for managed accounts (not admin) for the drovio platform.
 * 
 * @version	0.1-1
 * @created	November 10, 2015, 14:50 (GMT)
 * @updated	November 10, 2015, 14:50 (GMT)
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