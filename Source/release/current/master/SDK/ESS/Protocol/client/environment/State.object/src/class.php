<?php
//#section#[header]
// Namespace
namespace ESS\Protocol\client\environment;

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
 * @namespace	\client\environment
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Browser State Manager
 * 
 * Manages all the browser's state changes (Javascript) and keeps a history of what is happening.
 * 
 * @version	0.1-1
 * @created	March 7, 2013, 11:40 (EET)
 * @revised	July 29, 2014, 19:01 (EEST)
 * 
 * @deprecated	Use \ESS\Protocol\environment\State instead.
 */
class State {}
//#section_end#
?>