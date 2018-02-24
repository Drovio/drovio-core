<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Developer", "devTabber");

use \UI\Developer\devTabber;

/**
 * Web Integrated Development Environment
 * 
 * A tab-oriented object for handling multiple data objects.
 * 
 * @version	0.1-1
 * @created	April 26, 2013, 13:38 (EEST)
 * @revised	September 8, 2014, 15:21 (EEST)
 * 
 * @deprecated	Use \UI\Developer\devTabber instead.
 */
class redWIDE extends devTabber {}
//#section_end#
?>