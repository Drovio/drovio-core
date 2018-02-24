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

importer::import("API", "Profile", "accountSettings");

use \API\Profile\accountSettings;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	{empty}
 * @created	July 5, 2013, 12:38 (EEST)
 * @revised	October 17, 2013, 15:39 (EEST)
 * 
 * @deprecated	Use accountSettings instead.
 */
class account extends accountSettings {}
//#section_end#
?>