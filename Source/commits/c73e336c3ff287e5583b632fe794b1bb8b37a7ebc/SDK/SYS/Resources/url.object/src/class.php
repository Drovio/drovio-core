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
 * @namespace	{empty}
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
 * @version	{empty}
 * @created	July 8, 2014, 12:51 (EEST)
 * @revised	July 8, 2014, 12:51 (EEST)
 */
class url extends ESSUrl {}
//#section_end#
?>