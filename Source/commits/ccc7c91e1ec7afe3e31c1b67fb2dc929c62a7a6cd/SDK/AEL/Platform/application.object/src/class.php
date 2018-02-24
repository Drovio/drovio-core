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
class application
{
	public static function init()
	{
	}
	
	public static function import()
	{
	}
}
//#section_end#
?>