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
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "globl/globalHierarchy");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;
use \RTL\Products\globl\globalHierarchy;

/**
 * Company product hierarchy manager
 * 
 * Manages company product hierarchy.
 * Gets specific company hierarchy (active or not) and adds company specific hierarchy.
 * 
 * @version	0.1-2
 * @created	December 15, 2014, 16:29 (EET)
 * @revised	December 17, 2014, 14:35 (EET)
 */
class cProductHierarchy extends globalHierarchy
{
	/**
	 * Get the company product hierarchy in an array.
	 * 
	 * @param	boolean	$active
	 * 		If active, the function will return only the company hierarchies that have company products.
	 * 		Otherwise, it will return all company hierarchies only.
	 * 
	 * @return	array
	 * 		An array of all product hierarchies.
	 */
	public static function get($active = FALSE)
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
	 */
	public static function add($parentHierarchy_id, $title, $description = "")
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