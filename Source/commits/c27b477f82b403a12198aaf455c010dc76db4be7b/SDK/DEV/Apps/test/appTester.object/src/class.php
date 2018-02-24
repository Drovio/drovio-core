<?php
//#section#[header]
// Namespace
namespace DEV\Apps\test;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Apps
 * @namespace	\test
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");
use \DEV\Profiler\tester;

/**
 * Application Tester
 * 
 * Manages the application tester environment.
 * 
 * @version	2.0-1
 * @created	April 7, 2014, 11:18 (EEST)
 * @revised	August 28, 2014, 17:03 (EEST)
 */
class appTester extends tester
{
	/**
	 * The publisher lock for the running application.
	 * 
	 * @type	boolean
	 */
	private static $publisherLock = FALSE;
	
	/**
	 * Sets the subdomain running the application.
	 * 
	 * @param	boolean	$status
	 * 		The lock status for the application loading.
	 * 		Set true to lock application loading from publisher's library and false for development use.
	 * 
	 * @return	void
	 */
	public static function setPublisherLock($status = TRUE)
	{
		self::$publisherLock = $status;
	}
	
	/**
	 * Checks whether the subdomain running the application is publisher locked (some market or app center or dashboard).
	 * On developer's subdomain it is only application testing.
	 * 
	 * @return	boolean
	 * 		Whether the application is being loaded from the published version.
	 */
	public static function publisherLock()
	{
		return (self::$publisherLock === TRUE);
	}
	
	/**
	 * Activates the application tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function activate($appID)
	{
		if (empty($appID))
			return FALSE;
			
		return parent::activate("appTester_id_".$appID);
	}
	
	/**
	 * Deactivates the application tester mode.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function deactivate($appID)
	{
		if (empty($appID))
			return FALSE;
			
		return parent::deactivate("appTester_id_".$appID);
	}
	
	/**
	 * Gets the application tester status.
	 * 
	 * @param	integer	$appID
	 * 		The application id.
	 * 
	 * @return	boolean
	 * 		True if application is in tester mode, false otherwise.
	 */
	public static function status($appID)
	{
		if (empty($appID))
			return FALSE;
			
		return parent::status("appTester_id_".$appID);
	}
}
//#section_end#
?>