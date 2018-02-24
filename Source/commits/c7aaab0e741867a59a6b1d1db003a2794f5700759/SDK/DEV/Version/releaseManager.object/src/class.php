<?php
//#section#[header]
// Namespace
namespace DEV\Version;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	DEV
 * @package	Version
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Version", "tools::releaseManager");

use \DEV\Version\tools\releaseManager as newReleaseManager;

/**
 * Version Control Release Manager
 * 
 * This is a dialog for creating releases in project repositories.
 * You can choose branch and the version to release to.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:19 (EET)
 * @revised	June 26, 2014, 10:19 (EEST)
 * 
 * @deprecated	Use \DEV\Version\tools\releaseManager instead.
 */
class releaseManager extends newReleaseManager {}
//#section_end#
?>