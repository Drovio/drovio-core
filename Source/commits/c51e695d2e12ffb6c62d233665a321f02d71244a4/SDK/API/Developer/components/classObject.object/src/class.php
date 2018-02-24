<?php
//#section#[header]
// Namespace
namespace API\Developer\components;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	API
 * @package	Developer
 * @namespace	\components
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Developer", "components::prime::classObject");

use \API\Developer\components\prime\classObject as primeClassObject;

/**
 * Abstract Class Object
 * 
 * Handles the basics of a class object handler including the capability of documentation.
 * 
 * @version	{empty}
 * @created	May 9, 2013, 15:39 (EEST)
 * @revised	May 17, 2013, 14:23 (EEST)
 * 
 * @deprecated	Use \API\Developer\components\prime\classObject instead.
 */
class classObject extends primeClassObject {}
//#section_end#
?>