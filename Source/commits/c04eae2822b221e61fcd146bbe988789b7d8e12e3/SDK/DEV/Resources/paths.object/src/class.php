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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Redback developer paths.
 * 
 * It's a developer's path giver.
 * All developers should rely on this class to get the appropriate path for data manipulation.
 * 
 * @version	0.1-2
 * @created	July 3, 2014, 11:22 (EEST)
 * @revised	July 28, 2014, 10:17 (EEST)
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
	 * The system developer's resources directory path.
	 * 
	 * @type	string
	 */
	private static $publishPath = "/.library/";
	
	/**
	 * The system's dynamic folder data.
	 * 
	 * @type	string
	 */
	private static $systemData = "/.system/";
	
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
		return self::$devPath;
	}
	
	/**
	 * Get the published library folder path.
	 * 
	 * @return	string
	 * 		The published library path.
	 */
	public static function getPublishedPath()
	{
		return self::$publishPath;
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
	
	/**
	 * Gets the system's dynamic data folder path.
	 * 
	 * @return	string
	 * 		The dynamic data folder path.
	 */
	public static function getSysDynDataPath()
	{
		return self::$systemData;
	}
	
	/**
	 * Gets the system's dynamic resources folder path.
	 * 
	 * @return	string
	 * 		The dynamic resources folder path.
	 */
	public static function getSysDynRsrcPath()
	{
		return self::$systemData.self::$rsrcPath;
	}
	
	/**
	 * Get the developer's repository path.
	 * 
	 * @return	string
	 * 		The developer's repository path.
	 */
	public static function getRepositoryPath()
	{
		return self::$devPath."Repository/";
	}
}
//#section_end#
?>