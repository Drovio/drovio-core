<?php
//#section#[header]
// Namespace
namespace AEL\Platform;

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
 * @package	Platform
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Application Engine Class Loader
 * 
 * Manager for importing all classes from the Core SDK and the application's source.
 * 
 * @version	{empty}
 * @created	January 9, 2014, 20:08 (EET)
 * @revised	July 7, 2014, 11:19 (EEST)
 * 
 * @deprecated	Use \API\Platform\importer instead.
 */
class classLoader extends importer {}
//#section_end#
?>