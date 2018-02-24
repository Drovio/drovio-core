<?php
//#section#[header]
// Namespace
namespace ESS\Prototype;

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
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Prototype", "ModuleActionFactory");

use \ESS\Prototype\ModuleActionFactory;

/**
 * Redback's Action Factory
 * 
 * This is a class for attaching all kind of actions and listeners to elements to be handled in the client side and communicate with the server.
 * 
 * @version	2.0-1
 * @created	March 12, 2013, 14:23 (EET)
 * @revised	November 21, 2014, 11:05 (EET)
 * 
 * @deprecated	Use \ESS\Prototype\ModuleActionFactory instead.
 */
class ActionFactory extends ModuleActionFactory {}
//#section_end#
?>