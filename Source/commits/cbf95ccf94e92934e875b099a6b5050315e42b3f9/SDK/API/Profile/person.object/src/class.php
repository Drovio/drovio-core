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
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("DRVC", "Profile", "person");

use \DRVC\Profile\person as IDPerson;

/**
 * Person Class
 * 
 * Manages all person data.
 * 
 * @version	1.0-1
 * @created	December 31, 2013, 12:34 (EET)
 * @updated	October 8, 2015, 13:08 (EEST)
 * 
 * @deprecated	Use DRVC\Profile\person instead.
 */
class person extends IDPerson {}
//#section_end#
?>