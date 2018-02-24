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
importer::import("RTL", "Profile", "company");
importer::import("RTL", "Products", "globl/globalProduct");
importer::import("RTL", "Products", "globl/globalProductCodeManager");

use \RTL\Comm\dbConnection;
use \API\Model\sql\dbQuery;
use \API\Security\accountKey;
use \RTL\Profile\company;
use \RTL\Products\globl\globalProduct;
use \RTL\Products\globl\globalProductCodeManager;

/**
 * Company Product Manager
 * 
 * {description}
 * 
 * @version	1.0-1
 * @created	December 15, 2014, 15:48 (EET)
 * @updated	January 7, 2015, 15:17 (EET)
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
	 * NOTE: You must be TEAM_ADMIN to execute.
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
		
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
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
	 * Create a global product for this company product and connect.
	 * 
	 * NOTE: You must be TEAM_ADMIN to execute.
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
	public function createGlobal($productHierarchy, $title, $description = "")
	{
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
		
		// Check that this is a valid product
		$info = $this->info();
		if (empty($info))
			return FALSE;
		
		// Create global product
		$status = parent::create($productHierarchy, $title, $description);
		if ($status)
		{
			// Connect this product to global
			$q = new dbQuery("16655395421399", "retail.products");
			$attr = array();
			$attr['cid'] = company::getCompanyID();
			$attr['pid'] = $this->companyProductID;
			$attr['gid'] = $this->globalProductID;
			return $this->dbc->execute($q, $attr);
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
		// Check id
		if (empty($this->companyProductID))
			return NULL;
			
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
			
			$codeManager = new globalProductCodeManager($this->globalProductID);
			$productInfo['codes'] = $codeManager->get();
		}
		
		return $productInfo;
	}
	
	/**
	 * Update product information.
	 * 
	 * NOTE: You must be TEAM_ADMIN to execute.
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
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
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
	 * NOTE: You must be TEAM_ADMIN to execute.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function activate()
	{
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
		// Update company product info
		$q = new dbQuery("33096346320912", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['status'] = 1;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Deactivate the company product to be publicly invisible.
	 * 
	 * NOTE: You must be TEAM_ADMIN to execute.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function deactivate()
	{
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
		// Update company product info
		$q = new dbQuery("33096346320912", "retail.products");
		$attr = array();
		$attr['cid'] = company::getCompanyID();
		$attr['pid'] = $this->companyProductID;
		$attr['status'] = 0;
		return $this->dbc->execute($q, $attr);
	}
	
	/**
	 * Set the product's tax rate value.
	 * 
	 * NOTE: You must be TEAM_ADMIN to execute.
	 * 
	 * @param	integer	$taxRate
	 * 		The tax rate id as you get it from the financial API.
	 * 
	 * @return	boolean
	 * 		True on success, false on failure.
	 */
	public function setTaxRate($taxRate)
	{
		// Check if account is in the right user group
		if (!accountKey::validateGroup("TEAM_ADMIN", company::getCompanyID(), accountKey::TEAM_KEY_TYPE))
			return FALSE;
			
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