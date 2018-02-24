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

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;

/**
 * Company Product Manager
 * 
 * Fetches products by hierarchy or by title.
 * Future to be extended with more functionality and getters.
 * 
 * @version	0.1-1
 * @created	December 15, 2014, 16:33 (EET)
 * @revised	December 15, 2014, 16:33 (EET)
 */
class cProductManager
{
	/**
	 * Get all company products inside a given hierarchy id.
	 * 
	 * @param	integer	$hierarchyID
	 * 		The hierarchy id to get the products for.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 */
	public static function getProductsByHierarchy($hierarchyID)
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
	 * 		Default value is '%'.
	 * 
	 * @return	array
	 * 		An array of all product information.
	 */
	public static function getProductsByTitle($title = "%")
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
}
//#section_end#
?>