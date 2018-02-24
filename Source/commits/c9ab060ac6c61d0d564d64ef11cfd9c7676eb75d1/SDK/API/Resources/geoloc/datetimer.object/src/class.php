<?php
//#section#[header]
// Namespace
namespace API\Resources\geoloc;

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
 * @namespace	\geoloc
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Geoloc", "datetimer");

use \API\Geoloc\datetimer as newDatetimer;

/**
 * Date time manager
 * 
 * Manages the stored date the time and handles how they will be displayed (in the proper timezone) to the user.
 * 
 * @version	v. 0.1-0
 * @created	September 18, 2013, 12:38 (EEST)
 * @revised	July 9, 2014, 11:39 (EEST)
 * 
 * @deprecated	Use \API\Geoloc\datetimer instead.
 */
class datetimer extends newDatetimer {}
//#section_end#
?>