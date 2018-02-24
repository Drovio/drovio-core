<?php
//#section#[header]
// Namespace
namespace API\Security;

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
 * @package	Security
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Platform", "accessControl");

use \API\Platform\accessControl as newControl;

/**
 * Internal Access Control
 * 
 * This is the proxy class which is responsible for letting the API execute internal functions and prevent others from executing closed functions and features.
 * 
 * @version	1.0-1
 * @created	March 8, 2013, 10:41 (EET)
 * @revised	November 5, 2014, 15:18 (EET)
 * 
 * @deprecated	Use \API\Platform\accessControl instead.
 */
class accessControl extends newControl {}
//#section_end#
?>