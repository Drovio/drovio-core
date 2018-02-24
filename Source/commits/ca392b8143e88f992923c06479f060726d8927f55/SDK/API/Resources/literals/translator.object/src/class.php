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

importer::import("API", "Literals", "translator");

use \API\Literals\translator as newTranslator;

/**
 * Literal Translator
 * 
 * System's literal translation engine.
 * 
 * @version	v. 0.1-0
 * @created	April 23, 2013, 14:01 (EEST)
 * @revised	July 9, 2014, 11:03 (EEST)
 * 
 * @deprecated	Use \API\Literals\translator instead.
 */
class translator extends newTranslator {}
//#section_end#
?>