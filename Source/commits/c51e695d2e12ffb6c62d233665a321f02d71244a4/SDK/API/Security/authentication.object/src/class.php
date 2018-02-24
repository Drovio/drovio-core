<?php
//#section#[header]
// Namespace
namespace API\Security;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Security
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */


/**
 * Authentication manager
 * 
 * Authenticates all user's transactions with the system's modules and authorizes.
 * 
 * @version	{empty}
 * @created	April 23, 2013, 13:57 (EEST)
 * @revised	August 8, 2013, 19:02 (EEST)
 * 
 * @deprecated	This class is deprecated. Use \API\Security\privileges instead.
 */
class authentication
{
	
	/**
	 * Check if a user is authorized to execute a specific policy (module id)
	 * It returns according to access:
	 * -onauth: user can execute but needs password (protected)
	 * -auth: user cannot execute but can request access (public)
	 * -uc: page is under construction
	 * -off: page is offline
	 * -user: user can execute freely
	 * -user_open: user can execute freely, after authorization took place
	 * -no: user cannot execute (protected or private)
	 * 
	 * @param	integer	$policyID
	 * 		The module's id
	 * 
	 * @param	integer	$reqAccountID
	 * 		The account's id which requests the access
	 * 
	 * @return	string
	 * 		{description}
	 */
	public static function get_access($policyID, $reqAccountID = NULL)
	{
		return "user";
	}
	
	/**
	 * Load inner policies access status
	 * 
	 * @param	array	$inner
	 * 		An array of module ids
	 * 
	 * @return	mixed
	 * 		{description}
	 */
	public static function get_innerAccess($inner = array())
	{
		return TRUE;
	}
	
	/**
	 * Checks if the user can procced with the execution of the code given the access status.
	 * 
	 * @param	string	$access
	 * 		The access status code.
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function can_proceed($access)
	{
		switch ($access)
		{
			//_____ Open command
			case "open":
			//_____ User can execute
			case "user":
			//_____ User can execute after authentication
			case "user_open":
				return TRUE;
			//_____ User can execute but system needs user's authorization (protected policy)
			case "onauth":
			//_____ User cannot execute and system needs authorization (public policy)
			case "auth":
			//_____ User cannot execute and system denies access (protected|private policy or not exist)
			case "no":
			//_____ outer Page Policy : Under Construction
			case "uc":
			//_____ outer Page Policy : Policy removed
			case "off":
				return FALSE;
		}
		
		return FALSE;
	}
}
//#section_end#
?>