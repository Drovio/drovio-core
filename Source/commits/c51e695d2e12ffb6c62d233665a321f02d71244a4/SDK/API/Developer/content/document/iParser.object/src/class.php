<?php
//#section#[header]
// Namespace
namespace API\Developer\content\document;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_')) throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
// Import

// Usage

interface iParser
{
	public static function wrap($content);
	
	public static function unwrap($content);
	
	public static function get_comment($content, $multi = FALSE);
	
	public static function get_variable($name);
}
//#section_end#
?>