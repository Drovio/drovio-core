<?php
//#section#[header]
// Namespace
namespace API\Content\literals;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
/**
 * @library	{empty}
 * @package	{empty}
 * @namespace	{empty}
 * 
 * @copyright	Copyright (C) 2013 Skyworks SD. All rights reserved.
 */

importer::import("API", "Resources", "literals::moduleLiteral");

use \API\Resources\literals\moduleLiteral as APIModuleLiteral;

/**
 * {title}
 * 
 * Usage
 * 
 * @version	{empty}
 * @created	{empty}
 * @revised	{empty}
 * 
 * @deprecated	Use \API\Resources\literals\moduleLiteral instead.
 */
class moduleLiteral extends APIModuleLiteral {}
//#section_end#
?>