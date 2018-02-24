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
importer::import("DRVC", "Profile", "accountSession");

use \API\Profile\team;
use \DRVC\Profile\accountSession as IDAccountSession;

/**
 * Identity Account Session Manager
 * 
 * Manages account session for the current team.
 * Fetch all active sessions for a given account
 * 
 * @version	0.1-2
 * @created	October 23, 2015, 15:37 (BST)
 * @updated	November 10, 2015, 19:01 (GMT)
 */
class accountSession extends IDAccountSession
{
	/**
	 * The platform accountSession instance.
	 * 
	 * @type	accountSession
	 */
	private static $instance;
	
	/**
	 * Get the account session instance.
	 * 
	 * @return	accountSession
	 * 		The account session instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new accountSession();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new account session instance for the current team identity.
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