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
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

/**
 * Browser State Manager
 * 
 * Manages all the browser's state changes (Javascript) and keeps a history of what is happening.
 * 
 * @version	0.1-2
 * @created	October 23, 2014, 14:07 (BST)
 * @updated	October 27, 2015, 15:03 (GMT)
 */
class state {}
//#section_end#
?>