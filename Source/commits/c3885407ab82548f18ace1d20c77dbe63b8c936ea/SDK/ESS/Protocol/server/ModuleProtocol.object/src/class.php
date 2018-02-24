<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\server;

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
 * @namespace	\server
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "ModuleProtocol");

use \ESS\Protocol\ModuleProtocol as newModuleProtocol;

/**
 * Client-Server Module Handler Protocol
 * 
 * The most valuable and basic protocol for loading dynamic content.
 * It's based on AJAX content loading and defines the controllers for those interactions.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 9:20 (EET)
 * @revised	July 29, 2014, 21:59 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\ModuleProtocol instead.
 */
class ModuleProtocol extends newModuleProtocol {}
//#section_end#
?>