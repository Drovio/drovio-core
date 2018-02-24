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
importer::import("RTL", "Products", "global/globalProduct");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \RTL\Profile\company;
use \RTL\Products\global\globalProduct;

/**
 * Company Product Manager
 * 
 * {description}
 * 
 * @version	0.1-1
 * @created	December 15, 2014, 15:48 (EET)
 * @revised	December 15, 2014, 15:48 (EET)
 */
class cProduct extends globalProduct
{
	/**
	 * The Redback retail database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The company relative product id.
	 * 
	 * @type	integer
	 */
	protected $companyProductID;
	
	/**
	 * Initialize the company product.
	 * 
	 * @param	integer	$id
	 * 		The company product id.
	 * 		Leave empty for new product.
	 * 		It is empty by default.
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
	 * @param	integer	$productHierarchy
	 * 		The product hierarchy id.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @param	string	$description
	 * 		The product description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function create($productHierarchy, $title, $description = "")
	{
		// Check empty
		if (empty($productHierarchy) || empty($title))
			return FALSE;
			
		// Create company product
		$q = new dbQuery("15790019094335", "retail.products");
		$attr = array();
		$attr['gpid'] = "NULL";
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
	 * Get all information about this product.
	 * 
	 * @param	boolean	$includeGlobal
	 * 		If set to TRUE the the product has a global product reference, the array will include global product information.
	 * 
	 * @return	arra
	 * 		An array of product information.
	 */
	public function info($includeGlobal = FALSE)
	{
		// Get company product info
		$q = new dbQuery("20607211206042", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$result = $this->dbc->execute($q, $attr);
		$productInfo = $this->dbc->fetch($result);
		
		// Get global product info
		if ($includeGlobal && !empty($this->globalProductID))
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
	 * 		The product description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($title = "", $description = "")
	{
		// Update company product info
		$q = new dbQuery("29080143278493", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['title'] = $title;
		$attr['description'] = $description;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Activate the company product to be publicly visible.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function activate()
	{
		// Update company product info
		$q = new dbQuery("", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['status'] = 1;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Deactivate the company product to be publicly invisible.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function deactivate()
	{
		// Update company product info
		$q = new dbQuery("", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['status'] = 1;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set the product's tax rate value.
	 * 
	 * @param	integer	$taxRate
	 * 		The tax rate id as you get it from the financial API.
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