<?php
//#section#[header]
// Namespace
namespace ESS\Protocol;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Asynchronous Communication Protocol
 * 
 * Responsible class (in Javascript) for handling all communication protocols with any server.
 * 
 * @version	0.1-5
 * @created	July 29, 2014, 18:56 (BST)
 * @updated	December 5, 2015, 13:47 (GMT)
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
	 * 		It must include the REQUEST_PATH and the REQUEST_SUB variables.
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
	 * Returns whether the ascop variable exists (it is an async request of context).
	 * 
	 * @return	boolean
	 * 		True if exists, false otherwise.
	 */
	public static function exists()
	{
		return isset($GLOBALS['_ASCOP']);
	}
	
	/**
	 * Get the request subdomain origin.
	 * If empty, it's the www.
	 * 
	 * @return	string
	 * 		The subdomain where the request is made from.
	 */
	public static function getSubdomain()
	{
		return self::$requestSub;
	}
	
	/**
	 * Get the request url origin.
	 * 
	 * @return	string
	 * 		The page url making the request.
	 */
	public static function getPath()
	{
		return self::$requestPath;
	}
}
//#section_end#
?>