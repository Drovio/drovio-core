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

importer::import("API", "Profile", "account");

use \API\Profile\account as pAccount;

/**
 * Account Manager Class
 * 
 * Manages the active account.
 * 
 * @version	3.0-1
 * @created	July 31, 2013, 12:39 (EEST)
 * @revised	December 31, 2014, 10:14 (EET)
 * 
 * @deprecated	Use \API\Profile\account instead.
 */
class account extends pAccount {}
//#section_end#
?>