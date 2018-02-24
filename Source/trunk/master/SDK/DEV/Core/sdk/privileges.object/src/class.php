<?php
//#section#[header]
// Namespace
namespace DEV\Core\sdk;

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
 * @package	Core
 * @namespace	\sdk
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("DEV", "Core", "privileges");

use \DEV\Core\privileges as newPrivileges;

/**
 * Open SDK Packages Manager
 * 
 * Handles all SDK packages that are open to developers.
 * 
 * @version	1.0-1
 * @created	October 24, 2014, 15:17 (EEST)
 * @updated	February 17, 2015, 13:05 (EET)
 * 
 * @deprecated	Use \DEV\Core\privileges instead.
 */
class privileges extends newPrivileges {}
//#section_end#
?>