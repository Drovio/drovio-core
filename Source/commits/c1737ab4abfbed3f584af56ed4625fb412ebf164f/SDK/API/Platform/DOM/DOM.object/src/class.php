<?php
//#section#[header]
// Namespace
namespace API\Platform\DOM;

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
 * @package	Platform
 * @namespace	\DOM
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("UI", "Html", "DOM");

use \UI\Html\DOM as uiDOM;

/**
 * {title}
 * 
 * {description}
 * 
 * @version	{empty}
 * @created	March 24, 2014, 14:00 (EET)
 * @revised	March 24, 2014, 14:00 (EET)
 * 
 * @deprecated	Use \UI\Html\DOM Instead.
 */
class DOM extends uiDOM {}
//#section_end#
?>