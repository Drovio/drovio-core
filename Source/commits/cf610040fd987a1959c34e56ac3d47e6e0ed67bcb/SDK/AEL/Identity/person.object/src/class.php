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
 * @version	0.1-2
 * @created	October 23, 2015, 8:44 (BST)
 * @updated	October 23, 2015, 15:19 (BST)
 */
class person extends IDPerson
{
	/**
	 * Get the person instance.
	 * 
	 * @return	person
	 * 		The person instance.
	 */
	public static function getInstance()
	{
		// Get current team
		$teamName = team::getTeamUName();
		$teamName = strtolower($teamName);
		
		// Return instance
		return parent::getInstance($teamName);
	}
	
	/**
	 * Create a new person instance for the current team identity.
	 * 
	 * @return	void
	 */
	final protected function __construct() {}
}
//#section_end#
?>