<?php
//#section#[header]
// Namespace
namespace API\Developer\ebuilder;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\ebuilder
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "profiler::tester");
importer::import("API", "Profile", "user");
importer::import("API", "Profile", "person::personServices");

use \API\Developer\profiler\tester;
use \API\Profile\user;
use \API\Profile\person\personServices;

/**
 * Website Manager
 * 
 * Manager for the user's websites folders.
 * 
 * @version	{empty}
 * @created	July 2, 2013, 12:07 (EEST)
 * @revised	July 2, 2013, 12:07 (EEST)
 */
class wsManager
{
	/**
	 * The developer's websites root folder.
	 * 
	 * @type	string
	 */
	private $devRootFolder;
	
	/**
	 * Indicates whether the user is a developer and can use this class.
	 * 
	 * @type	boolean
	 */
	private $valid = FALSE;
	
	/**
	 * Constructor Method. Initializes the Developer's website service.
	 * 
	 * @return	void
	 */
	public function __construct()
	{
		// Initialize Service
		$pServices = new personServices("Developer");
		//$this->devRootFolder = $pServices->getServiceFolder()."/websites/";
		
		// Tester trunk (for testing reasons)
		$this->devRootFolder = tester::getTrunk()."/websites/";
		
		$this->init();
	}
	
	/**
	 * Performs the user's check for validation.
	 * 
	 * @return	void
	 */
	private function init()
	{
		// Check if user is developer and in the program	
		
		// Check if user has any active websites
	}
	
	/**
	 * Gets the website's folder inside the user's folder.
	 * 
	 * @param	string	$websiteId
	 * 		The website id.
	 * 
	 * @return	string
	 * 		Path to the requested website.
	 */
	public function getDevFolder($websiteId)
	{
		return $this->devRootFolder."w".$websiteId.".website/";
	}
}
//#section_end#
?>