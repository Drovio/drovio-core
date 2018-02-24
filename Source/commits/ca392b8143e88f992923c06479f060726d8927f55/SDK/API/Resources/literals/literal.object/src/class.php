<?php
//#section#[header]
// Namespace
namespace API\Resources\literals;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Resources
 * @namespace	\literals
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("API", "Literals", "literal");

use \API\Literals\literal as newLiteral;

/**
 * Literal Handler
 * 
 * Handles all translated and non-translated literals.
 * 
 * @version	v. 0.1-0
 * @created	April 23, 2013, 14:00 (EEST)
 * @revised	July 9, 2014, 11:02 (EEST)
 * 
 * @deprecated	Use \API\Literals\literal instead.
 */
class literal extends newLiteral {}
//#section_end#
?>