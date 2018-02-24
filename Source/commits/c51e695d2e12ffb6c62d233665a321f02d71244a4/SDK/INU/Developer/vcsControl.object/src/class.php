<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("INU", "Developer", "vcs::commitManager");

use \INU\Developer\vcs\commitManager;

/**
 * UI Version Control Manager
 * 
 * Manages the commit items of a vcs repository.
 * 
 * @version	{empty}
 * @created	October 15, 2013, 13:54 (EEST)
 * @revised	November 21, 2013, 20:09 (EET)
 * 
 * @deprecated	Use \INU\Developer\vcs\commitManager instead.
 */
class vcsControl extends commitManager {}
//#section_end#
?>