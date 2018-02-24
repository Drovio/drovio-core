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

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;

/**
 * Global Product Manager
 * 
 * {description}
 * 
 * @version	2.0-2
 * @created	November 3, 2014, 9:53 (EET)
 * @revised	December 15, 2014, 11:55 (EET)
 * 
 * @deprecated	Use \RTL\Products\global\globalProduct and \RTL\Products\global\globalProductCodeManager instead.
 */
class globalProduct
{
	/**
	 * The Redback database connection manager.
	 * 
	 * @type	dbConnection
	 */
	private $dbc;
	
	/**
	 * The global product id.
	 * 
	 * @type	integer
	 */
	protected $globalProductID;
	
	/**
	 * Initialize the product.
	 * 
	 * @param	integer	$id
	 * 		The global product id.
	 * 		Leave empty for creating a new product with create().
	 * 
	 * @return	void
	 */
	public function __construct($id = "")
	{
		// Initialize global product id
		$this->globalProductID = $id;
		
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
	}
	
	/**
	 * Initialize the global product with a given product code (EAN, UPC etc.)
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types.
	 * 
	 * @param	integer	$productCode
	 * 		The product code.
	 * 
	 * @return	boolean
	 * 		True if the product code exists and it is connected to a global product, False otherwise.
	 */
	public function initWithCode($productCodeType, $productCode)
	{
		// Get global product by product code
		$this->dbc = new dbConnection();
		$q = new dbQuery("28157806635232", "retail.products.global");
		$attr = array();
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		$attr['time'] = time();
		$result = $this->dbc->execute($q, $attr);
		if ($this->dbc->get_num_rows($result) > 0)
		{
			// Get Global Product ID
			$globalProductInfo = $this->dbc->fetch($result);
			$this->globalProductID = $globalProductInfo['id'];
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Create a new global product.
	 * 
	 * @param	integer	$productHierarchy
	 * 		The product hierarchy id.
	 * 
	 * @param	string	$title
	 * 		The product title.
	 * 
	 * @param	string	$description
	 * 		The product description.
	 * 		It is empty by default.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	protected function create($productHierarchy, $title, $description = "")
	{
		// Create a new global product
		$this->dbc = new dbConnection();
		$q = new dbQuery("14479661672866", "retail.products.global");
		$attr = array();
		$attr['hierarchy'] = $productHierarchy;
		$attr['title'] = $title;
		$attr['description'] = $description;
		$result = $this->dbc->execute($q, $attr);
		
		if ($result)
		{
			$info = $this->dbc->fetch($result);
			$this->globalProductID = $info['id'];
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Add a new product code to this product.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types.
	 * 
	 * @param	integer	$productCode
	 * 		The product code.
	 * 
	 * @param	integer	$expirationTime
	 * 		The timestamp of the code expiration time.
	 * 		Leave empty if this information is not available.
	 * 		If empty, the expiration time will be set for the next 3-5 years.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure (the code already exists).
	 */
	public function addProductCode($productCodeType, $productCode, $expirationTime = "")
	{
		// Add a product code
		$q = new dbQuery("21009436635756", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		
		// If expiration time is empty, set to 3 years from now
		$attr['expire'] = (empty($expirationTime) ? time() + 60 * 60 * 24 * 365 * 3 : $expirationTime);
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$productCodeType
	 * 		{description}
	 * 
	 * @param	{type}	$productCode
	 * 		{description}
	 * 
	 * @param	{type}	$expirationTime
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function updateProductCode($productCodeType, $productCode, $expirationTime = "")
	{
		// Add a product code
		$q = new dbQuery("20704646695378", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		
		// If expiration time is empty, set to 3 years from now
		$attr['expire'] = (empty($expirationTime) ? time() + 60 * 60 * 24 * 365 * 3 : $expirationTime);
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
	
	
	/**
	 * {description}
	 * 
	 * @param	{type}	$productCodeType
	 * 		{description}
	 * 
	 * @return	void
	 */
	public function removeProductCode($productCodeType)
	{
		// Add a product code
		$q = new dbQuery("27582287158689", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$attr['type'] = $productCodeType;
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all product codes of the given global product.
	 * 
	 * @return	array
	 * 		An array of code information, like code, type and product id.
	 */
	public function getProductCodes()
	{
		// Add a product code
		$q = new dbQuery("31045157283542", "retail.products.codes");
		$attr = array();
		$attr['pid'] = $this->globalProductID;
		
		// Get product codes
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get product information.
	 * 
	 * @return	array
	 * 		An array of all product information, including hierarchy information.
	 */
	public function info()
	{
		$q = new dbQuery("32523210036816", "retail.products.global");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result);
	}
}
//#section_end#
?>