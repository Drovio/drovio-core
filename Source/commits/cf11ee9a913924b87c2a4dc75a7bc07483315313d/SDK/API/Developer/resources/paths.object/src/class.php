<?php
//#section#[header]
// Namespace
namespace API\Developer\resources;

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
 * @package	Developer
 * @namespace	\resources
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Redback developer paths.
 * 
 * Keeps all the developer paths.
 * 
 * @version	{empty}
 * @created	October 7, 2013, 12:32 (EEST)
 * @revised	December 24, 2013, 10:17 (EET)
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
	private static $systemData = "/.systemData/";
	
	/**
	 * The account's and company's folder data.
	 * 
	 * @type	string
	 */
	private static $profileData = "/.profileData/";
	
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
	 * 		The directory path.
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
	 * 		The directory path.
	 */
	public static function getDevRsrcPath()
	{
		return self::$devPath.self::$rsrcPath;
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