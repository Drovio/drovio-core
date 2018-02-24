<?php
//#section#[header]
// Namespace
namespace API\Resources;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Protocol", "client::environment::Url");

use \ESS\Protocol\client\environment\Url as ESSUrl;

/**
 * Redback URL Manager
 * 
 * Manages all url resolving for navigation and resources.
 * 
 * @version	{empty}
 * @created	October 23, 2013, 10:54 (EEST)
 * @revised	October 23, 2013, 10:54 (EEST)
 */
class url extends ESSUrl {}
//#section_end#
?>