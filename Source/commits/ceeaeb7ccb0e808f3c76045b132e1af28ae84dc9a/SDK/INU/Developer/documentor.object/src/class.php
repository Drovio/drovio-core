<?php
//#section#[header]
// Namespace
namespace INU\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	INU
 * @package	Developer
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("INU", "Developer", "documentation::classDocumentor");

use \INU\Developer\documentation\classDocumentor;
/**
 * Documentor
 * 
 * Handles the documentation process of the classes.
 * 
 * @version	{empty}
 * @created	April 26, 2013, 14:53 (EEST)
 * @revised	June 28, 2013, 10:43 (EEST)
 * 
 * @deprecated	Use \INU\Developer\documentation\classDocumentor instead
 */
class documentor extends classDocumentor {}
//#section_end#
?>