<?php
//#section#[header]
// Namespace
namespace DEV\Temp;

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
 * @package	Temp
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("DEV", "Version", "vcs");
use \DEV\Version\vcs;

/**
 * VCS Manager
 * 
 * Created for migration reasons.
 * 
 * @version	{empty}
 * @created	May 10, 2014, 15:02 (EEST)
 * @revised	May 10, 2014, 15:02 (EEST)
 * 
 * @deprecated	Use \DEV\Version\vcs instead.
 */
class vcs2 extends vcs {}
//#section_end#
?>