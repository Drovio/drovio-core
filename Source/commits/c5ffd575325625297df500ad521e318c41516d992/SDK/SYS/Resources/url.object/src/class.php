<?php
//#section#[header]
// Namespace
namespace SYS\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	SYS
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");

use \ESS\Protocol\client\environment\Url as ESSUrl;

/**
 * Redback URL Manager
 * 
 * Manages all url resolving for navigation and resources.
 * 
 * @version	0.1-1
 * @created	July 8, 2014, 12:51 (EEST)
 * @revised	October 23, 2014, 14:23 (EEST)
 * 
 * @deprecated	Use \ESS\Environment\url instead.
 */
class url extends ESSUrl {}
//#section_end#
?>