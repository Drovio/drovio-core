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

importer::import("SYS", "Comm", "db/dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "globalProduct");

use \SYS\Comm\db\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;
use \RTL\Products\globalProduct;

/**
 * Company Product Manager
 * 
 * {description}
 * 
 * @version	1.0-1
 * @created	November 3, 2014, 9:54 (EET)
 * @revised	November 20, 2014, 17:32 (EET)
 */
class companyProduct extends globalProduct
{
	/**
	 * The Redback database connection manager.
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
	 * Initialize the company product.
	 * 
	 * @param	integer	$id
	 * 		The company product id.
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
		$globalProductID = "";
		
		if (!empty($id))
		{
			// Set company product id
			$this->companyProductID = $id;
			
			// Get product info
			$productInfo = $this->info(FALSE);
			$globalProductID = $productInfo['global_product_id'];
		}
		
		// Initialize global product
		parent::__construct($globalProductID);
	}
	
	/**
	 * Create a new company product.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types.
	 * 
	 * @param	integer	$productCode
	 * 		The unique product code (EAN, UPC etc.)
	 * 
	 * @param	integer	$productHierarchy
	 * 		The product hierarchy id.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @param	string	$description
	 * 		The product description.
	 * 
	 * @return	void
	 */
	public function create($productCodeType, $productCode, $productHierarchy, $title, $description = "")
	{
		// Init global product by product code
		if (!$this->initWithCode($productCodeType, $productCode))
		{
			// Create global product
			$status = parent::create($productHierarchy, $title, $description);
			if (!$status)
				return FALSE;
				
			// Add a product code
			$this->addProductCode($productCodeType, $productCode);
		}
		
		// Create company product
		$q = new dbQuery("15790019094335", "retail.products");
		$attr = array();
		$attr['gpid'] = $this->globalProductID;
		$attr['cid'] = company::getCompanyID();
		$attr['hid'] = $productHierarchy;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $this->dbc->execute($q, $attr);
		if ($result)
		{
			$productInfo = $this->dbc->fetch($result);
			$this->companyProductID = $productInfo['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Get product information about a company product.
	 * 
	 * @param	boolean	$includeGlobal
	 * 		If set to TRUE, the array will include global product information.
	 * 
	 * @return	array
	 * 		An array of product information.
	 * 		It can include global product information.
	 */
	public function info($includeGlobal = FALSE)
	{
		// Get company product info
		$q = new dbQuery("20607211206042", "retail.products");
		$attr = array();
		$attr['pid'] = $this->companyProductID;
		$attr['cid'] = company::getCompanyID();
		$result = $this->dbc->execute($q, $attr);
		$productInfo = $this->dbc->fetch($result);
		
		// Get global product info
		if ($includeGlobal)
		{
			$productInfo['global'] = parent::info();
			$productInfo['codes'] = parent::getProductCodes();
		}
		
		return $productInfo;
	}
	
	/**
	 * Update product information.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @param	string	$description
	 * 		The product description
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function updateInfo($title = "", $description = "")
	{
		// Update company product info
		$q = new dbQuery("29080143278493", "retail.products");
		$attr = array();
		$attr['pid'] = $this->companyProductID;
		$attr['cid'] = company::getCompanyID();
		$attr['title'] = $title;
		$attr['description'] = $description;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all product prices for the active company.
	 * 
	 * @return	array
	 * 		An array of all prices, including the tax rate and the price type title.
	 */
	public function getPrices()
	{
		// Set product price
		$q = new dbQuery("17383966877549", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Set a price for the current product.
	 * 
	 * @param	integer	$priceType
	 * 		The price type.
	 * 
	 * @param	float	$price
	 * 		The product price.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setPrice($priceType, $price)
	{
		// Set product price
		$q = new dbQuery("28240796859702", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['type'] = $priceType;
		$attr['price'] = $price;
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
	public function removePrice($priceType)
	{
		// Remove product price
		$q = new dbQuery("34438601673638", "retail.products.prices");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['type'] = $priceType;
		return $this->dbc->execute($q, $attr);
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
		$attr['pid'] = $this->companyProductID;
		$attr['rate'] = $taxRate;
		return $this->dbc->execute($q, $attr);
	}
}
//#section_end#
?>