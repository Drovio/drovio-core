<?php
//#section#[header]
// Namespace
namespace RTL\Products\globl;

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
 * @namespace	\globl
 * 
 * @copyright	Copyright (C) 2014 Skyworks SD. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\privileges;

/**
 * Global product code manager
 * 
 * Manages product codes like EAN, UPC and many more
 * 
 * @version	0.1-1
 * @created	December 17, 2014, 14:34 (EET)
 * @revised	December 17, 2014, 14:34 (EET)
 */
class globalProductCodeManager
{
	/**
	 * The Redback retail database connection manager.
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
	 * Initialize the manager.
	 * 
	 * @param	integer	$productID
	 * 		The global product id.
	 * 
	 * @return	void
	 */
	public function __construct($productID)
	{
		// Initialize global product id
		$this->globalProductID = $productID;
		
		// Initialize Redback database connection
		$this->dbc = new dbConnection();
	}
	
	/**
	 * Add a new product code to this product.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types as retrieved from the database.
	 * 
	 * @param	integer	$productCode
	 * 		The product code.
	 * 
	 * @param	integer	$expirationTime
	 * 		The duration of the code expiration time in the future.
	 * 		Leave empty if this information is not available.
	 * 		If empty, the expiration time will be set for the next 3-5 years.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure (the code already exists).
	 */
	public function add($productCodeType, $productCode, $expirationTime = "")
	{
		// Add a product code
		$q = new dbQuery("21009436635756", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		
		// If expiration time is empty, set to 3 years from now
		$expirationTime = (empty($expirationTime) ? 60 * 60 * 24 * 365 * 3 : $expirationTime);
		$attr['expire'] = time() + $expirationTime;
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Update a product code value.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 
	 * @param	integer	$productCode
	 * 		The new product code.
	 * 
	 * @param	integer	$expirationTime
	 * 		The duration of the code expiration time in the future.
	 * 		Leave empty if this information is not available.
	 * 		If empty, the expiration time will be set for the next 3-5 years.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function update($productCodeType, $productCode, $expirationTime = "")
	{
		// Add a product code
		$q = new dbQuery("20704646695378", "retail.products.codes");
		$attr = array();
		$attr['id'] = $this->globalProductID;
		$attr['type'] = $productCodeType;
		$attr['code'] = $productCode;
		
		// If expiration time is empty, set to 3 years from now
		$expirationTime = (empty($expirationTime) ? 60 * 60 * 24 * 365 * 3 : $expirationTime);
		$attr['expire'] = time() + $expirationTime;
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
	
	
	/**
	 * Remove an existing product code.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type to be removed.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function remove($productCodeType)
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
	public function get()
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
	 * Get all inventory product code types.
	 * 
	 * @return	array
	 * 		An array of all product code types.
	 */
	public static function getCodeTypes()
	{
		// Get all product code types
		$dbc = new dbConnection();
		$q = new dbQuery("20357141786342", "retail.products.codes");
		$result = $dbc->execute($q);
		return $dbc->fetch($result, TRUE);
	}
	
	/**
	 * Add a new product code type.
	 * 
	 * @param	string	$title
	 * 		The code type title.
	 * 
	 * @param	string	$description
	 * 		The code type description.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public static function addCodeType($title, $description = "")
	{
		// Check if account is in the right user group
		if (!privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Add a product code
		$q = new dbQuery("", "retail.products.codes");
		$attr = array();
		$attr['title'] = $title;
		$attr['description'] = $description;
		
		// Execute and return TRUE or FALSE
		return $this->dbc->execute($q, $attr);
	}
}
//#section_end#
?>