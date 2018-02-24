<?php
//#section#[header]
// Namespace
namespace API\Profile;

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
 * @package	Profile
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("API", "Login", "managedAccount");

use \API\Login\managedAccount as LoginManagedAccount;

/**
 * Managed Account Handler for Drovio
 * 
 * This class is responsible for managed accounts (not admin) for the drovio platform.
 * 
 * @version	1.0-1
 * @created	November 10, 2015, 14:50 (GMT)
 * @updated	November 11, 2015, 18:57 (GMT)
 * 
 * @deprecated	Use \API\Login\managedAccount instead.
 */
class managedAccount extends LoginManagedAccount {}
//#section_end#
?>