<?php
//#section#[header]
// Namespace
namespace RTL\Products\global;

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
 * @namespace	\global
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\privileges;

/**
 * Global product hierarchy manager
 * 
 * Add, get, remove and update global product hierarchy entries.
 * 
 * @version	0.1-1
 * @created	December 15, 2014, 11:35 (EET)
 * @revised	December 15, 2014, 11:35 (EET)
 */
class globalHierarchy
{
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
	 */
	public static function add($parentHierarchy_id, $title, $description = "")
	{
		// Check if account is in the right user group
		if (!privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
		
		// Check parent id
		if (empty($parentHierarchy_id))
			return FALSE;
		
		// Add new global hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("20364336591042", "retail.products.inventory");
		
		$attr['cid'] = "NULL";
		$attr['pid'] = $parentHierarchy_id;
		$attr['title'] = $title;
		$attr['desc'] = $description;
		$result = $dbc->execute($q);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get the global product hierarchy in an array.
	 * 
	 * @return	array
	 * 		An array of all product hierarchies
	 */
	public static function get()
	{
		// Get global product hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("19945959574559", "retail.products.inventory");
		$result = $dbc->execute($q);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Update a global product hierarchy (title and description).
	 * 
	 * @param	integer	$hierarchyID
	 * 		The hierarchy id.
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
	 */
	public static function update($hierarchyID, $title, $description = "")
	{
		// Check if account is in the right user group
		if (!privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Get global product hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("", "retail.products.inventory");
		
		$attr['hid'] = $hierarchyID;
		$attr['title'] = $title;
		$attr['desc'] = $description;
		return $dbc->execute($q);
	}
}
//#section_end#
?>