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

importer::import("API", "Login", "accountSession");

use \API\Login\accountSession as LoginAccountSession;

/**
 * Account session manager for drovio users.
 * 
 * It extends the drovio identity account session to manage sessions for the drovio platform.
 * 
 * @version	3.0-1
 * @created	October 29, 2015, 23:45 (GMT)
 * @updated	November 11, 2015, 18:44 (GMT)
 * 
 * @deprecated	Use \API\Login\accountSession instead.
 */
class accountSession extends LoginAccountSession {}
//#section_end#
?>