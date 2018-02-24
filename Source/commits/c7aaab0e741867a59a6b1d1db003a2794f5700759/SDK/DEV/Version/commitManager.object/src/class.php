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

importer::import("DEV", "Version", "tools::commitManager");

use \DEV\Version\tools\commitManager as newCommitManager;

/**
 * Version Control Commit Manager
 * 
 * This is a dialog for committing items in project repositories.
 * Displays all the working items of the current author, including the force-to-commit items in a separate list.
 * 
 * @version	{empty}
 * @created	February 12, 2014, 12:14 (EET)
 * @revised	June 26, 2014, 10:12 (EEST)
 * 
 * @deprecated	Use \DEV\Version\tools\commitManager instead.
 */
class commitManager extends newCommitManager {}
//#section_end#
?>