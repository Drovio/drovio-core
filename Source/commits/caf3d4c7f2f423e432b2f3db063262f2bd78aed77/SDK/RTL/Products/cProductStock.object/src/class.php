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
 * 
 * @copyright	Copyright (C) 2015 DrovIO. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Products", "cProduct");
importer::import("RTL", "Profile", "company");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Products\cProduct;
use \RTL\Profile\company;

/**
 * Company Product Stock Manager
 * 
 * Manages product stock in branches and in storages.
 * 
 * @version	0.1-1
 * @created	September 1, 2015, 17:08 (EEST)
 * @updated	September 1, 2015, 17:08 (EEST)
 */
class cProductStock
{
	/**
	 * The retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The product id.
	 * 
	 * @type	string
	 */
	private $productID;
	
	/**
	 * The branch id.
	 * 
	 * @type	string
	 */
	private $branchID;
	
	/**
	 * Whether the product is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	string	$productID
	 * 		The product id.
	 * 
	 * @param	string	$branchID
	 * 		The company branch id.
	 * 
	 * @return	void
	 */
	public function __construct($productID, $branchID)
	{
		// Initialize retail database connection
		$this->dbc = new dbConnection();
		
		// Set product id and branch id
		$this->productID = $productID;
		$this->branchID = $branchID;
	}
	
	/**
	 * Set the stock for a product in the given storage unit.
	 * 
	 * @param	float	$stock
	 * 		The product stock.
	 * 
	 * @param	integer	$storageID
	 * 		The storage id.
	 * 		It is optional and it is 1 by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($stock, $storageID = 1)
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		// Set product stock
		$dbc = new dbConnection();
		$q = new dbQuery("22457270538554", "retail.products.stock");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['bid'] = $this->branchID;
		$attr['storage'] = $storageID;
		$attr['stock'] = $stock;
		return $dbc->execute($q, $attr);
	}
	
	/**
	 * Get the stock for the product in the current branch.
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
	 * 		An array of product stock per branch or per storage, depending on the parameters.
	 */
	public function get($includeStorage = FALSE, $storageID = NULL)
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		// Get product stock
		$dbc = new dbConnection();
		
		// Include storage ids or not
		if ($includeStorage)
			$q = new dbQuery("25573349065969", "retail.products.stock");
		else
			$q = new dbQuery("14731582093376", "retail.products.stock");
			
		$attr = array();
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
	
	/**
	 * Set the unit of measurement for the current product.
	 * 
	 * @param	integer	$mUnit
	 * 		The unit of measurement id reference.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setMUnit($mUnit)
	{
		// Set product measurement unit
		$q = new dbQuery("17841505452715", "retail.products.stock");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		$attr['unit'] = $mUnit;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all units of measurement that the sdk supports.
	 * 
	 * @return	array
	 * 		An array of all units by id and title.
	 */
	public static function getMeasurementUnits()
	{
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("33985512890653", "retail.products.stock");
		$result = $dbc->execute($q);
		return $dbc->toArray($result, "id", "title");
	}
	
	/**
	 * Validate whether the given product is of the current company.
	 * 
	 * @return	boolean
	 * 		True if valid, false otherwise.
	 */
	private function validate()
	{
		// Check class cache
		if ($this->valid)
			return $this->valid;
			
		// Get current team/company id
		$companyID = company::getCompanyID();
		if (empty($companyID))
			return FALSE;
		
		// Get product's owner team/company id
		$product = new cProduct($this->productID);
		$productInfo = $product->info();
		
		// Check if it is the same team/company
		$this->valid = ($productInfo['owner_company_id'] == $companyID);
		return $this->valid;
	}
}
//#section_end#
?>