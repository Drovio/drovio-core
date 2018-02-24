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
 * Company product code manager
 * 
 * Manages product codes for company products.
 * If the product doesn't have a global reference, adding a new code will create the reference.
 * 
 * @version	2.0-2
 * @created	December 15, 2014, 16:16 (EET)
 * @updated	September 1, 2015, 16:33 (EEST)
 */
class cProductCodeManager
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
	 * Add a new product code to this product.
	 * This will also update a code of the same type.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types as retrieved from the database.
	 * 
	 * @param	string	$productCode
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
	public function set($productCodeType, $productCode, $expirationTime = "")
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
		
		$q = new dbQuery("21009436635756", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->productID;
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		$attr['expire'] = (empty($expirationTime) ? 0 : $expirationTime);
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Remove a code from a product.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type to remove.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($productCodeType)
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		$q = new dbQuery("27582287158689", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->productID;
		$attr['type'] = $productCodeType;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all product codes.
	 * 
	 * @return	array
	 * 		An array of all product code info.
	 */
	public function getAllCodes()
	{
		// Validate product code with company
		if (!$this->validate())
			return FALSE;
			
		$q = new dbQuery("31045157283542", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->productID;
		$result = $this->dbc->execute($q, $attr);
		return $this->dbc->fetch($result, TRUE);
	}
	
	/**
	 * Get all product code types.
	 * 
	 * @return	array
	 * 		An array of all product code types by id and title.
	 */
	public static function getCodeTypes()
	{
		$dbc = new dbConnection();
		$q = new dbQuery("20357141786342", "retail.products.codes");
		$result = $dbc->execute($q, $attr);
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