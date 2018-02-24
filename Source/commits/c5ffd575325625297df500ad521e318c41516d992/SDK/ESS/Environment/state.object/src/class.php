<?php
//#section#[header]
// Namespace
namespace ESS\Environment;

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
 * @package	Environment
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Browser State Manager
 * 
 * Manages all the browser's state changes (Javascript) and keeps a history of what is happening.
 * 
 * @version	0.1-1
 * @created	October 23, 2014, 16:07 (EEST)
 * @revised	October 23, 2014, 16:07 (EEST)
 */
class state {}
//#section_end#
?>