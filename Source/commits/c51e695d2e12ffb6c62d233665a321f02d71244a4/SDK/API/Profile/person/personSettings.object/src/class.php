<?php
//#section#[header]
// Namespace
namespace API\Profile\person;

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
 * @namespace	\person
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

/**
 * Person Settings Manager
 * 
 * Manages all the person's settings.
 * 
 * @version	{empty}
 * @created	April 19, 2013, 12:28 (EEST)
 * @revised	July 31, 2013, 13:27 (EEST)
 * 
 * @deprecated	Use \API\Profile\SettingsManager instead.
 */
class personSettings {}
//#section_end#
?>