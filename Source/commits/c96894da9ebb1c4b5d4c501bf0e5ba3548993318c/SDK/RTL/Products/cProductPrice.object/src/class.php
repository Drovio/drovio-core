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
 * Company Product Price Manager
 * 
 * Sets, gets and removes product prices.
 * To be used only by product managers.
 * Changing product prices will have immediate effect everywhere.
 * 
 * All prices are saved without any taxes or VATs.
 * 
 * @version	1.0-1
 * @created	December 15, 2014, 16:54 (EET)
 * @updated	August 31, 2015, 18:35 (EEST)
 */
class cProductPrice
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
	 * @type	integer
	 */
	private $productID;
	
	/**
	 * Whether the product is valid with the current company.
	 * 
	 * @type	boolean
	 */
	private $valid;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	integer	$id
	 * 		The company product id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize database connection
		$this->dbc = new dbConnection();
		$this->productID = $id;
	}
	
	/**
	 * Set a product price.
	 * This will also update an existing price of the same type.
	 * 
	 * @param	integer	$priceType
	 * 		The price type as it fetched from the getPriceTypes() function.
	 * 
	 * @param	float	$price
	 * 		The product price.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function set($priceType, $price)
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		// Set product price
		$q = new dbQuery("28240796859702", "retail.products.prices");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['type'] = $priceType;
		$attr['price'] = $price;
		$attr['time'] = time();
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a price from the current product.
	 * 
	 * @param	integer	$priceType
	 * 		The price type to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($priceType)
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		// Remove product price
		$q = new dbQuery("34438601673638", "retail.products.prices");
		$attr = array();
		$attr['pid'] = $this->productID;
		$attr['type'] = $priceType;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all product prices.
	 * 
	 * @return	array
	 * 		All product prices information.
	 */
	public function getAllPrices()
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		// Set product price
		$q = new dbQuery("17383966877549", "retail.products.prices");
		$attr = array();
		$attr['pid'] = $this->productID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all company price types.
	 * 
	 * @return	array
	 * 		An array of all price types by id and title.
	 */
	public static function getPriceTypes()
	{
		// Get all tax rates in the given company
		$dbc = new dbConnection();
		$q = new dbQuery("26594258916143", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$result = $dbc->execute($q, $attr);
		return $dbc->toArray($result, "id", "title");
	}
	
	/**
	 * Set the tax rate id for the current product.
	 * 
	 * @param	integer	$taxRate
	 * 		The tax rate id.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setTaxRate($taxRate)
	{
		// Set product tax rate
		$q = new dbQuery("28066033210391", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->productID;
		$attr['rate'] = $taxRate;
		return $this->dbc->execute($q, $attr);
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