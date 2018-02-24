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
 * @version	0.1-3
 * @created	October 23, 2015, 8:44 (BST)
 * @updated	October 23, 2015, 15:17 (BST)
 */
class account extends IDAccount
{
	/**
	 * Get the account instance.
	 * 
	 * @return	account
	 * 		The account instance
	 */
	public static function getInstance()
	{
		// Get current team
		$teamName = team::getTeamUName();
		$teamName = strtolower($teamName);
		
		// Return parent instance
		return parent::getInstance($teamName);
	}
	
	/**
	 * Create a new account instance for the current team identity.
	 * 
	 * @return	void
	 */
	final protected function __construct() {}
}
//#section_end#
?>