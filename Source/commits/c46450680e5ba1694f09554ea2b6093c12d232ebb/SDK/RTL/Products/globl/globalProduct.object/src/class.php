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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "privileges");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\privileges;

/**
 * Global Product Manager
 * 
 * Manages global products.
 * Create new product, update and get information.
 * 
 * @version	0.1-2
 * @created	December 17, 2014, 14:30 (EET)
 * @updated	January 7, 2015, 15:15 (EET)
 */
class globalProduct
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
	 * 		It references to EAN, UPC and other code types from the retail database.
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
	 * @param	integer	$hierarchyID
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
	protected function create($hierarchyID, $title, $description = "")
	{
		// Create a new global product
		$this->dbc = new dbConnection();
		$q = new dbQuery("14479661672866", "retail.products.global");
		$attr = array();
		$attr['hierarchy'] = $hierarchyID;
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
	 * Update global product information.
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
	protected function update($title, $description = "")
	{
		// Check if account is in the right user group
		if (!privileges::accountToGroup("RTL_PRODUCT_MANAGER"))
			return FALSE;
			
		// Create a new global product
		$this->dbc = new dbConnection();
		$q = new dbQuery("", "retail.products.global");
		$attr = array();
		$attr['pid'] = $this->globalProductID;
		$attr['title'] = $title;
		$attr['description'] = $description;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Get all product information.
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