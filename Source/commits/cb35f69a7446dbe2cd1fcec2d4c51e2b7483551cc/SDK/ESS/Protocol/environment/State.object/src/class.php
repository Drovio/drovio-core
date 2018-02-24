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

/**
 * Browser State Manager
 * 
 * Manages all the browser's state changes (Javascript) and keeps a history of what is happening.
 * 
 * @version	0.1-1
 * @created	July 29, 2014, 19:01 (EEST)
 * @revised	July 29, 2014, 19:01 (EEST)
 */
class State {}
//#section_end#
?>