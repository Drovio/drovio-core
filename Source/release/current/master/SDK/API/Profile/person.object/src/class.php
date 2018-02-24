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
importer::import("DRVC", "Profile", "person");

use \API\Profile\account;
use \DRVC\Profile\person as IDPerson;

/**
 * Person Class
 * 
 * Manages persons for the drovio identity database.
 * 
 * @version	2.0-2
 * @created	December 31, 2013, 10:34 (GMT)
 * @updated	November 10, 2015, 16:35 (GMT)
 */
class person extends IDPerson
{
	/**
	 * The system team name for the identity database.
	 * 
	 * @type	string
	 */
	const ID_TEAM_NAME = "drovio";
	
	/**
	 * The platform person instance.
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
	 * Create a new person instance for the platform identity.
	 * 
	 * @return	void
	 */
	protected function __construct()
	{
		parent::__construct(self::ID_TEAM_NAME);
		$this->account = account::getInstance();
	}
	
	/**
	 * Remove the current person.
	 * It will probably return FALSE because there must be no accounts connected.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove()
	{
		// Check secure mode
		if (importer::secure())
			return FALSE;
		
		// Get current account id
		$personID = account::getInstance()->getPersonID();
		return parent::remove($personID);
	}
}
//#section_end#
?>