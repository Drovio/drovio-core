<?php
//#section#[header]
// Namespace
namespace ESS\Prototype\html;

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
 * @package	Prototype
 * @namespace	\html
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "content::ModuleContainerPrototype");

use \ESS\Prototype\content\ModuleContainerPrototype as newPrototype;

/**
 * Module Container
 * 
 * Builds a module container element.
 * This will be filled asynchronously on content.modified with the module assigned.
 * 
 * @version	1.0-1
 * @created	March 7, 2013, 12:06 (EET)
 * @revised	September 8, 2014, 10:35 (EEST)
 * 
 * @deprecated	Use \ESS\Prototype\content\ModuleContainerPrototype instead.
 */
class ModuleContainerPrototype extends newPrototype {}
//#section_end#
?>