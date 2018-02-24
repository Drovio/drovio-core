<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Asynchronous Communication Protocol
 * 
 * Responsible class (in Javascript) for handling all communication protocols with the server.
 * 
 * @version	{empty}
 * @created	March 7, 2013, 9:18
 * @revised	April 5, 2013, 10:09 (EEST)
 */
class AsCoProtocol
{
	/**
	 * The ajax request url origin.
	 * 
	 * @type	string
	 */
	private static $requestPath;
	/**
	 * The ajax request subdomain origin.
	 * 
	 * @type	string
	 */
	private static $requestSub;
	
	/**
	 * Sets the ascop variables.
	 * 
	 * @param	array	$ASCOP
	 * 		The ascop array attributes.
	 * 
	 * @return	void
	 */
	public static function set($ASCOP)
	{
		self::$requestPath = $ASCOP['REQUEST_PATH'];
		self::$requestSub = $ASCOP['REQUEST_SUB'];
		
		// Temporary
		$GLOBALS['_ASCOP'] = $ASCOP;
	}
	
	/**
	 * Get the request subdomain origin.
	 * If empty, it's the www.
	 * 
	 * @return	string
	 */
	public static function getSubdomain()
	{
		return self::$requestSub;
	}
	
	/**
	 * Get the request url origin.
	 * 
	 * @return	string
	 */
	public static function getPath()
	{
		return self::$requestPath;
	}
}
//#section_end#
?>