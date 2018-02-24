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
importer::import("API", "Login", "team");
importer::import("DRVC", "Profile", "managedAccount");

use \API\Login\account;
use \API\Login\team as LoginTeam;
use \DRVC\Profile\managedAccount as IDManagedAccount;

/**
 * Managed Account Handler for Drovio
 * 
 * This class is responsible for managed accounts (not admin) for the drovio platform.
 * 
 * @version	0.1-2
 * @created	November 11, 2015, 18:57 (GMT)
 * @updated	December 17, 2015, 13:32 (GMT)
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
		// Get current identity team
		$identityTeam = LoginTeam::getIdentityTeamName();
		$identityTeam = (empty($identityTeam) ? LoginTeam::ID_TEAM_NAME : $identityTeam);
		
		// Create account instance
		parent::__construct($identityTeam);
		$this->account = account::getInstance();
	}
}
//#section_end#
?>