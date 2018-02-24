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
 * Person Account Manager
 * 
 * Manages all the person's account settings.
 * 
 * @version	{empty}
 * @created	April 19, 2013, 12:13 (EEST)
 * @revised	July 31, 2013, 13:24 (EEST)
 * 
 * @deprecated	Use \API\Profile\AccountManager instead.
 */
class personAccount {}
//#section_end#
?>