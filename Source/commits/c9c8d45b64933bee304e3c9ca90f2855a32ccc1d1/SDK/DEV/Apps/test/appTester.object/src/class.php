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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Profiler", "tester");
use \DEV\Profiler\tester;

/**
 * Application Tester
 * 
 * Manages the application tester environment.
 * 
 * @version	3.0-2
 * @created	April 7, 2014, 11:18 (EEST)
 * @updated	May 13, 2015, 17:52 (EEST)
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
	 * The domain from which the application is loaded.
	 * 
	 * @type	string
	 */
	private static $domain = "";
	
	/**
	 * Sets the subdomain running the application.
	 * 
	 * @param	boolean	$status
	 * 		The lock status for the application loading.
	 * 		Set true to lock application loading from publisher's library and false for development use.
	 * 
	 * @param	string	$domain
	 * 		The domain from which the application is being loaded.
	 * 		It makes difference when trying to get the running version of the application.
	 * 
	 * @return	void
	 */
	public static function setPublisherLock($status = TRUE, $domain = "")
	{
		self::$publisherLock = $status;
		self::$domain = $domain;
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
	 * Get the current domain where the application is running.
	 * 
	 * @return	string
	 * 		The current subdomain.
	 */
	public static function currentDomain()
	{
		return self::$domain;
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
			
		return parent::activate("appTester_app".$appID);
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
			
		return parent::deactivate("appTester_app".$appID);
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
			
		return parent::status("appTester_app".$appID);
	}
}
//#section_end#
?>