<?php
//#section#[header]
// Namespace
namespace RTL\Products\company;

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
 * @namespace	\company
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "globalInventory");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;
use \RTL\Products\globalInventory;

/**
 * Company Product inventory manager
 * 
 * Manages company product hierarchy.
 * It also can fetch products of a given hierarchy or with a specific title.
 * 
 * @version	0.2-1
 * @created	November 3, 2014, 11:13 (EET)
 * @revised	December 15, 2014, 16:37 (EET)
 * 
 * @deprecated	Use \RTL\Products\cProductHierarchy instead. See functions separately for deprecation.
 */
class companyInventory extends globalInventory
{
	/**
	 * Get the company product hierarchy in an array.
	 * It includes all the company's hierarchy records.
	 * 
	 * @param	boolean	$active
	 * 		If active, the function will return only the company hierarchies that have company products.
	 * 		Otherwise, it will return all company hierarchies only.
	 * 
	 * @return	array
	 * 		An array of all product hierarchies
	 * 
	 * @deprecated	Use \RTL\Products\cProductHierarchy::get() instead.
	 */
	public static function getHierarchy($active = FALSE)
	{
		// Get company product hierarchy
		$dbc = new dbConnection();
		if ($active)
			$q = new dbQuery("33227450903919", "retail.products.inventory");
		else
			$q = new dbQuery("19861165790137", "retail.products.inventory");
		
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all company products inside a given hierarchy id.
	 * 
	 * @param	integer	$hierarchyID
	 * 		The hierarchy id to get the products for.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 * 
	 * @deprecated	Use \RTL\Products\cProductManager::getProductsByHierarchy() instead.
	 */
	public static function getHierarchyProducts($hierarchyID)
	{
		// Get global product hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("1644169234483", "retail.products.inventory");
		
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['hid'] = $hierarchyID;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all company products that match the given title.
	 * 
	 * @param	string	$title
	 * 		The title match.
	 * 		It is an SQL-like match query.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 * 
	 * @deprecated	Use \RTL\Products\cProductManager::getProductsByTitle() instead.
	 */
	public static function getProducts($title = "*")
	{
		// Get global product hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("31355635191824", "retail.products.inventory");
		
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['title'] = $title;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Add a new company hierarchy record.
	 * 
	 * @param	integer	$parentHierarchy_id
	 * 		The parent hierarchy id.
	 * 
	 * @param	string	$title
	 * 		The hierarchy title.
	 * 
	 * @param	string	$description
	 * 		The hierarchy description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 * 
	 * @deprecated	Use \RTL\Products\cProductHierarchy::add() instead.
	 */
	public static function addHierarchy($parentHierarchy_id, $title, $description = "")
	{
		// Add new company hierarchy
		$dbc = new dbConnection();
		$q = new dbQuery("20364336591042", "retail.products.inventory");
		
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $parentHierarchy_id;
		$attr['title'] = $title;
		$attr['desc'] = $description;
		$result = $dbc->execute($q, $attr);
		return $dbc->fetch($result, TRUE);
	}
}
//#section_end#
?>