<?php
//#section#[header]
// Namespace
namespace ESS\Protocol;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	ESS
 * @package	Protocol
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("ESS", "Protocol", "BootLoader");

use \ESS\Protocol\BootLoader;

/**
 * Project Resource Boot Loader
 * 
 * This is the manager class for loading and general handling project's resources (javascript and css).
 * 
 * @version	1.0-1
 * @created	January 6, 2015, 18:33 (EET)
 * @updated	January 20, 2015, 12:02 (EET)
 * 
 * @deprecated	Use \ESS\Protocol\BootLoader instead.
 */
class BootLoader2 extends BootLoader {}
//#section_end#
?>