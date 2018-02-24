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
 * Company Product Quantity Manager
 * 
 * Manages product quantities in branches and in storages.
 * 
 * @version	0.1-1
 * @created	December 16, 2014, 13:24 (EET)
 * @revised	December 16, 2014, 13:24 (EET)
 */
class cProductQuantity
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	/**
	 * The company product id.
	 * 
	 * @type	integer
	 */
	protected $companyProductID;
	
	/**
	 * The company branch id.
	 * 
	 * @type	integer
	 */
	protected $branchID;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	integer	$productID
	 * 		The company product id.
	 * 
	 * @param	integer	$branchID
	 * 		The company branch id.
	 * 
	 * @return	void
	 */
	public function __construct($productID, $branchID)
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		
		// Set company product id and branch id
		$this->companyProductID = $id;
		$this->branchID = $id;
	}
	
	/**
	 * Set the quantity for a product in the given storage unit.
	 * 
	 * @param	float	$quantity
	 * 		The product quantity.
	 * 
	 * @param	integer	$storageID
	 * 		The storage id.
	 * 		It is optional and it is 1 by default.
	 * 		This should change in the next version because storage units must be created manually for every company.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($quantity, $storageID = 1)
	{
		// Get product stock
		$dbc = new dbConnection();
		$q = new dbQuery("22457270538554", "retail.products.stock");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['bid'] = $this->branchID;
		$attr['storage'] = $storageID;
		$attr['quantity'] = $quantity;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get the quantity for the product in the current branch.
	 * 
	 * @param	boolean	$includeStorage
	 * 		If include storage, the array returned will have records for each storage in each branch.
	 * 		It is FALSE by default.
	 * 
	 * @param	integer	$storageID
	 * 		It the includeStorage is set to TRUE, this will define the storage id to get quantity for, explicitly.
	 * 		Leave NULL for all storage units.
	 * 		It is NULL by default.
	 * 
	 * @return	array
	 * 		An array of product quantity per branch or per storage, depending on the arguments.
	 */
	public function get($includeStorage = FALSE, $storageID = NULL)
	{
		// Get product stock
		$dbc = new dbConnection();
		
		// Include storage ids or not
		if ($includeStorage)
			$q = new dbQuery("25573349065969", "retail.products.stock");
		else
			$q = new dbQuery("14731582093376", "retail.products.stock");
			
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['bid'] = $this->branchID;
		$result = $dbc->execute($q, $attr);
		$productQuantity = $dbc->fetch($result, TRUE);
		
		// Check if should include storage and return specific storage id
		if ($includeStorage && !empty($storageID))
			foreach ($productQuantity as $pq)
				if ($pq['storage_id'] == $storageID)
					return $pq;
		
		return $productQuantity;
	}
}
//#section_end#
?>