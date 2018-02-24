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

importer::import("API", "Profile", "tester");

use \API\Profile\tester as profileTester;

/**
 * User's tester extension
 * 
 * Manages all the tester's privileges.
 * 
 * @version	{empty}
 * @created	July 31, 2013, 13:53 (EEST)
 * @revised	July 31, 2013, 13:53 (EEST)
 * 
 * @deprecated	Use \API\Profile\tester instead.
 */
class tester extends profileTester
{	
	/**
	 * Checks if a user is a tester of a module
	 * 
	 * @param	integer	$module_id
	 * 		The module's id
	 * 
	 * @return	boolean
	 * 		{description}
	 */
	public static function get_moduleStatus($module_id)
	{
		return parent::testerModule($module_id);
	}
}
//#section_end#
?>