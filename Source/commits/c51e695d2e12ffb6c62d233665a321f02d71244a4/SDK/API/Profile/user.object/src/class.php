<?php
//#section#[header]
// Namespace
namespace API\Profile;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Profile
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * User
 * 
 * Handles all user validation with the system
 * 
 * @version	{empty}
 * @created	March 16, 2013, 11:30 (EET)
 * @revised	July 31, 2013, 13:20 (EEST)
 * 
 * @deprecated	Use \API\Security\account instead.
 */

class user {}
//#section_end#
?>