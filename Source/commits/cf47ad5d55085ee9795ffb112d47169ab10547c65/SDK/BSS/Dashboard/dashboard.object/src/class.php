<?php
//#section#[header]
// Namespace
namespace BSS\Dashboard;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	BSS
 * @package	Dashboard
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("BSS", "Dashboard", "appDecorator");

use \BSS\Dashboard\appDecorator;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	April 3, 2015, 11:35 (EEST)
 * @updated	April 3, 2015, 11:35 (EEST)
 * 
 * @deprecated	Use \BSS\Dashboard\appDecorator instead.
 */
class dashboard extends appDecorator {}
//#section_end#
?>