<?php
//#section#[header]
// Namespace
namespace API\Resources\storage;

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
 * @package	Resources
 * @namespace	\storage
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "session");

use \ESS\Environment\session as ENVSession;

/**
 * Session Manager
 * 
 * Handles all session storage processes.
 * 
 * @version	0.1-1
 * @created	April 23, 2013, 13:58 (EEST)
 * @revised	November 4, 2014, 10:14 (EET)
 * 
 * @deprecated	Use \ESS\Environment\session instead.
 */
class session extends ENVSession {}
//#section_end#
?>