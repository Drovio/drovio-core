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

importer::import("DRVC", "Profile", "accountSession");

use \DRVC\Profile\accountSession as IDAccountSession;

/**
 * Account session manager for drovio users.
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	October 29, 2015, 23:45 (GMT)
 * @updated	October 29, 2015, 23:45 (GMT)
 */
class accountSession extends IDAccountSession
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
	/**
	 * The platform accountSession instance.
	 * 
	 * @type	accountSession
	 */
	private static $instance;
	
	/**
	 * Get the accountSession instance.
	 * 
	 * @return	accountSession
	 * 		The accountSession instance.
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
	 * Create a new accountSession instance for the platform identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(self::ID_TEAM_NAME);
	}
}
//#section_end#
?>