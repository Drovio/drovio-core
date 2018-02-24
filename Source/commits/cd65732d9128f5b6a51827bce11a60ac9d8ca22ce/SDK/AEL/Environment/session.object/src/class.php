<?php
//#section#[header]
// Namespace
namespace AEL\Environment;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	AEL
 * @package	Environment
 * 
 * @copyright	Copyright (C) 2015 Drovio. All rights reserved.
 */

importer::import("ESS", "Environment", "session");
importer::import("AEL", "Platform", "application");

use \ESS\Environment\session as APISession;
use \AEL\Platform\application;

/**
 * Application Session Manager
 * 
 * Manages session storage on behalf of the application.
 * 
 * @version	0.1-1
 * @created	October 19, 2015, 17:43 (BST)
 * @updated	October 19, 2015, 17:43 (BST)
 */
class session extends APISession {}
//#section_end#
?>