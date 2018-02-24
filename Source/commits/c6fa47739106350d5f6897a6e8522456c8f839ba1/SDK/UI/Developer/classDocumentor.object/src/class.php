<?php
//#section#[header]
// Namespace
namespace UI\Developer;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	UI
 * @package	Developer
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

/**
 * Class Documentor
 * 
 * Handles the documentation process of the classes. Used to display the documentor tool and as a bridge between user and documentor classes
 * 
 * @version	{empty}
 * @created	July 4, 2014, 14:13 (EEST)
 * @revised	July 5, 2014, 13:06 (EEST)
 * 
 * @deprecated	Use \DEV\Documentation\classDocEditor instead.
 */
class classDocumentor extends UIObjectPrototype
{	
}
//#section_end#
?>