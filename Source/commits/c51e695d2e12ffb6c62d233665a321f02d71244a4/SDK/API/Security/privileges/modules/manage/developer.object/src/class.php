<?php
//#section#[header]
// Namespace
namespace API\Security\privileges\modules\manage;

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
 * @namespace	\privileges\modules\manage
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Profile", "developer");

use \API\Profile\developer as profileDeveloper;

/**
 * User's developer extension
 * 
 * Manages and checks all the developer's privileges.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 13:45 (EEST)
 * @revised	July 31, 2013, 13:45 (EEST)
 * 
 * @deprecated	Use \API\Profile\developer instead.
 */
class developer extends profileDeveloper
{
	/**
	 * Checks if logged in user is a developer of a module
	 * 
	 * @param	integer	$module_id
	 * 		The module's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Profile\developer::moduleInWorkspace() instead.
	 */
	public static function get_moduleStatus($module_id)
	{
		return parent::moduleInWorkspace($module_id);
	}
	
	/**
	 * Checks if a user has privileges on a group
	 * 
	 * @param	integer	$group_id
	 * 		The moduleGroup's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Profile\developer::moduleGroupInWorkspace() instead.
	 */
	public static function get_groupStatus($group_id)
	{
		return parent::moduleGroupInWorkspace($group_id);
	}
	
	/**
	 * Checks if a user has privileges on a group as master (for creating subgroups)
	 * 
	 * @param	integer	$group_id
	 * 		The moduleGroup's id
	 * 
	 * @return	boolean
	 * 		{description}
	 * 
	 * @deprecated	Use \API\Profile\developer::masterGroup() instead.
	 */
	public static function get_masterGroupStatus($group_id)
	{
		return parent::masterGroup($group_id);
	}
}
//#section_end#
?>