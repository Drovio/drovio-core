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
importer::import("DRVC", "Profile", "person");

use \API\Profile\team;
use \DRVC\Profile\person as IDPerson;

/**
 * Identity Person Manager
 * 
 * Manages persons for the current team.
 * Most of the functionality is referred to the current logged in person.
 * 
 * @version	0.1-1
 * @created	October 23, 2015, 8:44 (BST)
 * @updated	October 23, 2015, 8:44 (BST)
 */
class person extends IDPerson
{
	/**
	 * The team person instance.
	 * 
	 * @type	person
	 */
	private static $instance;
	
	/**
	 * Get the person instance.
	 * 
	 * @return	person
	 * 		The person instance.
	 */
	public static function getInstance()
	{
		// Check for an existing instance
		if (!isset(self::$instance))
			self::$instance = new person();
		
		// Return instance
		return self::$instance;
	}
	
	/**
	 * Create a new person instance for the current team identity.
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