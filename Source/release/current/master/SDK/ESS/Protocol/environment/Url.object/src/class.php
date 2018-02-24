<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\environment;

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
 * @namespace	\environment
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("ESS", "Environment", "url");

use \ESS\Environment\url as ESSUrl;

/**
 * URL Resolver
 * 
 * This class is responsible for url resolving.
 * It is used for resolving urls for resources and redirections.
 * 
 * @version	1.0-1
 * @created	July 29, 2014, 19:38 (EEST)
 * @revised	October 23, 2014, 14:22 (EEST)
 * 
 * @deprecated	Use \ESS\Environment\url instead.
 */
class Url extends ESSUrl {}
//#section_end#
?>