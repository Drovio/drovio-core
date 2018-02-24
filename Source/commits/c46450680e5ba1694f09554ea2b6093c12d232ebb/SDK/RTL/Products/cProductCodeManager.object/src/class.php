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
 * @copyright	Copyright (C) 2015 Redback. All rights reserved.
 */

importer::import("RTL", "Comm", "dbConnection");
importer::import("API", "Model", "sql/dbQuery");
importer::import("API", "Security", "accountKey");
importer::import("RTL", "Products", "globl/globalProductCodeManager");
importer::import("RTL", "Products", "cProduct");
importer::import("RTL", "Profile", "company");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\accountKey;
use \RTL\Products\globl\globalProductCodeManager;
use \RTL\Products\cProduct;
use \RTL\Profile\company;

/**
 * Company product code manager
 * 
 * Manages product codes for company products.
 * If the product doesn't have a global reference, adding a new code will create the reference.
 * 
 * @version	0.1-4
 * @created	December 15, 2014, 16:16 (EET)
 * @updated	January 7, 2015, 15:15 (EET)
 */
class cProductCodeManager extends globalProductCodeManager
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
			$product = new cProduct($id);
			$productInfo = $product->info(FALSE);
			$globalProductID = $productInfo['global_product_id'];
		}
		
		// Initialize global product
		parent::__construct($globalProductID);
	}
	
	/**
	 * Add a new product code to this product.
	 * 
	 * NOTE: You must be TEAM_ADMIN to execute.
	 * 
	 * @param	integer	$productCodeType
	 * 		The product code type.
	 * 		It references to EAN, UPC and other code types as retrieved from the database.
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
	public function add($productCodeType, $productCode, $expirationTime = "")
	{
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
		// Check if the product has a global reference
		$product = new cProduct($this->companyProductID);
		$productInfo = $product->info(FALSE);
		if (empty($productInfo['global_product_id']))
		{
			// Create global product and update reference
			$status = $product->createGlobal($productInfo['hierarchy_id'], $productInfo['title'], $productInfo['description']);
			if (!$status)
				return FALSE;
			$productInfo = $product->info(FALSE);
			parent::__construct($productInfo['global_product_id']);
		}
		
		// Add a product code
		return parent::add($productCodeType, $productCode, $expirationTime);
	}
}
//#section_end#
?>