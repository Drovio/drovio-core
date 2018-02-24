<?php
//#section#[header]
// Namespace
namespace AEL\Identity;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Identity
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Profile", "team");
importer::import("DRVC", "Profile", "account");

use \API\Profile\team;
use \DRVC\Profile\account as IDAccount;

/**
 * Identity Account Manager
 * 
 * Manages accounts for the current team.
 * Most of the functionality is referred to the current logged in account.
 * 
 * @version	0.1-2
 * @created	October 23, 2015, 8:44 (BST)
 * @updated	October 23, 2015, 14:38 (BST)
 */
class account
{
	/**
	 * The team account instance.
	 * 
	 * @type	account
	 */
	private static $instance;
	
	/**
	 * Get the account instance.
	 * 
	 * @return	account
	 * 		The account instance
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new account();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new account instance for the current team identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		// Get current team
		$teamName = team::getTeamUName();
		$teamName = strtolower($teamName);
		
		// Create instance
		parent::__construct($teamName);
	}
}
//#section_end#
?>