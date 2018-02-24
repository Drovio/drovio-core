<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document;

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
 * @package	Developer
 * @namespace	\content\document
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */
interface iParser {}
//#section_end#
?>