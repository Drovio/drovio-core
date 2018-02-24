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
importer::import("DRVC", "Security", "permissions");

use \API\Profile\team;
use \DRVC\Security\permissions as IDpermissions;

/**
 * Account permission manager with groups
 * 
 * Manages permissions for the current team.
 * 
 * @version	0.1-1
 * @created	December 17, 2015, 11:49 (GMT)
 * @updated	December 17, 2015, 11:49 (GMT)
 */
class permissions extends IDpermissions
{
	/**
	 * The platform permissions instance.
	 * 
	 * @type	permissions
	 */
	private static $instance;
	
	/**
	 * Get the permissions instance.
	 * 
	 * @return	permissions
	 * 		The permissions instance
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new permissions();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new permissions instance for the current team identity.
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