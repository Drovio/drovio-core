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
/**
 * @library	RTL
 * @package	Products
 * @namespace	\
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");
importer::import("RTL", "Products", "global/globalHierarchy");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\privileges;
use \RTL\Products\global\globalHierarchy;

/**
 * Global Product inventory manager
 * 
 * Manages global product hierarchy.
 * 
 * @version	1.2-1
 * @created	November 3, 2014, 11:12 (EET)
 * @revised	December 15, 2014, 13:11 (EET)
 * 
 * @deprecated	This class is deprecated. See each function for further information.
 */
class globalInventory
{
	/**
	 * Get the global product hierarchy in an array.
	 * 
	 * @return	array
	 * 		An array of all product hierarchies
	 * 
	 * @deprecated	Use \RTL\Products\global\globalHierarchy::get() instead.
	 */
	public static function getHierarchy()
	{
		return globalHierarchy::get();
	}
	
	/**
	 * Add a new global product hierarchy.
	 * 
	 * NOTE: You must be member of the Retail Product Manager user group in order to add a global hierarchy.
	 * 
	 * @param	integer	$parentHierarchy_id
	 * 		The parent hierarchy id.
	 * 
	 * @param	string	$title
	 * 		The hierarchy title.
	 * 
	 * @param	string	$description
	 * 		The hierarchy description.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use \RTL\Products\global\globalHierarchy::add() instead.
	 */
	public static function addHierarchy($parentHierarchy_id, $title, $description = "")
	{
		return globalHierarchy::add($parentHierarchy_id, $title, $description);
	}
	
	/**
	 * Get all inventory product code types.
	 * 
	 * @return	array
	 * 		An array of all product code types.
	 * 
	 * @deprecated	Use \RTL\Products\global\globalProductCodeManager::getCodeTypes() instead.
	 */
	public static function getProductCodeTypes()
	{
		// Get all product code types
		$dbc = new dbConnection();
		$q = new dbQuery("20357141786342", "retail.products.codes");
		$result = $dbc->execute($q);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>