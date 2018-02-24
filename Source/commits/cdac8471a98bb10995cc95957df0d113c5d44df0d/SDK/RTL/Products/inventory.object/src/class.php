<?php
//#section#[header]
// Namespace
namespace RTL\Products;

// Use Important Headers
use \API\Platform\importer;
use \Exception;

// Check Platform Existance
if (!defined('_RB_PLATFORM_'))
	throw new Exception("Platform is not defined!");
//#section_end#
//#section#[class]
class inventory
{
	public static function getHierarchy()
	{
	}
	
	public static function getHierarchyProducts($hierarchyID = NULL)
	{
	}
	
	public static function getProducts($title = "*")
	{
	}
}
//#section_end#
?>