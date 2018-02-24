<?php
//#section#[header]
// Namespace
namespace DEV\Resources;

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
 * @package	Resources
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

/**
 * Redback developer paths.
 * 
 * It's a developer's path giver.
 * All developers should rely on this class to get the appropriate path for data manipulation.
 * 
 * @version	4.0-1
 * @created	July 3, 2014, 11:22 (EEST)
 * @updated	September 13, 2015, 15:44 (EEST)
 */
class paths
{
	/**
	 * The system developer's directory path.
	 * 
	 * @type	string
	 */
	private static $devPath = "/.developer/";
	
	/**
	 * The system production library path.
	 * 
	 * @type	string
	 */
	private static $libraryPath = "/.library/";
	
	/**
	 * The system's cdn library path.
	 * 
	 * @type	string
	 */
	private static $cdnPath = "/.cdn/";
	
	/**
	 * The account's and company's folder data.
	 * 
	 * @type	string
	 */
	private static $profileData = "/.profile/";
	
	/**
	 * The resource folder name.
	 * 
	 * @type	string
	 */
	private static $rsrcPath = "/Resources/";
	
	/**
	 * Gets the system developer's directory path.
	 * 
	 * @return	string
	 * 		The developer's directory path.
	 */
	public static function getDevPath()
	{
		return self::$devPath."/";
	}
	
	/**
	 * Get the developer's repository path.
	 * 
	 * @return	string
	 * 		The developer's repository path.
	 */
	public static function getRepositoryPath()
	{
		return self::getDevPath()."/Repository/";
	}
	
	/**
	 * Gets the system's cdn library inner path.
	 * 
	 * @return	string
	 * 		The cdn library path.
	 */
	public static function getCdnPath()
	{
		return self::$cdnPath."/";
	}
	
	/**
	 * Get the system's profile data path.
	 * 
	 * @return	string
	 * 		The system's profile data path from the root.
	 */
	public static function getProfilePath()
	{
		return self::$profileData."/";
	}
	
	/**
	 * Gets the system's Modules resources directory path.
	 * 
	 * @return	void
	 */
	public static function getLibraryPath()
	{
		return self::$libraryPath."/";
	}
	
	/**
	 * Get the published library folder path.
	 * 
	 * @return	string
	 * 		The published library path.
	 * 
	 * @deprecated	Use getLibraryPath() instead.
	 */
	public static function getPublishedPath()
	{
		return self::getLibraryPath();
	}
	
	/**
	 * Gets the system's SDK resources directory path.
	 * 
	 * @return	string
	 * 		The SDK's resources path.
	 */
	public static function getSDKRsrcPath()
	{
		return "/System/".self::$rsrcPath."/SDK/";
	}
	
	/**
	 * Gets the system developer's resources directory path.
	 * 
	 * @return	string
	 * 		The developer's resources path.
	 */
	public static function getDevRsrcPath()
	{
		return "/System/".self::$rsrcPath."/SDK/DEV/";
	}
}
//#section_end#
?>